<?php

declare(strict_types=1);

namespace Lzpeng\HyperfCycle\Pool;

use Cycle\Database\LoggerFactoryInterface;
use Lzpeng\HyperfCycle\Config\DatabaseConfig;
use Psr\Container\ContainerInterface;

/**
 * 数据库连接池工厂
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class PoolFactory
{
    /**
     * 连接池实例集合
     * 
     * @var array<string, Pool>
     */
    protected array $pools = [];

    /**
     * @param ContainerInterface $container
     * @param LoggerFactoryInterface|null $loggerFactory
     */
    public function __construct(
        protected ContainerInterface $container,
        private ?LoggerFactoryInterface $loggerFactory = null
    ) {
    }

    /**
     * 创建并返回数据库连接池
     *
     * @param string $name
     * @param DatabaseConfig $config
     * @return Pool
     */
    public function create(string $name, DatabaseConfig $config): Pool
    {
        if (!isset($this->pools[$name])) {
            $this->pools[$name] = new Pool($this->container, $name, $config, $this->loggerFactory);
        }

        return $this->pools[$name];
    }
}
