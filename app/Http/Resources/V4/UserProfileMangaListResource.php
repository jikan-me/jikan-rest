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
        JikanConstants::USER_MANGA_LIST_CURRENTLY_PUBLISHING => 'Publishing',
        JikanConstants::USER_MANGA_LIST_COMPLETED => 'Finished',
        JikanConstants::USER_MANGA_LIST_NOT_YET_PUBLISHED => 'Not yet published',
        JikanConstants::USER_MANGA_LIST_ON_HIATUS => 'On Hiatus',
        JikanConstants::USER_MANGA_LIST_DISCONTINUED => 'Discontinued'
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
                'status' => self::VALID_PUBLISHING_STATUS[$this['publishing_status']],
                'publishing' => $this['publishing_status'] === JikanConstants::USER_MANGA_LIST_CURRENTLY_PUBLISHING,
                'published' => [
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
                'magazines' => $this['magazines'],
                'genres' => $this['genres'],
                'demographics' => $this['demographics']
            ],
        ];
    }
}