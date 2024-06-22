<?php

declare(strict_types=1);

namespace Lzpeng\HyperfCycle\Pool;

use Cycle\Database\LoggerFactoryInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Frequency;
use Hyperf\Pool\Pool as AbstractPool;
use Lzpeng\HyperfCycle\Config\DatabaseConfig;
use Lzpeng\HyperfCycle\Config\PoolConfigInterface;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

/**
 * 数据库连接池
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class Pool extends AbstractPool
{
    /**
     * @param ContainerInterface $container
     * @param string $name 连接池名称
     */
    public function __construct(
        ContainerInterface $container,
        protected string $name,
        protected DatabaseConfig $config,
        protected ?LoggerFactoryInterface $loggerFactory = null
    ) {
        $this->frequency = make(Frequency::class, [$this]);

        $driverConfig = $this->config->getDriverConfig($name);
        $poolConfig = [];
        if ($driverConfig instanceof PoolConfigInterface) {
            $poolConfig = $driverConfig->poolOptions();
        }

        parent::__construct($container, $poolConfig);
    }

    /**
     * @inheritDoc
     *
     * @return ConnectionInterface
     */
    protected function createConnection(): ConnectionInterface
    {
        return new Connection($this->container, $this, $this->name, $this->config, $this->loggerFactory);
    }
}
