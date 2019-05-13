<?php

namespace App\Http\Controllers\V2;

use Jikan\Request\Manga\MangaCharactersRequest;
use Jikan\Request\Manga\MangaForumRequest;
use Jikan\Request\Manga\MangaMoreInfoRequest;
use Jikan\Request\Manga\MangaNewsRequest;
use Jikan\Request\Manga\MangaPicturesRequest;
use Jikan\Request\Manga\MangaRequest;
use Jikan\Request\Manga\MangaStatsRequest;

class MangaController extends Controller
{
    public function _main($id)
    {
        $manga = $this->jikan->getManga(new MangaRequest($id));

        // backwards compatibility
        $manga = json_decode(
            $this->serializer->serialize($manga, 'json'),
            true
        );

        $manga['published_string'] = $manga['published']['string'];
        unset($manga['published']['string']);
        $manga['title_synonyms'] = empty($manga['title_synonyms']) ? null : implode(",", $manga['title_synonyms']);
        ;

        return $manga;
    }

    public function main(int $id)
    {
        $manga = $this->_main($id);

        return response(
            json_encode($manga)
        );
    }

    public function characters(int $id)
    {
        $manga = $this->_main($id);
        $characters = ['character' => $this->jikan->getMangaCharacters(new MangaCharactersRequest($id))];
        $characters = json_decode(
            $this->serializer->serialize($characters, 'json'),
            true
        );

        return response(
            json_encode(
                array_merge(
                    $manga,
                    $characters
                )
            )
        );
    }

    public function news(int $id)
    {
        $manga = $this->_main($id);
        $news = ['news' => $this->jikan->getNewsList(new MangaNewsRequest($id))];
        $news = json_decode(
            $this->serializer->serialize($news, 'json'),
            true
        );


        return response(
            json_encode(
                array_merge(
                    $manga,
                    $news
                )
            )
        );
    }

    public function forum(int $id)
    {
        $manga = $this->_main($id);
        $forum = ['topic' => $this->jikan->getMangaForum(new MangaForumRequest($id))];
        $forum = json_decode(
            $this->serializer->serialize($forum, 'json'),
            true
        );


        return response(
            json_encode(
                array_merge(
                    $manga,
                    $forum
                )
            )
        );
    }

    public function pictures(int $id)
    {
        $manga = $this->_main($id);
        $pictures = ['image' =>$this->jikan->getMangaPictures(new MangaPicturesRequest($id))];
        $pictures = json_decode(
            $this->serializer->serialize($pictures, 'json'),
            true
        );

        foreach ($pictures['image'] as $key => $value) {
            $pictures['image'][$key] = $value['small'];
        }


        return response(
            json_encode(
                array_merge(
                    $manga,
                    $pictures
                )
            )
        );
    }

    public function stats(int $id)
    {
        $manga = $this->_main($id);
        $stats = $this->jikan->getMangaStats(new MangaStatsRequest($id));
        $stats = json_decode(
            $this->serializer->serialize($stats, 'json'),
            true
        );

        return response(
            json_encode(
                array_merge(
                    $manga,
                    $stats
                )
            )
        );
    }

    public function moreInfo(int $id)
    {
        $manga = $this->_main($id);
        $moreinfo = ['moreinfo' => $this->jikan->getMangaMoreInfo(new MangaMoreInfoRequest($id))];
        $moreinfo = json_decode(
            $this->serializer->serialize($moreinfo, 'json'),
            true
        );

        return response(
            json_encode(
                array_merge(
                    $manga,
                    $moreinfo
                )
            )
        );
    }
}
