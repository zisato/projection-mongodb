<?php

namespace Zisato\Projection\Tests\Unit\Infrastructure\MongoDB\Repository;

use Zisato\Projection\Exception\ProjectionModelNotFoundException;
use Zisato\Projection\Tests\Stub\ValueObject\PhpUnitProjectionModel;
use Zisato\Projection\Tests\Stub\Infrastructure\MongoDB\Repository\PhpUnitMongoDBReadRepository;
use Zisato\Projection\Tests\Stub\Infrastructure\MongoDB\Repository\PhpUnitMongoDBWriteRepository;
use MongoDB\Client;
use PHPUnit\Framework\TestCase;

class MongoDBWriteRepositoryTest extends TestCase
{
    private $mongoDBReadRepository;
    private $mongoDBWriteRepository;

    protected function setUp(): void
    {
        $client = new Client(getenv('MONGO_URL'));

        $this->mongoDBReadRepository = new PhpUnitMongoDBReadRepository($client);
        $this->mongoDBWriteRepository = new PhpUnitMongoDBWriteRepository($client);
    }

    public function testSaveInsertSuccessfully()
    {
        $id = '11111111-1111-1111-1111-111111111111';
        $model = [
            'id' => $id,
            'attributes' => [
                'name' => 'Test 1'
            ],
            'relationships' => [
                'test' => [
                    'id' => '11111111-1111-1111-1111-111111111112',
                ]
            ]
        ];
        $this->mongoDBWriteRepository->save(PhpUnitProjectionModel::fromData($model));

        $model = $this->mongoDBReadRepository->get($id);

        static::assertEquals($id, $model->data()['id']);
    }

    public function testSaveUpdateSuccessfully()
    {
        $id = '11111111-1111-1111-1111-111111111111';
        $modelCreate = [
            'id' => $id,
            'attributes' => [
                'name' => 'Test 1'
            ],
            'relationships' => [
                'test' => [
                    'id' => '11111111-1111-1111-1111-111111111112',
                ]
            ]
        ];
        $this->mongoDBWriteRepository->save(PhpUnitProjectionModel::fromData($modelCreate));

        $newName = 'Test New';
        $modelUpdate = [
            'id' => $id,
            'attributes' => [
                'name' => $newName
            ],
            'relationships' => [
                'test' => [
                    'id' => '11111111-1111-1111-1111-111111111112',
                ]
            ]
        ];
        $this->mongoDBWriteRepository->save(PhpUnitProjectionModel::fromData($modelUpdate));

        $model = $this->mongoDBReadRepository->get($id);

        static::assertEquals($newName, $model->data()['attributes']['name']);
    }

    public function testDeleteSuccessfully()
    {
        $this->expectException(ProjectionModelNotFoundException::class);

        $id = '11111111-1111-1111-1111-111111111111';
        $model = [
            'id' => $id,
            'attributes' => [
                'name' => 'Test 1'
            ],
            'relationships' => [
                'test' => [
                    'id' => '11111111-1111-1111-1111-111111111112',
                ]
            ]
        ];
        $this->mongoDBWriteRepository->save(PhpUnitProjectionModel::fromData($model));

        $this->mongoDBWriteRepository->delete($id);

        $this->mongoDBReadRepository->get($id);
    }
}
