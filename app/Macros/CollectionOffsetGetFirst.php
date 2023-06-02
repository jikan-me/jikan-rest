<?php

namespace App\Macros;

use App\JikanApiModel;
use Illuminate\Support\Collection;

/**
 * @mixin Collection
 * @method offsetGetFirst(string $offset, mixed $default = null)
 */
final class CollectionOffsetGetFirst
{
    public function __invoke(): \Closure
    {
        return function (string $offset, mixed $default = null) {
            /**
             * @var $this Collection
             */
            $firstItem = $this->first();
            if (is_array($firstItem)) {
                $result = collect($firstItem)->get($offset, $default);
            }
            else if ($firstItem instanceof JikanApiModel) {
                $result = $firstItem[$offset];
            }
            else if ($firstItem instanceof Collection) {
                $result = $firstItem->get($offset, $default);
            }
            else {
                $result = $default;
            }

            return $result;
        };
    }
}
