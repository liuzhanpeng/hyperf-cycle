<?php

declare(strict_types=1);

namespace Lzpeng\HyperfCycle;

use Cycle\ORM\EntityManager;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Factory;
use Cycle\ORM\Heap\Heap;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Transaction\CommandGeneratorInterface;
use Hyperf\Context\Context;

/**
 * Cycle创建工厂
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class ORMFactory
{
    /**
     * 每个进程将缓存一个orm实例，用于创建各个请求协程的EntityManager
     *
     * @var ORMInterface|null
     */
    private ?ORMInterface $instance = null;

    /**
     * @param DatabaseManager $databaseManager
     * @param SchemaInterface $schema
     * @param array|null $defaultSchemaClasses
     * @param CommandGeneratorInterface|null $commandGenerator
     */
    public function __construct(
        private DatabaseManager $databaseManager,
        private SchemaInterface $schema,
        private ?array $defaultSchemaClasses = null,
        private ?CommandGeneratorInterface $commandGenerator = null
    ) {
    }

    /**
     * 获取EntityManager实例
     *
     * @return EntityManagerInterface
     */
    public function entityManager(): EntityManagerInterface
    {
        return new EntityManager($this->orm());
    }

    /**
     * 获取ORM实例
     *
     * @return ORMInterface
     */
    public function orm(): ORMInterface
    {
        $key = $this->getContextKey();
        if (!Context::has($key)) {
            if (is_null($this->instance)) {
                $factory = new Factory($this->databaseManager);
                if (!is_null($this->defaultSchemaClasses)) {
                    $factory = $factory->withDefaultSchemaClasses($this->defaultSchemaClasses);
                }

                $this->instance = new ORM(
                    factory: $factory,
                    schema: $this->schema,
                    commandGenerator: $this->commandGenerator
                );

                // 每个Coroutine生成一个独立heap的ORM实例
                Context::set($key, $this->instance->with(heap: new Heap()));
            }
        }

        return Context::get($key);
    }

    /**
     * 返回Context Key
     *
     * @return string
     */
    private function getContextKey(): string
    {
        return sprintf('cycle.orm');
    }
}
