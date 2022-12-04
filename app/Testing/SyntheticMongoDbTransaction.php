<?php

namespace App\Testing;

use App\JikanApiModel;

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
    }
}
