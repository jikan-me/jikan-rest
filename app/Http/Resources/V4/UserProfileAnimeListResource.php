<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;
use Jikan\Helper\Constants as JikanConstants;
use Jikan\Model\Common\DateRange;
use Jikan\Model\Resource\CommonImageResource\CommonImageResource;
use JMS\Serializer\Serializer;

class UserProfileAnimeListResource extends JsonResource
{
    private const VALID_AIRING_STATUS = [
        JikanConstants::STATUS_ANIME_AIRING => 'airing',
        JikanConstants::STATUS_ANIME_FINISHED => 'complete',
        JikanConstants::STATUS_ANIME_NOT_YET_AIRED => 'not_yet_aired'
    ];
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $startDate = $this['start_date'] ?? 'Not Available';
        $endDate = $this['end_date'] ?? '?';
        $startDate = strtotime($startDate);
        $endDate = strtotime($endDate);
        $dateRange = new DateRange(
            date('M j, Y', $startDate) . ' to ' . date('M j, Y', $endDate)
        );

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
                'airing' => self::VALID_AIRING_STATUS[$this['airing_status']] == JikanConstants::USER_ANIME_LIST_CURRENTLY_AIRING,
                'aired' => [
                    'from' => $dateRange->getFrom()->format('c'),
                    'to' => $dateRange->getUntil()->format('c'),
                    'prop' => [
                        'from' => [
                            'day' => $dateRange->getFromProp()->getDay(),
                            'month' => $dateRange->getFromProp()->getMonth(),
                            'year' => $dateRange->getFromProp()->getYear(),
                        ],
                        'to' => [
                            'day' => $dateRange->getUntilProp()->getDay(),
                            'month' => $dateRange->getUntilProp()->getMonth(),
                            'year' => $dateRange->getUntilProp()->getYear(),
                        ]
                    ],
                    'string' => (string) $dateRange
                ],
                'studios' => $this['studios'],
                'licensors' => $this['licensors'],
            ]
        ];
    }
}