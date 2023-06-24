<?php

declare(strict_types=1);

namespace Zisato\Projection\Infrastructure\MongoDB\Repository;

use Zisato\Projection\Repository\ProjectionWriteRepository;
use Zisato\Projection\ValueObject\ProjectionModel;

abstract class MongoDBWriteRepository extends MongoDBBaseRepository implements ProjectionWriteRepository
{
    public function save(ProjectionModel $projectionModel): void
    {
        $this->collection()
            ->replaceOne([
                'id' => $projectionModel->data()['id'],
            ], $projectionModel->data(), [
                'upsert' => true,
            ])
        ;
    }

    public function delete(string $id): void
    {
        $this->collection()
            ->deleteOne([
                'id' => $id,
            ])
        ;
    }
}
