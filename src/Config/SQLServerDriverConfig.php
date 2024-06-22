<?php

declare(strict_types=1);

namespace Lzpeng\HyperfCycle\Config;

use Cycle\Database\Config\SQLServerDriverConfig as CycleSQLServerDriverConfig;
use Cycle\Database\Config\SQLServer\ConnectionConfig;
use Cycle\Database\Driver\SQLServer\SQLServerDriver;

class SQLServerDriverConfig extends CycleSQLServerDriverConfig implements PoolConfigInterface
{
    public function __construct(
        ConnectionConfig $connection,
        string $driver = SQLServerDriver::class,
        bool $reconnect = true,
        string $timezone = 'UTC',
        bool $queryCache = true,
        bool $readonlySchema = false,
        bool $readonly = false,
        array $options = [],
        private array $poolOptions = [],
    ) {
        /** @psalm-suppress ArgumentTypeCoercion */
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
