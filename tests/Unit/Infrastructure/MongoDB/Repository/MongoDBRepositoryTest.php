<?php

namespace Zisato\Projection\Tests\Unit\Infrastructure\MongoDB\Repository;

use Zisato\Projection\Criteria\Condition;
use Zisato\Projection\Criteria\Criteria;
use Zisato\Projection\Criteria\CriteriaItem;
use Zisato\Projection\Exception\ProjectionModelNotFoundException;
use Zisato\Projection\OrderBy\Direction;
use Zisato\Projection\OrderBy\OrderBy;
use Zisato\Projection\OrderBy\OrderByItem;
use Zisato\Projection\Tests\Stub\Infrastructure\MongoDB\Repository\PhpUnitMongoDBRepository;
use Zisato\Projection\Tests\Stub\ValueObject\PhpUnitProjectionModel;
use MongoDB\Client;
use PHPUnit\Framework\TestCase;

class MongoDBRepositoryTest extends TestCase
{
    private Client $client;
    private PhpUnitMongoDBRepository $mongoDBRepository;

    protected function setUp(): void
    {
        $this->client = new Client(getenv('MONGO_URL'));

        $this->mongoDBRepository = new PhpUnitMongoDBRepository($this->client);
    }

    protected function tearDown(): void
    {
        $this->client->dropDatabase('projection');
    }

    public function testSaveInsertSuccessfully(): void
    {
        $id = '1';
        $model = [
            'id' => $id,
            'name' => 'Test 1',
            'testId' => '2',
        ];
        $this->mongoDBRepository->save(PhpUnitProjectionModel::fromData($model));

        $model = $this->mongoDBRepository->get($id);

        static::assertEquals($id, $model->data()['id']);
    }

    public function testSaveUpdateSuccessfully(): void
    {
        $id = '1';
        $modelCreate = [
            'id' => $id,
            'name' => 'Test 1',
            'testId' => '2',
        ];
        $this->mongoDBRepository->save(PhpUnitProjectionModel::fromData($modelCreate));

        $newName = 'Test New';
        $modelUpdate = [
            'id' => $id,
            'name' => $newName,
            'testId' => '2',
        ];
        $this->mongoDBRepository->save(PhpUnitProjectionModel::fromData($modelUpdate));

        $model = $this->mongoDBRepository->get($id);

        static::assertEquals($newName, $model->data()['name']);
    }

    public function testDeleteSuccessfully(): void
    {
        $this->expectException(ProjectionModelNotFoundException::class);

        $id = '1';
        $model = [
            'id' => $id,
            'name' => 'Test 1',
            'testId' => '2',
        ];
        $this->mongoDBRepository->save(PhpUnitProjectionModel::fromData($model));

        $this->mongoDBRepository->delete($id);

        $this->mongoDBRepository->get($id);
    }

    /**
     * @dataProvider getFindByCriteriaData
     */
    public function testFindByCriteria(?Criteria $criteria, array $expectedData): void
    {
        $this->createFindByData();

        $collection = $this->mongoDBRepository->findBy($criteria);

        $this->assertEquals(count($expectedData), $collection->count());
        $this->assertEquals($expectedData, iterator_to_array($collection->data()));
    }

    /**
     * @dataProvider getFindByOrderByData
     */
    public function testOrderBy(?OrderBy $orderBy, array $expectedData): void
    {
        $this->createFindByData();

        $collection = $this->mongoDBRepository->findBy(null, null, null, $orderBy);

        $this->assertEquals(count($expectedData), $collection->count());
        $this->assertEquals($expectedData, iterator_to_array($collection->data()));
    }

    /**
     * @dataProvider getFindByLimitData
     */
    public function testLimit(?int $limit, array $expectedData): void
    {
        $this->createFindByData();

        $collection = $this->mongoDBRepository->findBy(null, null, $limit, null);

        $this->assertEquals(count($expectedData), $collection->count());
        $this->assertEquals($expectedData, iterator_to_array($collection->data()));
    }

    /**
     * @dataProvider getFindByOffsetData
     */
    public function testOffset(?int $offset, array $expectedData): void
    {
        $this->createFindByData();

        $collection = $this->mongoDBRepository->findBy(null, $offset, null, null);

        $this->assertEquals(count($expectedData), $collection->count());
        $this->assertEquals($expectedData, iterator_to_array($collection->data()));
    }

    public static function getFindByCriteriaData(): array
    {
        return [
            // null criteria
            [
                null,
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '1',
                        'name' => 'Test 1',
                        'order' => 1,
                        'testId' => '2',
                    ]),
                    PhpUnitProjectionModel::fromData([
                        'id' => '2',
                        'name' => 'Test 2',
                        'order' => 2,
                    ])
                ]
            ],
            // eq criteria
            [
                new Criteria(new CriteriaItem('id', '2', Condition::eq())),
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '2',
                        'name' => 'Test 2',
                        'order' => 2,
                    ])
                ]
            ],
            // notEq criteria
            [
                new Criteria(new CriteriaItem('id', '2', Condition::notEq())),
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '1',
                        'name' => 'Test 1',
                        'order' => 1,
                        'testId' => '2',
                    ])
                ]
            ],
            // gt criteria
            [
                new Criteria(new CriteriaItem('order', 1, Condition::gt())),
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '2',
                        'name' => 'Test 2',
                        'order' => 2,
                    ])
                ]
            ],
            // gte criteria
            [
                new Criteria(new CriteriaItem('order', 1, Condition::gte())),
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '1',
                        'name' => 'Test 1',
                        'order' => 1,
                        'testId' => '2',
                    ]),
                    PhpUnitProjectionModel::fromData([
                        'id' => '2',
                        'name' => 'Test 2',
                        'order' => 2,
                    ])
                ]
            ],
            // lt criteria
            [
                new Criteria(new CriteriaItem('order', 2, Condition::lt())),
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '1',
                        'name' => 'Test 1',
                        'order' => 1,
                        'testId' => '2',
                    ])
                ]
            ],
            // lte criteria
            [
                new Criteria(new CriteriaItem('order', 2, Condition::lte())),
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '1',
                        'name' => 'Test 1',
                        'order' => 1,
                        'testId' => '2',
                    ]),
                    PhpUnitProjectionModel::fromData([
                        'id' => '2',
                        'name' => 'Test 2',
                        'order' => 2,
                    ])
                ]
            ],
            // in criteria
            [
                new Criteria(new CriteriaItem('id', ['2'], Condition::in())),
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '2',
                        'name' => 'Test 2',
                        'order' => 2,
                    ])
                ]
            ],
            // like criteria
            [
                new Criteria(new CriteriaItem('name', 'test', Condition::like())),
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '1',
                        'name' => 'Test 1',
                        'order' => 1,
                        'testId' => '2',
                    ]),
                    PhpUnitProjectionModel::fromData([
                        'id' => '2',
                        'name' => 'Test 2',
                        'order' => 2,
                    ])
                ]
            ],
            // notIn criteria
            [
                
                new Criteria(new CriteriaItem('id', ['2'], Condition::notIn())),
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '1',
                        'name' => 'Test 1',
                        'order' => 1,
                        'testId' => '2',
                    ])
                ]
            ],
            // notLike criteria
            [
                new Criteria(new CriteriaItem('name', 'test 1', Condition::notLike())),
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '2',
                        'name' => 'Test 2',
                        'order' => 2,
                    ])
                ]
            ]
        ];
    }

    public static function getFindByOrderByData(): array
    {
        return [
            // null order by
            [
                null,
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '1',
                        'name' => 'Test 1',
                        'order' => 1,
                        'testId' => '2',
                    ]),
                    PhpUnitProjectionModel::fromData([
                        'id' => '2',
                        'name' => 'Test 2',
                        'order' => 2,
                    ])
                ]
            ],
            // asc order by
            [
                new OrderBy(new OrderByItem('order', Direction::asc())),
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '1',
                        'name' => 'Test 1',
                        'order' => 1,
                        'testId' => '2',
                    ]),
                    PhpUnitProjectionModel::fromData([
                        'id' => '2',
                        'name' => 'Test 2',
                        'order' => 2,
                    ]),
                ]
            ],
            // desc order by
            [
                new OrderBy(new OrderByItem('order', Direction::desc())),
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '2',
                        'name' => 'Test 2',
                        'order' => 2,
                    ]),
                    PhpUnitProjectionModel::fromData([
                        'id' => '1',
                        'name' => 'Test 1',
                        'order' => 1,
                        'testId' => '2',
                    ]),
                ]
            ],
        ];
    }

    public static function getFindByLimitData(): array
    {
        return [
            // null limit
            [
                null,
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '1',
                        'name' => 'Test 1',
                        'order' => 1,
                        'testId' => '2',
                    ]),
                    PhpUnitProjectionModel::fromData([
                        'id' => '2',
                        'name' => 'Test 2',
                        'order' => 2,
                    ])
                ]
            ],
            // 0 limit
            [
                0,
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '1',
                        'name' => 'Test 1',
                        'order' => 1,
                        'testId' => '2',
                    ]),
                    PhpUnitProjectionModel::fromData([
                        'id' => '2',
                        'name' => 'Test 2',
                        'order' => 2,
                    ])
                ]
            ],
            // 1 offset
            [
                1,
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '1',
                        'name' => 'Test 1',
                        'order' => 1,
                        'testId' => '2',
                    ]),
                ]
            ],
            // 2 limit
            [
                2,
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '1',
                        'name' => 'Test 1',
                        'order' => 1,
                        'testId' => '2',
                    ]),
                    PhpUnitProjectionModel::fromData([
                        'id' => '2',
                        'name' => 'Test 2',
                        'order' => 2,
                    ])
                ]
            ],
        ];
    }

    public static function getFindByOffsetData(): array
    {
        return [
            // null offset
            [
                null,
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '1',
                        'name' => 'Test 1',
                        'order' => 1,
                        'testId' => '2',
                    ]),
                    PhpUnitProjectionModel::fromData([
                        'id' => '2',
                        'name' => 'Test 2',
                        'order' => 2,
                    ])
                ]
            ],
            // 0 offset
            [
                0,
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '1',
                        'name' => 'Test 1',
                        'order' => 1,
                        'testId' => '2',
                    ]),
                    PhpUnitProjectionModel::fromData([
                        'id' => '2',
                        'name' => 'Test 2',
                        'order' => 2,
                    ])
                ]
            ],
            // 1 offset
            [
                1,
                [
                    PhpUnitProjectionModel::fromData([
                        'id' => '2',
                        'name' => 'Test 2',
                        'order' => 2,
                    ]),
                ]
            ],
            // 2 offset
            [
                2,
                []
            ],
        ];
    }

    private function createFindByData(): void
    {
        $model1 = [
            'id' => '1',
            'name' => 'Test 1',
            'order' => 1,
            'testId' => '2',
        ];
        $model2 = [
            'id' => '2',
            'name' => 'Test 2',
            'order' => 2,
        ];
        $this->mongoDBRepository->save(PhpUnitProjectionModel::fromData($model1));
        $this->mongoDBRepository->save(PhpUnitProjectionModel::fromData($model2));
    }
    /*
    public function testFindBySuccessfully(array $projectionModels, ?Criteria $criteria, ?int $offset, ?int $limit, ?OrderBy $orderBy): void
    {
        // TODO: create method to load data and many scenarios
        $model1 = [
            'id' => '1',
            'attributes' => [
                'name' => 'Test 1'
            ],
            'relationships' => [
                'test' => [
                    'id' => '2',
                ]
            ]
        ];
        $model2 = [
            'id' => '2',
            'attributes' => [
                'name' => 'Test 2'
            ],
        ];
        $this->mongoDBRepository->save(PhpUnitProjectionModel::fromData($model1));
        $this->mongoDBRepository->save(PhpUnitProjectionModel::fromData($model2));

        $collection = $this->mongoDBRepository->findBy();
    }
    */
}
