<?php

namespace Zisato\Projection\Tests\Stub\Infrastructure\MongoDB\Repository;

use Zisato\Projection\Infrastructure\MongoDB\Repository\MongoDBWriteRepository;

class PhpUnitMongoDBWriteRepository extends MongoDBWriteRepository
{
    public function getDatabaseName(): string
    {
        return 'projection';
    }

    public function getCollectionName(): string
    {
        return 'test';
    }
}
