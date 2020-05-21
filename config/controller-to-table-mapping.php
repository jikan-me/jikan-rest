<?php

return [

    'AnimeController@main' => 'anime',
    'AnimeController@characters_staff' => 'anime_characters_staff',
    'AnimeController@episodes' => 'anime_episodes',
    'AnimeController@episode' => 'anime_episode',
    'AnimeController@news' => 'anime_news',
    'AnimeController@forum' => 'anime_forum',
    'AnimeController@videos' => 'anime_videos',
    'AnimeController@pictures' => 'anime_pictures',
    'AnimeController@stats' => 'anime_stats',
    'AnimeController@moreInfo' => 'anime_moreinfo',
    'AnimeController@recommendations' => 'anime_recommendations',
    'AnimeController@userupdates' => 'anime_userupdates',
    'AnimeController@reviews' => 'anime_reviews',

    'MangaController@main' => 'manga',
    'MangaController@characters' => 'manga_characters',
    'MangaController@news' => 'manga_news',
    'MangaController@forum' => 'manga_forum',
    'MangaController@pictures' => 'manga_pictures',
    'MangaController@stats' => 'manga_stats',
    'MangaController@moreInfo' => 'manga_moreinfo',
    'MangaController@recommendations' => 'manga_recommendations',
    'MangaController@userupdates' => 'manga_userupdates',
    'MangaController@reviews' => 'manga_reviews',

    'CharacterController@main' => 'characters',
    'CharacterController@pictures' => 'characters_pictures',

    'PersonController@main' => 'people',
    'PersonController@pictures' => 'people_pictures',

    'SeasonController@archive' => 'season_archive',
    'SeasonController@later' => 'season_later',
    'SeasonController@main' => 'season',

    'ScheduleController@main' => 'schedule',

    'ProducerController@main' => 'producers',
    'ProducerController@resource' => 'producers_anime',

    'MagazineController@main' => 'magazines',
    'MagazineController@resource' => 'magazines_manga',

    'UserController@recentlyOnline' => 'users_recently_online',
    'UserController@profile' => 'users',
    'UserController@history' => 'users_history',
    'UserController@friends' => 'users_friends',
    'UserController@animelist' => 'users_animelist',
    'UserController@mangalist' => 'users_mangalist',
    'UserController@recommendations' => 'users_recommendations',
    'UserController@reviews' => 'users_reviews',
    'UserController@clubs' => 'users_clubs',

    'GenreController@animeListing' => 'genres',
    'GenreController@mangaListing' => 'genres',
    'GenreController@anime' => 'genres_anime',
    'GenreController@manga' => 'genres_manga',

    'TopController@anime' => 'top_anime',
    'TopController@manga' => 'top_manga',
    'TopController@characters' => 'top_characters',
    'TopController@people' => 'top_people',
    'ReviewsController@bestVoted' => 'top_reviews',

    'SearchController@anime' => 'search_anime',
    'SearchController@manga' => 'search_manga',
    'SearchController@character' => 'search_characters',
    'SearchController@people' => 'search_people',
    'SearchController@users' => 'search_users',
    'SearchController@userById' => 'search_users_by_id',

    'ClubController@main' => 'clubs',
    'ClubController@members' => 'clubs_members',

    'ReviewsController@anime' => 'reviews',
    'ReviewsController@manga' => 'reviews',

    'RecommendationsController@anime' => 'recommendations',
    'RecommendationsController@manga' => 'recommendations',

    'WatchController@recentEpisodes' => 'watch',
    'WatchController@popularEpisodes' => 'watch',
    'WatchController@recentPromos' => 'watch',
    'WatchController@popularPromos' => 'watch',

];