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
        $type = strtolower($type);

        return $this->getTypeMap()[$type] ?? null;
    }

    protected function getTypeMap(): array
    {
        return [];
    }
}
