<?php

namespace Zisato\Projection\Tests\Unit\Infrastructure\MongoDB\Criteria;

use Zisato\Projection\Criteria\Condition;
use Zisato\Projection\Criteria\Criteria;
use Zisato\Projection\Infrastructure\MongoDB\Criteria\MongoDBCriteriaTransformer;
use PHPUnit\Framework\TestCase;

class MongoDBCriteriaTransformerTest extends TestCase
{
    /**
     * @dataProvider getSuccessData
     */
    public function testTransformSuccessfully(string $column, $value, Condition $condition, array $expected): void
    {
        $criteria = new Criteria();
        $criteria->add($column, $value, $condition);

        $result = MongoDBCriteriaTransformer::transform($criteria);

        static::assertEquals($expected, $result);
    }

    public static function getSuccessData(): array
    {
        return [
            [
                'id',
                '11111111-1111-1111-1111-111111111111',
                Condition::eq(),
                [
                    'id' => [
                        '$eq' => '11111111-1111-1111-1111-111111111111',
                    ]
                ]
            ],
            [
                'id',
                '11111111-1111-1111-1111-111111111111',
                Condition::gt(),
                [
                    'id' => [
                        '$gt' => '11111111-1111-1111-1111-111111111111',
                    ]
                ]
            ],
            [
                'id',
                '11111111-1111-1111-1111-111111111111',
                Condition::gte(),
                [
                    'id' => [
                        '$gte' => '11111111-1111-1111-1111-111111111111',
                    ]
                ]
            ],
            [
                'id',
                '11111111-1111-1111-1111-111111111111',
                Condition::lt(),
                [
                    'id' => [
                        '$lt' => '11111111-1111-1111-1111-111111111111',
                    ]
                ]
            ],
            [
                'id',
                '11111111-1111-1111-1111-111111111111',
                Condition::lte(),
                [
                    'id' => [
                        '$lte' => '11111111-1111-1111-1111-111111111111',
                    ]
                ]
            ],
            [
                'id',
                '11111111-1111-1111-1111-111111111111',
                Condition::lte(),
                [
                    'id' => [
                        '$lte' => '11111111-1111-1111-1111-111111111111',
                    ]
                ]
            ],
            [
                'id',
                '11111111-1111-1111-1111-111111111111',
                Condition::like(),
                [
                    'id' => [
                        '$regex' => new \MongoDB\BSON\Regex('.*11111111-1111-1111-1111-111111111111.*', 'i'),
                    ]
                ]
            ],
            [
                'id',
                '11111111-1111-1111-1111-111111111111',
                Condition::in(),
                [
                    'id' => [
                        '$in' => ['11111111-1111-1111-1111-111111111111'],
                    ]
                ]
            ],
            [
                'id',
                ['11111111-1111-1111-1111-111111111111'],
                Condition::in(),
                [
                    'id' => [
                        '$in' => ['11111111-1111-1111-1111-111111111111'],
                    ]
                ]
            ],
            [
                'id',
                '11111111-1111-1111-1111-111111111111',
                Condition::notEq(),
                [
                    'id' => [
                        '$ne' => '11111111-1111-1111-1111-111111111111',
                    ]
                ]
            ],
            [
                'id',
                '11111111-1111-1111-1111-111111111111',
                Condition::notIn(),
                [
                    'id' => [
                        '$nin' => ['11111111-1111-1111-1111-111111111111'],
                    ]
                ]
            ],
            [
                'id',
                ['11111111-1111-1111-1111-111111111111'],
                Condition::notIn(),
                [
                    'id' => [
                        '$nin' => ['11111111-1111-1111-1111-111111111111'],
                    ]
                ]
            ],
            [
                'id',
                '11111111-1111-1111-1111-111111111111',
                Condition::notLike(),
                [
                    'id' => [
                        '$regex' => new \MongoDB\BSON\Regex('^((?!11111111-1111-1111-1111-111111111111).)*$', 'i'),
                    ]
                ]
            ],
        ];
    }
}
