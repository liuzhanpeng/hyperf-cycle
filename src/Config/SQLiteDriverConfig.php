<?php

declare(strict_types=1);

namespace Lzpeng\HyperfCycle\Config;

use Cycle\Database\Config\SQLiteDriverConfig as CycleSQLiteDriverConfig;
use Cycle\Database\Config\SQLite\ConnectionConfig;
use Cycle\Database\Config\SQLite\MemoryConnectionConfig;
use Cycle\Database\Driver\SQLite\SQLiteDriver;

class SQLiteDriverConfig extends CycleSQLiteDriverConfig implements PoolConfigInterface
{
    public function __construct(
        ?ConnectionConfig $connection = null,
        string $driver = SQLiteDriver::class,
        bool $reconnect = true,
        string $timezone = 'UTC',
        bool $queryCache = true,
        bool $readonlySchema = false,
        bool $readonly = false,
        private array $options = [],
    ) {
        /** @psalm-suppress ArgumentTypeCoercion */
        parent::__construct(
            connection: $connection ?? new MemoryConnectionConfig(),
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
        return $this->options;
    }
}
