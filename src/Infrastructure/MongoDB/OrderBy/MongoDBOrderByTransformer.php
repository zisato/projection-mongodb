<?php

declare(strict_types=1);

namespace Zisato\Projection\Infrastructure\MongoDB\OrderBy;

use Zisato\Projection\OrderBy\Direction;
use Zisato\Projection\OrderBy\OrderBy;

final class MongoDBOrderByTransformer
{
    /**
     * @var array<string, int>
     */
    private const DIRECTION_MAPPING = [
        Direction::DIRECTION_ASC => 1,
        Direction::DIRECTION_DESC => -1,
    ];

    /**
     * @return array<string, int>
     */
    public static function transform(OrderBy $orderBy): array
    {
        $result = [];

        foreach ($orderBy->values() as $data) {
            $result[$data->column()] = self::DIRECTION_MAPPING[$data->direction()->value()];
        }

        return $result;
    }
}
