<?php

declare(strict_types=1);

namespace Zisato\Projection\Infrastructure\MongoDB\Criteria;

use Zisato\Projection\Criteria\Condition;
use Zisato\Projection\Criteria\Criteria;

final class MongoDBCriteriaTransformer
{
    /**
     * @var array<string, string>
     */
    private const CONDITION_MAPPING = [
        Condition::CONDITION_EQUALS => '$eq',
        Condition::CONDITION_GREATER => '$gt',
        Condition::CONDITION_GREATER_EQUALS => '$gte',
        Condition::CONDITION_IN => '$in',
        Condition::CONDITION_LIKE => '$regex',
        Condition::CONDITION_LOWER => '$lt',
        Condition::CONDITION_LOWER_EQUALS => '$lte',
        Condition::CONDITION_NOT_EQUALS => '$ne',
        Condition::CONDITION_NOT_IN => '$nin',
        Condition::CONDITION_NOT_LIKE => '$regex',
    ];

    /**
     * @return array<mixed, mixed>
     */
    public static function transform(Criteria $criteria): array
    {
        $result = [];

        foreach ($criteria->values() as $data) {
            $condition = $data->condition()
                ->value();
            $value = self::normalizeValue($condition, $data->value());

            $result[$data->column()] = [
                self::CONDITION_MAPPING[$condition] => $value,
            ];
        }

        return $result;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function normalizeValue(string $condition, $value)
    {
        if ($condition === Condition::CONDITION_LIKE) {
            $value = new \MongoDB\BSON\Regex(\sprintf('.*%s.*', $value), 'i');
        }

        if ($condition === Condition::CONDITION_NOT_LIKE) {
            $value = new \MongoDB\BSON\Regex(\sprintf('^((?!%s).)*$', $value), 'i');
        }

        if ($condition === Condition::CONDITION_IN ||
            $condition === Condition::CONDITION_NOT_IN
        ) {
            $value = is_array($value) ? $value : [$value];
        }

        return $value;
    }
}
