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
     * @var array<string, ORMInterface>
     */
    private array $caches = [];

    /**
     * @param DatabaseManager $databaseManager
     * @param SchemaInterface $schema
     * @param CommandGeneratorInterface|null $commandGenerator
     */
    public function __construct(
        private DatabaseManager $databaseManager,
        private SchemaInterface $schema,
        private ?CommandGeneratorInterface $commandGenerator
    ) {
    }

    /**
     * 获取EntityManager实例
     *
     * @param string $name|null
     * @return EntityManagerInterface
     */
    public function entityManager(?string $name = null): EntityManagerInterface
    {
        return new EntityManager($this->orm($name));
    }

    /**
     * 获取ORM实例
     *
     * @param string|null $name
     * @return ORMInterface
     */
    public function orm(?string $name = null): ORMInterface
    {
        $databaseManager = $this->databaseManager;
        $name = $databaseManager->config()->getDefaultDatabase();

        $key = $this->getContextKey($name);
        if (!Context::has($key)) {
            if (!isset($this->caches[$name])) {
                $this->caches[$name] = new ORM(
                    factory: new Factory($databaseManager),
                    schema: $this->schema,
                    commandGenerator: $this->commandGenerator
                );

                // 每个Coroutine生成一个独立heap的ORM实例
                Context::set($key, $this->caches[$name]->with(heap: new Heap()));
            }
        }

        return Context::get($key);
    }

    /**
     * 返回Context Key
     *
     * @param string $name
     * @return string
     */
    private function getContextKey(string $name): string
    {
        return sprintf('cycle.orm.%s', $name);
    }
}
