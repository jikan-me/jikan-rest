<?php
namespace App\Http\QueryBuilder\Traits;

trait StatusResolver
{
    /**
     * @param string|null $status
     * @return string|null
     */
    public function mapStatus(?string $status = null): ?string
    {
        $status = strtolower($status);

        return $this->getStatusMap()[$status] ?? null;
    }

    protected function getStatusMap(): array
    {
        return [];
    }
}
