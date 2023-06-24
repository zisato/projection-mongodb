<?php

declare(strict_types=1);

namespace Zisato\Projection\Infrastructure\MongoDB\Repository;

use MongoDB\Client;
use Zisato\Projection\Criteria\Criteria;
use Zisato\Projection\Exception\ProjectionModelNotFoundException;
use Zisato\Projection\Infrastructure\MongoDB\Criteria\MongoDBCriteriaTransformer;
use Zisato\Projection\Infrastructure\MongoDB\OrderBy\MongoDBOrderByTransformer;
use Zisato\Projection\OrderBy\OrderBy;
use Zisato\Projection\Repository\ProjectionReadRepository;
use Zisato\Projection\Repository\ProjectionWriteRepository;
use Zisato\Projection\ValueObject\ProjectionModel;
use Zisato\Projection\ValueObject\ProjectionModelCollection;

abstract class MongoDBRepository extends MongoDBBaseRepository implements ProjectionReadRepository, ProjectionWriteRepository
{
    /**
     * @var array<string, string>
     */
    final public const CURSOR_TYPE_MAP = [
        'root' => 'array',
        'document' => 'array',
        'array' => 'array',
    ];

    /**
     * @var int
     */
    final public const DEFAULT_LIMIT = 20;

    /**
     * @var int
     */
    final public const DEFAULT_OFFSET = 0;

    public function __construct(Client $client)
    {
        parent::__construct($client);
    }

    public function get(string $id): ProjectionModel
    {
        /** @var array<mixed, mixed> $data */
        $data = $this->collection()
            ->findOne(
                [
                    'id' => $id,
                ],
                [
                    'projection' => [
                        '_id' => false,
                    ],
                    'typeMap' => self::CURSOR_TYPE_MAP,
                ]
            );

        if ($data === []) {
            throw new ProjectionModelNotFoundException(sprintf('Projection with id %s not found', $id));
        }

        return $this->createProjectionModel($data);
    }

    public function findBy(
        ?Criteria $criteria = null,
        ?int $offset = self::DEFAULT_OFFSET,
        ?int $limit = self::DEFAULT_LIMIT,
        ?OrderBy $orderBy = null
    ): ProjectionModelCollection {
        $parsedCriteria = ($criteria instanceof Criteria)
            ? MongoDBCriteriaTransformer::transform($criteria)
            : [];

        /** @var array<mixed, mixed> $data */
        $data = $this->collection()
            ->find(
                $parsedCriteria,
                [
                    'projection' => [
                        '_id' => false,
                    ],
                    'limit' => $limit,
                    'skip' => $offset,
                    'sort' => ($orderBy instanceof OrderBy)
                        ? MongoDBOrderByTransformer::transform($orderBy)
                        : [],
                    'typeMap' => self::CURSOR_TYPE_MAP,
                ]
            );

        $total = $this->collection()
            ->countDocuments($parsedCriteria, [
                'typeMap' => self::CURSOR_TYPE_MAP,
            ]);

        $collection = ProjectionModelCollection::create($total);
        foreach ($data as $item) {
            $collection->add($this->createProjectionModel($item));
        }

        return $collection;
    }

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

    /**
     * @param array<mixed, mixed> $data
     */
    protected function createProjectionModel(array $data): ProjectionModel
    {
        /** @var callable $callable */
        $callable = [static::getProjectionModelName(), 'fromData'];

        return \call_user_func($callable, $data);
    }
}
