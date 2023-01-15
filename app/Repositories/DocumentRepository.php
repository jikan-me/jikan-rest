<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

/**
 * Represents a table in the document database which doesn't have a model representation in the code base.
 */
final class DocumentRepository extends DatabaseRepository
{
    private string $tableName;

    public function __construct(string $tableName)
    {
        parent::__construct(fn() => DB::table($tableName), fn($x, $y) => throw new \Exception("Not supported"));
        $this->tableName = $tableName;
    }

    public function scrape(int|string $id): array
    {
        throw new \Exception("Not supported");
    }

    public function tableName(): string
    {
        return $this->tableName;
    }

    public function createEntity()
    {
        throw new \Exception("Not supported");
    }
}
