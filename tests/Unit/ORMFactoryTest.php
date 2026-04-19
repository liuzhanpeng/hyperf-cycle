<?php

use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Lzpeng\HyperfCycle\Config\DatabaseConfig;
use Lzpeng\HyperfCycle\DatabaseManager;
use Lzpeng\HyperfCycle\ORMFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\NullLogger;

beforeEach(function () {
    Context::destroy('cycle.orm');
    ApplicationContext::setContainer(makeOrmFactoryTestContainer());
});

function makeOrmFactoryTestContainer(): ContainerInterface
{
    return new class () implements ContainerInterface {
        public function get(string $id)
        {
            if ($id === StdoutLoggerInterface::class) {
                return new class () extends NullLogger implements StdoutLoggerInterface {
                };
            }

            throw new RuntimeException("No entry found for {$id}.");
        }

        public function has(string $id): bool
        {
            return $id === StdoutLoggerInterface::class;
        }
    };
}

function makeOrmFactoryUnderTest(): ORMFactory
{
    $databaseManager = new DatabaseManager(new DatabaseConfig([]));

    return new ORMFactory($databaseManager, new Schema([]));
}

it('reuses the same orm instance within the same context', function () {
    $factory = makeOrmFactoryUnderTest();

    $first = $factory->orm();
    $second = $factory->orm();

    expect($first)->toBeInstanceOf(ORMInterface::class)
        ->and($second)->toBe($first);
});

it('creates an entity manager for the current orm context', function () {
    $factory = makeOrmFactoryUnderTest();

    expect($factory->entityManager())->toBeInstanceOf(EntityManagerInterface::class);
});
