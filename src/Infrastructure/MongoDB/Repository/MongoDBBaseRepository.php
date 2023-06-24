<?php

declare(strict_types=1);

namespace Zisato\Projection\Infrastructure\MongoDB\Repository;

use MongoDB\Client;
use MongoDB\Collection;

abstract class MongoDBBaseRepository
{
    private ?Collection $collection = null;

    public function __construct(private readonly Client $client) {}

    abstract public function getDatabaseName(): string;

    abstract public function getCollectionName(): string;

    protected function client(): Client
    {
        return $this->client;
    }

    protected function collection(): Collection
    {
        if (!$this->collection instanceof Collection) {
            $this->collection = $this->client
                ->selectCollection($this->getDatabaseName(), $this->getCollectionName()); 
        }

        return $this->collection;
    }
}
