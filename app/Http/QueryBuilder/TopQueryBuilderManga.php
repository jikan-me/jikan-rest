<?php
    
    namespace App\Http\QueryBuilder;
    
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Jenssegers\Mongodb\Eloquent\Builder;
    
    
    /**
     * Class SearchQueryBuilderAnime
     * @package App\Http\QueryBuilder
     */
    class TopQueryBuilderManga implements SearchQueryBuilderInterface
    {
    
        /**
         *
         */
        const MAX_RESULTS_PER_PAGE = 25;
    
        /**
         *
         */
        const MAP_TYPES = [
            'manga' => 'Manga',
            'novels' => 'Novel',
            'oneshots' => 'One-shot',
            'doujin' => 'Doujinshi',
            'manhwa' => 'Manhwa',
            'manhua' => 'Manhua'
        ];
    
        /**
         *
         */
        const MAP_FILTER = [
            'upcoming', 'bypopularity', 'favorites'
        ];
    
        /**
         * @param Request $request
         * @param Builder $builder
         * @return Builder
         */
        public static function query(Request $request, Builder $results) : Builder
        {
            $mangaType = self::mapType($request->get('type'));
            $filterType = self::mapFilter($request->get('filter'));
    
            $results = $results
                ->whereNotNull('rank')
                ->where('rank', '>', 0)
                ->orderBy('rank', 'asc')
                ->where('type', '!=', 'Doujinshi');
    
            if (!is_null($mangaType)) {
                $results = $results
                    ->where('type', $mangaType);
            }
    
            if (!is_null($filterType) && $filterType === 'publishing') {
                $results = $results
                    ->where('publishing', true);
            }
    
            if (!is_null($filterType) && $filterType === 'bypopularity') {
                $results = $results
                    ->orderBy('popularity', 'desc');
            }
    
            if (!is_null($filterType) && $filterType === 'favorite') {
                $results = $results
                    ->orderBy('favorites', 'desc');
            }
    
            return $results;
        }
    
        /**
         * @param string|null $type
         * @return string|null
         */
        public static function mapType(?string $type = null) : ?string
        {
            if (is_null($type)) {
                return null;
            }
    
            $type = strtolower($type);
    
            return self::MAP_TYPES[$type] ?? null;
        }
    
        /**
         * @param string|null $filter
         * @return string|null
         */
        public static function mapFilter(?string $filter = null) : ?string
        {
            $filter = strtolower($filter);
    
            if (!\in_array($filter, self::MAP_FILTER)) {
                return null;
            }
    
            return $filter;
        }
    }