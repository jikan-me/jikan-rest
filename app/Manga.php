<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Jikan\Helper\Media;
use Jikan\Helper\Parser;
use Jikan\Jikan;
use Jikan\Model\Common\YoutubeMeta;

class Manga extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id', 'url', 'title', 'title_english', 'title_japanese', 'title_synonyms', 'status', 'type', 'volumes', 'chapters', 'publishing', 'published', 'rank', 'score',
        'scored_by', 'popularity', 'members', 'favorites', 'synopsis', 'background', 'related', 'genres', 'authors', 'serializations',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['images'];

    protected $mainDataRequest = true;
    protected $databaseStoreAvailability = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'manga';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
//    protected $primaryKey = 'mal_id';
//
//    const CREATED_AT = 'creation_date';
//    const UPDATED_AT = 'last_update';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '_id', 'expiresAt', 'request_hash', 'images_url'
    ];

    public function setRelatedAttribute($value)
    {
        $this->attributes['related'] = $this->getRelatedAttribute();
    }

    public function getRelatedAttribute()
    {
        // Fix JSON response for empty related object
        if (\count($this->attributes['related']) === 0) {
            $this->attributes['related'] = new \stdClass();
        }

        if (!is_object($this->attributes['related']) && !empty($this->attributes['related'])) {
            $relation = [];
            foreach ($this->attributes['related'] as $relationType => $related) {
                $relation[] = [
                    'relation' => $relationType,
                    'items' => $related
                ];
            }
            $this->attributes['related'] = $relation;
        }

        return $this->attributes['related'];
    }

    public function setImageAttribute($value)
    {
        $this->attributes['image'] = $this->getImageAttribute();
    }

    public function getImageAttribute()
    {
        $imageUrl = $this->attributes['image_url'];

        return [
            'jpg' => [
                'image_url' => $imageUrl,
                'small_image_url' => str_replace('.jpg', 't.jpg', $imageUrl),
                'large_image_url' => str_replace('.jpg', 'l.jpg', $imageUrl),
            ],
            'webp' => [
                'image_url' => str_replace('.jpg', '.webp', $imageUrl),
                'small_image_url' => str_replace('.jpg', 't.webp', $imageUrl),
                'large_image_url' => str_replace('.jpg', 'l.webp', $imageUrl),
            ]
        ];
    }
}