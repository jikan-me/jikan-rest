<?php

namespace App\Testing;

use App\JikanApiModel;
use Illuminate\Support\Facades\DB;

trait SyntheticMongoDbTransaction
{
    private static array $jikanModels = [];

    private static function getJikanModels(): array
    {
        if (count(self::$jikanModels) === 0)
        {
            self::$jikanModels = array_filter(get_declared_classes(), fn($class) => is_subclass_of($class, JikanApiModel::class));
        }

        return self::$jikanModels;
    }

    public function beginDatabaseTransaction(): void
    {
        $jikanModels = self::getJikanModels();
        $tablesWithoutModels = [
            "anime_characters_staff",
            "anime_episodes",
            "anime_forum",
            "anime_moreinfo",
            "anime_news",
            "anime_pictures",
            "anime_recommendations",
            "anime_reviews",
            "anime_stats",
            "anime_userupdates",
            "anime_videos",
            "character_pictures",
            "clubs_members",
            "demographics_manga",
            "demographics_anime",
            "manga_characters",
            "manga_moreinfo",
            "manga_news",
            "manga_pictures",
            "manga_recommendations",
            "manga_reviews",
            "manga_stats",
            "manga_userupdates",
            "people_pictures"
        ];

        foreach ($jikanModels as $jikanModel)
        {
            if ($jikanModel === "App\\JikanApiSearchableModel") {
                continue;
            }
            if ($jikanModel::count() > 0)
            {
                $jikanModel::truncate();
            }
        }

        foreach ($tablesWithoutModels as $tableName)
        {
            DB::table($tableName)->truncate();
        }
    }
}
