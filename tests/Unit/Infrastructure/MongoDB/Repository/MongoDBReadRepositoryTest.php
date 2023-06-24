<?php

namespace Zisato\Projection\Tests\Unit\Infrastructure\MongoDB\Repository;

use MongoDB\Client;
use PHPUnit\Framework\TestCase;
use Zisato\Projection\Criteria\Condition;
use Zisato\Projection\Criteria\Criteria;
use Zisato\Projection\Criteria\CriteriaItem;
use Zisato\Projection\Exception\ProjectionModelNotFoundException;
use Zisato\Projection\OrderBy\Direction;
use Zisato\Projection\OrderBy\OrderBy;
use Zisato\Projection\Infrastructure\MongoDB\Repository\MongoDBReadRepository;
use Zisato\Projection\Infrastructure\MongoDB\Repository\MongoDBWriteRepository;
use Zisato\Projection\Tests\Stub\ValueObject\PhpUnitProjectionModel;
use Zisato\Projection\Tests\Stub\Infrastructure\MongoDB\Repository\PhpUnitMongoDBReadRepository;
use Zisato\Projection\Tests\Stub\Infrastructure\MongoDB\Repository\PhpUnitMongoDBWriteRepository;

class MongoDBReadRepositoryTest extends TestCase
{
    private MongoDBReadRepository $mongoDBReadRepository;
    private MongoDBWriteRepository $mongoDBWriteRepository;

    protected function setUp(): void
    {
        $client = new Client(getenv('MONGO_URL'));

        $this->mongoDBReadRepository = new PhpUnitMongoDBReadRepository($client);
        $this->mongoDBWriteRepository = new PhpUnitMongoDBWriteRepository($client);

        $this->loadData();
    }

    public function testGetSuccessfully(): void
    {
        $id = '11111111-1111-1111-1111-111111111111';
        $model = $this->mongoDBReadRepository->get($id);

        static::assertEquals($id, $model->data()['id']);
    }

    public function testGetFailed(): void
    {
        $this->expectException(ProjectionModelNotFoundException::class);

        $this->mongoDBReadRepository->get('foo');
    }

    /**
     * @dataProvider criteriaData
     */
    public function testFindByCriteriaSuccessfully(CriteriaItem $criteriaItem, array $expectedValues): void
    {
        $criteria = new Criteria($criteriaItem);

        $collection = $this->mongoDBReadRepository->findBy($criteria);

        $data = iterator_to_array($collection->data());

        foreach ($data as $index => $datum) {
            static::assertEquals($expectedValues[$index]['id'], $datum->data()['id']);
            static::assertEquals($expectedValues[$index]['name'], $datum->data()['attributes']['name']);
        }
    }

    public function testFindByMultipleCriteriaSuccessfully(): void
    {
        $id = '11111111-1111-1111-1111-111111111111';
        $name = 'Test 1';
        $testId = '11111111-1111-1111-1111-111111111112';
        $criteria = new Criteria();
        $criteria->add('attributes.name', $name);
        $criteria->add('relationships.test.id', $testId);

        $collection = $this->mongoDBReadRepository->findBy($criteria);

        $data = iterator_to_array($collection->data());

        static::assertEquals(1, $collection->count());
        static::assertEquals($id, $data[0]->data()['id']);
        static::assertEquals($name, $data[0]->data()['attributes']['name']);
        static::assertEquals($testId, $data[0]->data()['relationships']['test']['id']);
    }

    public function testFindByOffsetSuccessfully(): void
    {
        $id = '22222222-2222-2222-2222-222222222222';
        $name = 'Test 2';
        $offset = 1;
        $collection = $this->mongoDBReadRepository->findBy(null, $offset);

        $data = iterator_to_array($collection->data());

        static::assertEquals(2, $collection->count());
        static::assertEquals($id, $data[0]->data()['id']);
        static::assertEquals($name, $data[0]->data()['attributes']['name']);
    }

    public function testFindByLimitSuccessfully(): void
    {
        $id = '22222222-2222-2222-2222-222222222222';
        $name = 'Test 2';
        $limit = 2;
        $collection = $this->mongoDBReadRepository->findBy(null, null, $limit);

        $data = iterator_to_array($collection->data());

        static::assertEquals(2, $collection->count());
        static::assertEquals($id, $data[1]->data()['id']);
        static::assertEquals($name, $data[1]->data()['attributes']['name']);
    }

    public function testFindByOrderBySuccessfully()
    {
        $id = '33333333-3333-3333-3333-333333333333';
        $name = 'Test 3';
        $orderBy = new OrderBy();
        $orderBy->add('attributes.name', Direction::desc());
        $collection = $this->mongoDBReadRepository->findBy(null, null, null, $orderBy);

        $data = iterator_to_array($collection->data());

        static::assertEquals(3, $collection->count());
        static::assertEquals($id, $data[0]->data()['id']);
        static::assertEquals($name, $data[0]->data()['attributes']['name']);
    }

    public static function criteriaData(): array
    {
        return [
            [
                new CriteriaItem('attributes.name', 'Test 1', Condition::eq()),
                [
                    [
                        'id' => '11111111-1111-1111-1111-111111111111',
                        'name' => 'Test 1'
                    ]
                ]
            ],
            [
                new CriteriaItem('attributes.age', 42, Condition::gt()),
                [
                    [
                        'id' => '33333333-3333-3333-3333-333333333333',
                        'name' => 'Test 3'
                    ]
                ]
            ],
            [
                new CriteriaItem('attributes.age', 42, Condition::gte()),
                [
                    [
                        'id' => '22222222-2222-2222-2222-222222222222',
                        'name' => 'Test 2'
                    ],
                    [
                        'id' => '33333333-3333-3333-3333-333333333333',
                        'name' => 'Test 3'
                    ]
                ]
            ],
            [
                new CriteriaItem('attributes.age', 42, Condition::lt()),
                [
                    [
                        'id' => '11111111-1111-1111-1111-111111111111',
                        'name' => 'Test 1'
                    ]
                ]
            ],
            [
                new CriteriaItem('attributes.age', 42, Condition::lte()),
                [
                    [
                        'id' => '11111111-1111-1111-1111-111111111111',
                        'name' => 'Test 1'
                    ],
                    [
                        'id' => '22222222-2222-2222-2222-222222222222',
                        'name' => 'Test 2'
                    ],
                ]
            ],
            [
                new CriteriaItem('attributes.age', 42, Condition::in()),
                [
                    [
                        'id' => '22222222-2222-2222-2222-222222222222',
                        'name' => 'Test 2'
                    ],
                ]
            ],
            [
                new CriteriaItem('attributes.age', [42, 57], Condition::in()),
                [
                    [
                        'id' => '22222222-2222-2222-2222-222222222222',
                        'name' => 'Test 2'
                    ],
                    [
                        'id' => '33333333-3333-3333-3333-333333333333',
                        'name' => 'Test 3'
                    ]
                ]
            ],
            [
                new CriteriaItem('id', '2222', Condition::like()),
                [
                    [
                        'id' => '22222222-2222-2222-2222-222222222222',
                        'name' => 'Test 2'
                    ],
                ]
            ],
            [
                new CriteriaItem('attributes.name', 'Test 1', Condition::notEq()),
                [
                    [
                        'id' => '22222222-2222-2222-2222-222222222222',
                        'name' => 'Test 2'
                    ],
                    [
                        'id' => '33333333-3333-3333-3333-333333333333',
                        'name' => 'Test 3'
                    ]
                ]
            ],
            [
                new CriteriaItem('id', '2222', Condition::notLike()),
                [
                    [
                        'id' => '11111111-1111-1111-1111-111111111111',
                        'name' => 'Test 1'
                    ],
                    [
                        'id' => '33333333-3333-3333-3333-333333333333',
                        'name' => 'Test 3'
                    ]
                ]
            ],

            [
                new CriteriaItem('attributes.age', 42, Condition::notIn()),
                [
                    [
                        'id' => '11111111-1111-1111-1111-111111111111',
                        'name' => 'Test 1'
                    ],
                    [
                        'id' => '33333333-3333-3333-3333-333333333333',
                        'name' => 'Test 3'
                    ]
                ]
            ],
            [
                new CriteriaItem('attributes.age', [42, 57], Condition::notIn()),
                [
                    [
                        'id' => '11111111-1111-1111-1111-111111111111',
                        'name' => 'Test 1'
                    ]
                ]
            ],
        ];
    }

    private function loadData(): void
    {
        $data = [
            [
                'id' => '11111111-1111-1111-1111-111111111111',
                'attributes' => [
                    'name' => 'Test 1',
                    'age' => 23,
                ],
                'relationships' => [
                    'test' => [
                        'id' => '11111111-1111-1111-1111-111111111112',
                    ]
                ]
            ],
            [
                'id' => '22222222-2222-2222-2222-222222222222',
                'attributes' => [
                    'name' => 'Test 2',
                    'age' => 42,
                ],
                'relationships' => [
                    'test' => [
                        'id' => '22222222-2222-2222-2222-222222222221',
                    ]
                ]
            ],
            [
                'id' => '33333333-3333-3333-3333-333333333333',
                'attributes' => [
                    'name' => 'Test 3',
                    'age' => 57,
                ],
                'relationships' => [
                    'test' => [
                        'id' => '33333333-3333-3333-3333-333333333331',
                    ]
                ]
            ]
        ];

        foreach ($data as $model) {
            $this->mongoDBWriteRepository->save(PhpUnitProjectionModel::fromData($model));
        }
    }
}
