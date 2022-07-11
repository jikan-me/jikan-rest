<?php
namespace App\Http\QueryBuilder\Traits;

trait TypeResolver
{
    /**
     * @param string|null $type
     * @return string|null
     */
    public function mapType(?string $type = null): ?string
    {
        if (empty($type)) {
            return null;
        }
        $type = strtolower($type);

        $typeMap = $this->getTypeMap();

        // fallback to the original value, so we would show an empty result set.
        return array_key_exists($type, $typeMap) ? $typeMap[$type] : $type;
    }

    protected function getTypeMap(): array
    {
        return [];
    }
}
