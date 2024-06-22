<?php

declare(strict_types=1);

namespace Lzpeng\HyperfCycle\Config;

use Cycle\Database\Config\PostgresDriverConfig as CyclePostgresDriverConfig;
use Cycle\Database\Config\Postgres\ConnectionConfig;
use Cycle\Database\Driver\Postgres\PostgresDriver;

class PostgresDriverConfig extends CyclePostgresDriverConfig implements PoolConfigInterface
{
    public function __construct(
        ConnectionConfig $connection,
        iterable|string $schema = self::DEFAULT_SCHEMA,
        string $driver = PostgresDriver::class,
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
