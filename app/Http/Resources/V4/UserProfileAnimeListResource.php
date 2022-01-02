<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;
use Jikan\Helper\Constants as JikanConstants;
use Jikan\Helper\Parser;
use Jikan\Model\Common\DateRange;
use Jikan\Model\Resource\CommonImageResource\CommonImageResource;
use JMS\Serializer\Serializer;

class UserProfileAnimeListResource extends JsonResource
{
    private const VALID_AIRING_STATUS = [
        JikanConstants::USER_ANIME_LIST_CURRENTLY_AIRING => 'Currently Airing',
        JikanConstants::USER_ANIME_LIST_FINISHED_AIRING => 'Finished Airing',
        JikanConstants::USER_ANIME_LIST_NOT_YET_AIRED => 'Not yet aired',
    ];
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $startDateStr = $this['start_date'] ?? 'Not available';
        $endDateStr = $this['end_date'] ?? '?';
        $startDate = strtotime($startDateStr);
        $endDate = strtotime($endDateStr);

        $dateRangeStr = "";
        switch ($startDateStr) {
            case 'Not available':
                $dateRangeStr .= 'Not available';
                break;
            default:
                $dateRangeStr .= date('M j, Y', $startDate);
        }

        $dateRangeStr .= " to ";

        switch ($endDateStr) {
            case '?':
                $dateRangeStr .= '?';
                break;
            default:
                $dateRangeStr .= date('M j, Y', $endDate);
        }

        $dateRange = new DateRange($dateRangeStr);


        return [
            'watching_status' => $this['watching_status'],
            'score' => $this['score'],
            'episodes_watched' => $this['watched_episodes'],
            'tags' => $this['tags'],
            'is_rewatching' => $this['is_rewatching'],
            'watch_start_date' => $this['watch_start_date'],
            'watch_end_date' => $this['watch_end_date'],
            'days' => $this['days'],
            'storage' => $this['storage'],
            'priority' => $this['priority'],
            'anime' => [
                'mal_id' => $this['mal_id'],
                'title' => $this['title'],
                'url' => $this['url'],
                'images' => $this['images'],
                'type' => $this['type'],
                'season' => strtolower($this['season_name']),
                'year' => $this['season_year'],
                'episodes' => $this['total_episodes'],
                'rating' => $this['rating'], // @todo make same as GET /anime
                'status' => self::VALID_AIRING_STATUS[$this['airing_status']],
                'airing' => $this['airing_status'] === JikanConstants::USER_ANIME_LIST_CURRENTLY_AIRING,
                'aired' => [
                    'from' => $startDateStr === 'Not available' ? null : $dateRange->getFrom()->format('c'),
                    'to' => $endDateStr === '?' ? null : $dateRange->getUntil()->format('c'),
                    'prop' => [
                        'from' => [
                            'day' => $startDateStr === 'Not available' ? null : $dateRange->getFromProp()->getDay(),
                            'month' => $startDateStr === 'Not available' ? null : $dateRange->getFromProp()->getMonth(),
                            'year' => $startDateStr === 'Not available' ? null : $dateRange->getFromProp()->getYear(),
                        ],
                        'to' => [
                            'day' => $endDateStr === '?' ? null : $dateRange->getUntilProp()->getDay(),
                            'month' => $endDateStr === '?' ? null : $dateRange->getUntilProp()->getMonth(),
                            'year' => $endDateStr === '?' ? null : $dateRange->getUntilProp()->getYear(),
                        ]
                    ],
                    'string' => (string) $dateRange
                ],
                'studios' => $this['studios'],
                'licensors' => $this['licensors'],
                'genres' => $this['genres'],
                'demographics' => $this['demographics']
            ],
        ];
    }
}