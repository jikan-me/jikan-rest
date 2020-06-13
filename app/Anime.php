<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Anime extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mal_id','url','image_url','trailer_url','title','title_english','title_japanese','title_synonyms','type','source','episodes','status','airing','aired','duration','rating','score','scored_by','rank','popularity','members','favorites','synopsis','background','premiered','broadcast','related','producers','licensors','studios','genres','opening_themes','ending_themes'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        '_id', 'expiresAt', 'request_hash'
    ];
}