<?php

declare(strict_types=1);

namespace Lzpeng\HyperfCycle;

use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\Database\LoggerFactoryInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Lzpeng\HyperfCycle\Config\DatabaseConfig;
use Lzpeng\HyperfCycle\Pool\PoolFactory;

use function Hyperf\Coroutine\defer;

/**
 * 支持连接池的DatabaseManager
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
final class DatabaseManager implements DatabaseProviderInterface
{
    /**
     * @var PoolFactory
     */
    private PoolFactory $poolFactory;

    /**
     * @param DatabaseConfig $config
     */
    public function __construct(private DatabaseConfig $config, ?LoggerFactoryInterface $loggerFactory = null)
    {
        $this->poolFactory = new PoolFactory(ApplicationContext::getContainer(), $loggerFactory);
    }

    /**
     * 创建并返回一个Database实例
     *
     * @param string|null $name
     * @return DatabaseInterface
     */
    public function database(?string $name = null): DatabaseInterface
    {
        if ($name === null) {
            $name = $this->config->getDefaultDatabase();
        }

        $key = $this->getContextKey($name);

        if (!Context::has($key)) {
            $pool = $this->poolFactory->create($name, $this->config);
            $connection = $pool->get();

            try {
                Context::set($key, $connection->getConnection());
            } finally {
                defer(function () use ($connection, $key) {
                    Context::set($key, null);
                    $connection->release();
                });
            }
        }

        return Context::get($key);
    }

    /**
     * 获取数据库配置
     */
    public function config(): DatabaseConfig
    {
        return $this->config;
    }

    /**
     * 返回数据库连接的上下文key
     *
     * @param string $name
     * @return string
     */
    private function getContextKey(string $name): string
    {
        return sprintf('cycle.database.%s', $name);
    }
}
