<?php

namespace App\Http\Controllers\V4DB;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Laravel\Lumen\Routing\Controller as BaseController;

/**
 * Image Proxy Controller for Jikan REST API.
 *
 * Proxies image requests to MyAnimeList's CDN to avoid hotlink/CORS blocks.
 * Features:
 * - Whitelist: only proxies cdn.myanimelist.net URLs
 * - Disk caching with configurable TTL (default 24h)
 * - Proper Content-Type headers
 * - Cache-Control headers for browser caching
 * - Referrer stripping to bypass CDN restrictions
 */
class ImageProxyController extends BaseController
{
    /**
     * Allowed CDN hostnames for proxying.
     */
    private const ALLOWED_HOSTS = [
        'cdn.myanimelist.net',
        'img1.ak.crunchyroll.com',
    ];

    /**
     * Cache TTL in seconds (default 24 hours).
     */
    private int $cacheTtl;

    /**
     * Maximum image size in bytes (10MB).
     */
    private const MAX_IMAGE_SIZE = 10 * 1024 * 1024;

    public function __construct()
    {
        $this->cacheTtl = (int) env('IMAGE_PROXY_CACHE_TTL', 86400);
    }

    /**
     * @OA\Get(
     *     path="/proxy/image",
     *     operationId="proxyImage",
     *     tags={"proxy"},
     *
     *     @OA\Parameter(
     *         name="url",
     *         in="query",
     *         required=true,
     *         description="The CDN image URL to proxy (must be from cdn.myanimelist.net)",
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns the proxied image binary",
     *         @OA\MediaType(
     *             mediaType="image/*"
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid or disallowed URL",
     *     ),
     *     @OA\Response(
     *         response="502",
     *         description="Failed to fetch image from upstream CDN",
     *     ),
     * )
     */
    public function proxy(Request $request)
    {
        $url = $request->query('url');

        if (empty($url)) {
            return response()->json([
                'status' => 400,
                'type' => 'BadRequestException',
                'message' => 'Missing required "url" query parameter.',
                'error' => 'url parameter is required',
            ], 400);
        }

        // Decode URL if double-encoded
        $url = urldecode($url);

        // Validate the URL
        if (!$this->isAllowedUrl($url)) {
            return response()->json([
                'status' => 400,
                'type' => 'BadRequestException',
                'message' => 'Only MyAnimeList CDN URLs are allowed.',
                'error' => 'URL host not in whitelist',
            ], 400);
        }

        // Check cache first
        $cacheKey = 'img_proxy:' . sha1($url);

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $this->buildImageResponse($cached['body'], $cached['content_type']);
        }

        // Fetch from upstream CDN
        $result = $this->fetchImage($url);

        if ($result === null) {
            return response()->json([
                'status' => 502,
                'type' => 'BadGatewayException',
                'message' => 'Failed to fetch image from upstream CDN.',
                'error' => 'Upstream image fetch failed',
            ], 502);
        }

        // Cache the result
        try {
            Cache::put($cacheKey, $result, $this->cacheTtl);
        } catch (\Exception $e) {
            // Cache failure is non-fatal
        }

        return $this->buildImageResponse($result['body'], $result['content_type']);
    }

    /**
     * Validate that the URL is from an allowed CDN host.
     */
    private function isAllowedUrl(string $url): bool
    {
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['host'])) {
            return false;
        }

        $host = strtolower($parsed['host']);
        foreach (self::ALLOWED_HOSTS as $allowed) {
            if ($host === $allowed || str_ends_with($host, '.' . $allowed)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetch an image from the upstream CDN using Guzzle HTTP client.
     */
    private function fetchImage(string $url): ?array
    {
        try {
            $client = new Client([
                RequestOptions::TIMEOUT => 10,
                RequestOptions::CONNECT_TIMEOUT => 5,
                RequestOptions::ALLOW_REDIRECTS => [
                    'max' => 3,
                    'referer' => false,
                ],
                RequestOptions::HTTP_ERRORS => false,
                RequestOptions::HEADERS => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Referer' => '',
                ],
            ]);

            $response = $client->get($url);

            $httpCode = $response->getStatusCode();
            if ($httpCode !== 200) {
                return null;
            }

            $body = $response->getBody()->getContents();

            // Enforce size limit
            if (strlen($body) > self::MAX_IMAGE_SIZE) {
                return null;
            }

            // Get content type from response headers
            $contentType = $response->getHeaderLine('Content-Type');

            // Validate it's actually an image
            if ($contentType && strpos($contentType, 'image') === false) {
                return null;
            }

            // Fallback content type detection
            if (empty($contentType) || strpos($contentType, 'image') === false) {
                $contentType = $this->detectContentType($url, $body);
            }

            return [
                'body' => base64_encode($body),
                'content_type' => $contentType ?: 'image/jpeg',
            ];
        } catch (GuzzleException $e) {
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Build an HTTP response with the image data.
     */
    private function buildImageResponse(string $base64Body, string $contentType)
    {
        $body = base64_decode($base64Body);

        return response($body, 200)
            ->header('Content-Type', $contentType)
            ->header('Content-Length', strlen($body))
            ->header('Cache-Control', 'public, max-age=' . $this->cacheTtl . ', immutable')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('X-Jikan-Image-Proxy', 'true');
    }

    /**
     * Detect content type from URL extension or magic bytes.
     */
    private function detectContentType(string $url, string $body): string
    {
        // Try URL extension first
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $map = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                'avif' => 'image/avif',
            ];
            if (isset($map[$ext])) {
                return $map[$ext];
            }
        }

        // Magic bytes detection
        if (strlen($body) >= 4) {
            $header = substr($body, 0, 4);
            if ($header === "\x89PNG") return 'image/png';
            if (substr($header, 0, 3) === "GIF") return 'image/gif';
            if ($header === "RIFF") return 'image/webp';
            if (substr($header, 0, 2) === "\xFF\xD8") return 'image/jpeg';
        }

        return 'image/jpeg';
    }
}
