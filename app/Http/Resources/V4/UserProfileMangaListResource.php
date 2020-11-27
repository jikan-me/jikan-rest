<?php

namespace App\Http\Resources\V4;

use Illuminate\Http\Resources\Json\JsonResource;
use Jikan\Helper\Constants as JikanConstants;
use Jikan\Model\Common\DateRange;
use Jikan\Model\Resource\CommonImageResource\CommonImageResource;
use JMS\Serializer\Serializer;

class UserProfileMangaListResource extends JsonResource
{

    private const VALID_PUBLISHING_STATUS = [
        JikanConstants::STATUS_MANGA_PUBLISHING => 'publishing',
        JikanConstants::STATUS_MANGA_FINISHED => 'complete',
        JikanConstants::STATUS_MANGA_NOT_YET_PUBLISHED => 'not_yet_published',
        JikanConstants::STATUS_MANGA_ON_HIATUS => 'on_hiatus',
        JikanConstants::STATUS_MANGA_DISCONTINUED => 'discontinued'
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
            'reading_status' => $this['reading_status'],
            'score' => $this['score'],
            'chapters_read' => $this['read_chapters'],
            'volumes_read' => $this['read_volumes'],
            'tags' => $this['tags'],
            'is_rereading' => $this['is_rereading'],
            'read_start_date' => $this['read_start_date'],
            'read_end_date' => $this['read_end_date'],
            'days' => $this['days'],
            'retail' => $this['retail'],
            'priority' => $this['priority'],
            'manga' => [
                'mal_id' => $this['mal_id'],
                'title' => $this['title'],
                'url' => $this['url'],
                'images' => $this['images'],
                'type' => $this['type'],
                'chapters' => $this['total_chapters'],
                'volumes' => $this['total_volumes'],
                'publishing' => self::VALID_PUBLISHING_STATUS[$this['publishing_status']] == JikanConstants::USER_MANGA_LIST_CURRENTLY_PUBLISHING,
                'published' => [
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
                'magazines' => $this['magazines'],
            ]
        ];
    }
}