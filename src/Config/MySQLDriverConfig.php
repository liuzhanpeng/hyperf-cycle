<?php

declare(strict_types=1);

namespace Lzpeng\HyperfCycle\Config;

use Cycle\Database\Config\MySQL\ConnectionConfig;
use Cycle\Database\Config\MySQLDriverConfig as CycleMySQLDriverConfig;
use Cycle\Database\Driver\MySQL\MySQLDriver;

class MySQLDriverConfig extends CycleMySQLDriverConfig implements PoolConfigInterface
{
    public function __construct(
        ConnectionConfig $connection,
        string $driver = MySQLDriver::class,
        bool $reconnect = true,
        string $timezone = 'UTC',
        bool $queryCache = true,
        bool $readonlySchema = false,
        bool $readonly = false,
        array $options = [],
        private array $poolOptions = []
    ) {
        parent::__construct(
            connection: $connection,
            driver: $driver,
            reconnect: $reconnect,
            timezone: $timezone,
            queryCache: $queryCache,
            readonlySchema: $readonlySchema,
            readonly: $readonly,
            options: $options,
        );
    }

    public function poolOptions(): array
    {
        return $this->poolOptions;
    }
}
