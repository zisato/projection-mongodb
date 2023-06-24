<?php

namespace Zisato\Projection\Tests\Unit\Infrastructure\MongoDB\OrderBy;

use Zisato\Projection\OrderBy\Direction;
use Zisato\Projection\OrderBy\OrderBy;
use Zisato\Projection\Infrastructure\MongoDB\OrderBy\MongoDBOrderByTransformer;
use PHPUnit\Framework\TestCase;

class MongoDBOrderByTransformerTest extends TestCase
{
    /**
     * @dataProvider getSuccessData
     */
    public function testTransformSuccessfully(string $column, Direction $direction, array $expected): void
    {
        $orderBy = new OrderBy();
        $orderBy->add($column, $direction);

        $result = MongoDBOrderByTransformer::transform($orderBy);

        static::assertEquals($expected, $result);
    }

    public static function getSuccessData(): array
    {
        return [
            [
                'id',
                Direction::asc(),
                [
                    'id' => 1
                ]
            ],
            [
                'id',
                Direction::desc(),
                [
                    'id' => -1
                ]
            ],
        ];
    }
}
