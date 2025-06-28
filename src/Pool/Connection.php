<?php

declare(strict_types=1);

namespace Lzpeng\HyperfCycle\Pool;

use Cycle\Database\Database;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\Driver\DriverInterface;
use Cycle\Database\LoggerFactoryInterface;
use Hyperf\Contract\PoolInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Pool\Connection as AbstractConnection;
use Hyperf\Pool\Exception\ConnectionException;
use Lzpeng\HyperfCycle\Config\DatabaseConfig;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * 表示一个数据库连接池连接, 封装了DatabaseInterface实例
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class Connection extends AbstractConnection
{
    /**
     * @var DatabaseInterface|null
     */
    protected ?DatabaseInterface $database = null;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @param ContainerInterface $container
     * @param PoolInterface $pool
     * @param DatabaseConfig $config
     * @param LoggerFactoryInterface|null $loggerFactory
     */
    public function __construct(
        ContainerInterface $container,
        protected PoolInterface $pool,
        protected string $name,
        protected DatabaseConfig $config,
        protected ?LoggerFactoryInterface $loggerFactory
    ) {
        parent::__construct($container, $pool);

        $this->logger = $container->get(StdoutLoggerInterface::class);

        $this->reconnect();
    }

    /**
     * @inheritDoc
     *
     * @return boolean
     */
    public function reconnect(): bool
    {
        $this->close();

        $databasePartial = $this->config->getDatabase($this->name);

        $this->database = new Database(
            $databasePartial->getName(),
            $databasePartial->getPrefix(),
            $this->createDriver($databasePartial->getDriver()),
            $databasePartial->getReadDriver() ? $this->createDriver($databasePartial->getReadDriver()) : null
        );

        $this->lastUseTime = microtime(true);
        return true;
    }

    /**
     * @inheritDoc
     *
     * @return boolean
     */
    public function close(): bool
    {
        if ($this->database instanceof DatabaseInterface) {
            $this->database->getDriver()->disconnect();
        }

        $this->database = null;

        return true;
    }

    /**
     * @inheritDoc
     *
     * @return DatabaseInterface
     */
    public function getActiveConnection(): DatabaseInterface
    {
        if ($this->check()) {
            return $this->database;
        }

        if (!$this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }

        return $this->database;
    }

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function release(): void
    {
        try {
            if ($this->database instanceof DatabaseInterface) {
                if ($this->database->getDriver()->getTransactionLevel() > 0) {
                    $this->database->getDriver()->rollbackTransaction();
                    $this->logger->error('Maybe you\'ve forgotten to commit or rollback the transaction.');
                }
            }
        } catch (\Throwable $ex) {
            $this->logger->error('Rollback connection failed, caused by ' . $ex);
            $this->lastUseTime = 0.0;
        }

        parent::release();
    }

    /**
     * 创建 driver
     *
     * @param string $driver
     * @return DriverInterface
     */
    private function createDriver(string $driver): DriverInterface
    {
        $driverObject = $this->config->getDriver($driver);
        if ($driverObject instanceof LoggerAwareInterface) {
            if (!is_null($this->loggerFactory)) {
                $logger = $this->loggerFactory->getLogger($driverObject);
            } else {
                $logger = new NullLogger();
            }

            $driverObject->setLogger($logger);
        }

        return $driverObject;
    }
}
