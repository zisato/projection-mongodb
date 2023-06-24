<?php

namespace Zisato\Projection\Tests\Stub\Infrastructure\MongoDB\Repository;

use Zisato\Projection\Infrastructure\MongoDB\Repository\MongoDBRepository;
use Zisato\Projection\Tests\Stub\ValueObject\PhpUnitProjectionModel;

class PhpUnitMongoDBRepository extends MongoDBRepository
{
    public static function getProjectionModelName(): string
    {
        return PhpUnitProjectionModel::class;
    }

    public function getDatabaseName(): string
    {
        return 'projection';
    }

    public function getCollectionName(): string
    {
        return 'test';
    }
}
