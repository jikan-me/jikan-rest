<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Response Normalizer Middleware for Jikan REST API.
 *
 * Ensures consistent data schemas in API responses by:
 * - Filling missing image URL variants (small, image, large)
 * - Defaulting null title_english to title
 * - Extracting year from aired.from when missing
 * - Defaulting null episodes to 0
 * - Defaulting null score to 0.0
 * - Rewriting CDN image URLs through the local proxy (when enabled)
 * - Adding audio_languages heuristic (sub/dub inference)
 */
class ResponseNormalizerMiddleware
{
    private readonly bool $imageProxyEnabled;
    private readonly string $imageProxyBase;

    /**
     * Known English-language licensors that indicate a dub is available.
     */
    private const DUB_LICENSORS = [
        'funimation', 'crunchyroll', 'sentai filmworks', 'viz media',
        'aniplex of america', 'bang zoom!', 'bandai entertainment',
        'geneon', 'adv films', 'media blasters', 'nozomi entertainment',
        'discotek media', 'nis america', 'ponycan usa', 'eleven arts',
        'gkids', 'shout! factory', 'manga entertainment',
    ];

    public function __construct()
    {
        $this->imageProxyEnabled = (bool) env('IMAGE_PROXY_ENABLED', false);
        $this->imageProxyBase = rtrim(env('APP_URL', 'http://localhost'), '/') . '/v4/proxy/image';
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only normalize JSON responses
        $contentType = $response->headers->get('Content-Type', '');
        if (strpos($contentType, 'json') === false && !($response instanceof \Illuminate\Http\JsonResponse)) {
            return $response;
        }

        try {
            $data = null;
            $isJsonResponse = $response instanceof \Illuminate\Http\JsonResponse;

            if ($isJsonResponse) {
                $data = $response->getData(true);
            } else {
                $content = $response->getContent();
                if ($content) {
                    $data = json_decode($content, true);
                }
            }

            if ($data === null || !is_array($data)) {
                return $response;
            }

            $data = $this->normalizeResponseData($data);

            if ($isJsonResponse) {
                $response->setData($data);
            } else {
                $response->setContent(json_encode($data));
            }
        } catch (\Exception $e) {
            // Normalization failure should never break the response
        }

        return $response;
    }

    /**
     * Normalize the top-level response structure.
     */
    private function normalizeResponseData(array $data): array
    {
        // Handle single item response: { "data": { ... } }
        if (isset($data['data']) && is_array($data['data']) && !$this->isSequentialArray($data['data'])) {
            $data['data'] = $this->normalizeItem($data['data']);
        }
        // Handle list response: { "data": [ { ... }, { ... } ] }
        elseif (isset($data['data']) && is_array($data['data']) && $this->isSequentialArray($data['data'])) {
            foreach ($data['data'] as $key => $item) {
                if (is_array($item)) {
                    $data['data'][$key] = $this->normalizeItem($item);
                }
            }
        }

        return $data;
    }

    /**
     * Normalize a single anime/manga item.
     */
    private function normalizeItem(array $item): array
    {
        // Normalize images
        $item = $this->normalizeImages($item);

        // Normalize title_english
        if ((!isset($item['title_english']) || $item['title_english'] === null) && isset($item['title'])) {
            $item['title_english'] = $item['title'];
        }

        // Normalize year — extract from aired.from if missing
        if ((!isset($item['year']) || $item['year'] === null) && isset($item['aired']['from'])) {
            $from = $item['aired']['from'];
            if (is_string($from) && preg_match('/^(\d{4})/', $from, $matches)) {
                $item['year'] = (int) $matches[1];
            }
        }

        // Normalize episodes — default to 0 for types without fixed episode counts
        if (!isset($item['episodes']) || $item['episodes'] === null) {
            $item['episodes'] = 0;
        }

        // Normalize score — default to 0.0
        if (!isset($item['score']) || $item['score'] === null) {
            $item['score'] = 0.0;
        }

        // Normalize synopsis — default to empty string
        if (!isset($item['synopsis']) || $item['synopsis'] === null) {
            $item['synopsis'] = '';
        }

        // Add audio_languages heuristic (Problem 6)
        $item = $this->normalizeAudioLanguages($item);

        return $item;
    }

    /**
     * Normalize image URLs, ensuring all size variants exist.
     */
    private function normalizeImages(array $item): array
    {
        if (!isset($item['images']) || !is_array($item['images'])) {
            return $item;
        }

        foreach (['jpg', 'webp'] as $format) {
            if (!isset($item['images'][$format]) || !is_array($item['images'][$format])) {
                continue;
            }

            $img = &$item['images'][$format];

            // Find any available URL to use as base
            $baseUrl = $img['image_url']
                ?? $img['large_image_url']
                ?? $img['small_image_url']
                ?? null;

            if ($baseUrl === null) {
                continue;
            }

            // Ensure all variants exist
            if (!isset($img['image_url']) || $img['image_url'] === null) {
                $img['image_url'] = $baseUrl;
            }

            if (!isset($img['small_image_url']) || $img['small_image_url'] === null) {
                // MAL pattern: small images use 't' suffix before extension
                $img['small_image_url'] = $this->generateSmallImageUrl($baseUrl);
            }

            if (!isset($img['large_image_url']) || $img['large_image_url'] === null) {
                // MAL pattern: large images use 'l' suffix before extension
                $img['large_image_url'] = $this->generateLargeImageUrl($baseUrl);
            }

            // Optionally rewrite URLs through the image proxy
            if ($this->imageProxyEnabled) {
                foreach (['image_url', 'small_image_url', 'large_image_url'] as $urlKey) {
                    if (isset($img[$urlKey]) && $this->isMalCdnUrl($img[$urlKey])) {
                        $img[$urlKey] = $this->imageProxyBase . '?url=' . urlencode($img[$urlKey]);
                    }
                }
            }

            unset($img);
        }

        return $item;
    }

    /**
     * Add audio_languages and has_dub fields based on licensors heuristic.
     */
    private function normalizeAudioLanguages(array $item): array
    {
        // Only applicable to anime (items with 'episodes' or 'type' like TV/Movie/OVA)
        if (!isset($item['type']) || !in_array($item['type'], ['TV', 'Movie', 'OVA', 'ONA', 'Special', 'Music'])) {
            return $item;
        }

        // Default: Japanese audio (sub)
        $languages = ['ja'];
        $hasDub = false;

        // Check licensors for known dub companies
        if (isset($item['licensors']) && is_array($item['licensors'])) {
            foreach ($item['licensors'] as $licensor) {
                $name = '';
                if (is_array($licensor) && isset($licensor['name'])) {
                    $name = strtolower($licensor['name']);
                } elseif (is_string($licensor)) {
                    $name = strtolower($licensor);
                }

                foreach (self::DUB_LICENSORS as $dubLicensor) {
                    if (strpos($name, $dubLicensor) !== false) {
                        $hasDub = true;
                        if (!in_array('en', $languages)) {
                            $languages[] = 'en';
                        }
                        break 2;
                    }
                }
            }
        }

        // Check studios for known dub-producing studios
        if (!$hasDub && isset($item['studios']) && is_array($item['studios'])) {
            foreach ($item['studios'] as $studio) {
                $name = '';
                if (is_array($studio) && isset($studio['name'])) {
                    $name = strtolower($studio['name']);
                }
                if (strpos($name, 'funimation') !== false || strpos($name, 'sentai') !== false) {
                    $hasDub = true;
                    if (!in_array('en', $languages)) {
                        $languages[] = 'en';
                    }
                    break;
                }
            }
        }

        $item['audio_languages'] = $languages;
        $item['has_dub'] = $hasDub;

        return $item;
    }

    /**
     * Generate a small image URL from a base URL (MAL convention: 't' suffix).
     */
    private function generateSmallImageUrl(string $baseUrl): string
    {
        // Example: /images/anime/6/73245.jpg → /images/anime/6/73245t.jpg
        return preg_replace('/(\.\w+)$/', 't$1', $baseUrl);
    }

    /**
     * Generate a large image URL from a base URL (MAL convention: 'l' suffix).
     */
    private function generateLargeImageUrl(string $baseUrl): string
    {
        // Example: /images/anime/6/73245.jpg → /images/anime/6/73245l.jpg
        return preg_replace('/(\.\w+)$/', 'l$1', $baseUrl);
    }

    /**
     * Check if a URL belongs directly to MAL's CDN domain.
     * Uses host parsing to avoid double-proxying or matching query parameters.
     */
    private function isMalCdnUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return false;
        }

        return strtolower($host) === 'cdn.myanimelist.net';
    }

    /**
     * Check if an array is a sequential (list-style) array.
     */
    private function isSequentialArray(array $arr): bool
    {
        if (empty($arr)) {
            return true;
        }
        return array_keys($arr) === range(0, count($arr) - 1);
    }
}
