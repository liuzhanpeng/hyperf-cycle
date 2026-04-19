<?php

use Cycle\Database\Config\MySQL\TcpConnectionConfig as MySQLTcpConnectionConfig;
use Cycle\Database\Config\Postgres\TcpConnectionConfig as PostgresTcpConnectionConfig;
use Cycle\Database\Config\SQLite\FileConnectionConfig;
use Cycle\Database\Config\SQLServer\TcpConnectionConfig as SQLServerTcpConnectionConfig;
use Lzpeng\HyperfCycle\Config\MySQLDriverConfig;
use Lzpeng\HyperfCycle\Config\PostgresDriverConfig;
use Lzpeng\HyperfCycle\Config\SQLiteDriverConfig;
use Lzpeng\HyperfCycle\Config\SQLServerDriverConfig;

it('returns mysql pool options', function () {
    $config = new MySQLDriverConfig(
        connection: new MySQLTcpConnectionConfig(database: 'testing'),
        poolOptions: ['max_connections' => 20],
    );

    expect($config->poolOptions())->toBe(['max_connections' => 20]);
});

it('returns postgres pool options', function () {
    $config = new PostgresDriverConfig(
        connection: new PostgresTcpConnectionConfig(database: 'testing'),
        poolOptions: ['min_connections' => 2],
    );

    expect($config->poolOptions())->toBe(['min_connections' => 2]);
});

it('returns sqlserver pool options', function () {
    $config = new SQLServerDriverConfig(
        connection: new SQLServerTcpConnectionConfig(database: 'testing'),
        poolOptions: ['wait_timeout' => 1.5],
    );

    expect($config->poolOptions())->toBe(['wait_timeout' => 1.5]);
});

it('returns sqlite pool options through the pool config interface', function () {
    $config = new SQLiteDriverConfig(
        connection: new FileConnectionConfig(database: ':memory:'),
        poolOptions: ['max_connections' => 1],
    );

    expect($config->poolOptions())->toBe(['max_connections' => 1]);
});
