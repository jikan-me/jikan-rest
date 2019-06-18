# Jikan REST Migration (v2 -> v3)

## NOTICE!
- Any key that holds an array value will be an empty array if there's nothing
- Any key that holds anything other than an array (string, int, float) will be `null` if there's nothing

## Anime
- Added `aired['string']`
- Added `trailer_url`
- Removed `airing_string`
- `link_canonical` -> `url`
- `title_synonyms` is now an array
- `producer` -> `producers`
- `licensor` -> `licensors`
- `studio` -> `studios`
- `genre` -> `genres`
- `opening_theme` -> `opening_themes`
- `ending_theme` -> `ending_themes`
## Anime : /episodes
- `episode` -> `episodes`
    - `id` -> `episode_id`
    - `aired` is now an array
- `episode_last_page` -> `episodes_last_page`

## Anime : /characters\_and\_staff
- `character` -> `characters`
    `voice_actor` -> `voice_actors`
- `staff`
    - `role` -> `positions`
    - `positions` is now an array

## Anime : /news
- `news` -> `articles`
- `date` is now in ISO8601
- Added `intro`

## Anime : /pictures
- `image` -> `pictures`
- `pictures` is now an array with 2 items
    - `large` - large version of the image
    - `small` - small version of the image

## Anime : /videos
- `episode` -> `episodes`

## Anime : /stats
- `score_stats` -> `scores`

## Anime : /forum
- `topic` -> `topics`
    - `last_post`
        - `date_relative` -> `date_posted`
        - `date_posted` is now in ISO8601

## Anime : /moreinfo
- `more_info` -> `moreinfo`


## Manga
- Added `published['string']`
- Removed `published_string`
- `link_canonical` -> `url`
- `title_synonyms` is now an array
- `author` -> `authors`
- `serialization` -> `serializations`
- `genre` -> `genres`

## Manga : /characters
- `character` -> `characters`

## Manga : /news
- `news` -> `articles`
- `date` is now in ISO8601
- Added `intro`

## Manga : /pictures
- `image` -> `pictures`
- `pictures` is now an array with 2 items
    - `large` - large version of the image
    - `small` - small version of the image

## Manga : /stats
- `score_stats` -> `scores`

## Manga : /forum
- `topic` -> `topics`
    - `last_post`
        - `date_relative` -> `date_posted`
        - `date_posted` is now in ISO8601

## Manga : /moreinfo
- `more_info` -> `moreinfo`

## Character
- `link_canonical` -> `url`
- `nicknames` is now an array
- `voice_actor` -> `voice_actors`

## Character : /pictures
- `image` -> `pictures`
- `pictures` is now an array with 2 items
    - `large` - large version of the image
    - `small` - small version of the image

## Person
- `link_canonical` -> `url`
- `birthday` is now in ISO8601
- `more` -> `about`
- `voice_acting_role` -> `voice_acting_roles`
    - Added `role`
- `anime_staff_position` -> `anime_staff_positions`
    - `role` -> `position`
- `published_manga`
    - `role` -> `position`

## Person : /pictures
- `image` -> `pictures`
- `pictures` is now an array with 2 items
    - `large` - large version of the image
    - `small` - small version of the image

## Search
Search query as a URL segment is now depreciated. You have to pass the query via GET key `q`.
e.g `/search/anime?q=Fate/Zero`

- `result` -> `results`
- `result_last_page` -> `last_page`

## Search : /anime
- `result`
    - Added `airing` (boolean)
    - `description` -> `synopsis`
    - Added `start_date` (ISO8601)
    - Added `end_date` (ISO8601)
    - Added `rated`
    
## Search : /manga
- `result`
    - Added `publishing` (boolean)
    - `description` -> `synopsis`
    - Added `start_date` (ISO8601)
    - Added `end_date` (ISO8601)
    - Added `chapters` (int)
    
## Search : /people, /person
- `result`
    - `nicknames` -> `alternative_names`
    - `alternative_names` is now an array
    
## Search : /character
- `result`
    - `nicknames` -> `alternative_names`
    - `alternative_names` is now an array
    
## Season
- `season` -> `anime`
    - `producer` -> `producers`
    - `genre` -> `genres`
    - `licensor` -> `licensors`
    - `continued` -> `continuing`
    - `airing_start` is now in ISO8601
    - `r18_plus` -> `r18`
    
## Schedule
- Added `other`
- Added `unknown`
- `producer` -> `producers`
- `genre` -> `genres`
- `licensor` -> `licensors`
- `continued` -> `continuing`
- `airing_start` is now in ISO8601
- `r18_plus` -> `r18`


## Top : Anime
- `airing_start` -> `start_date`
- `airing_end` -> `end_date`

## Top : Manga
- `publishing_start` -> `start_date`
- `publishing_end` -> `end_date`