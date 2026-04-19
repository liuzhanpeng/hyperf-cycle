<?php

use Cycle\Database\Config\MySQL\TcpConnectionConfig;
use Cycle\Database\Driver\MySQL\MySQLDriver;
use Cycle\Database\Exception\ConfigException;
use Lzpeng\HyperfCycle\Config\DatabaseConfig;
use Lzpeng\HyperfCycle\Config\MySQLDriverConfig;

function makeTestMysqlDriverConfig(array $poolOptions = []): MySQLDriverConfig
{
    return new MySQLDriverConfig(
        connection: new TcpConnectionConfig(
            database: 'testing',
            host: '127.0.0.1',
            port: 3306,
            user: 'root',
            password: '',
        ),
        poolOptions: $poolOptions,
    );
}

function makeTestDatabaseConfig(array $overrides = []): DatabaseConfig
{
    $base = [
        'default' => 'default',
        'databases' => [
            'default' => [
                'connection' => 'mysql',
            ],
        ],
        'connections' => [
            'mysql' => makeTestMysqlDriverConfig(['max_connections' => 10]),
        ],
    ];

    return new DatabaseConfig(array_replace_recursive($base, $overrides));
}

it('returns the configured default database name', function () {
    $config = makeTestDatabaseConfig(['default' => 'reporting']);

    expect($config->getDefaultDatabase())->toBe('reporting');
});

it('falls back to the library default database name', function () {
    $config = new DatabaseConfig([]);

    expect($config->getDefaultDatabase())->toBe(DatabaseConfig::DEFAULT_DATABASE);
});

it('builds database partials from cycle compatible config aliases', function () {
    $config = makeTestDatabaseConfig([
        'databases' => [
            'reporting' => [
                'prefix' => 'pre_',
                'write' => 'mysql',
                'read' => 'mysql-read',
            ],
        ],
        'connections' => [
            'mysql-read' => makeTestMysqlDriverConfig(['max_connections' => 5]),
        ],
    ]);

    $database = $config->getDatabase('reporting');

    expect($database->getName())->toBe('reporting')
        ->and($database->getPrefix())->toBe('pre_')
        ->and($database->getDriver())->toBe('mysql')
        ->and($database->getReadDriver())->toBe('mysql-read');
});

it('returns the configured driver config instance for a database', function () {
    $driverConfig = makeTestMysqlDriverConfig(['max_connections' => 20]);

    $config = new DatabaseConfig([
        'default' => 'default',
        'databases' => [
            'default' => ['connection' => 'mysql'],
        ],
        'connections' => [
            'mysql' => $driverConfig,
        ],
    ]);

    expect($config->getDriverConfig('default'))->toBe($driverConfig);
});

it('creates driver instances from the configured driver config', function () {
    $config = makeTestDatabaseConfig();
    $driver = $config->getDriver('mysql');

    expect($driver)->toBeInstanceOf(MySQLDriver::class)
        ->and($driver->getName())->toBe('mysql');
});

it('throws when the requested database or driver is missing', function () {
    $config = makeTestDatabaseConfig();

    expect(fn () => $config->getDatabase('missing'))
        ->toThrow(ConfigException::class, 'Undefined database');

    expect(fn () => $config->getDriver('missing'))
        ->toThrow(ConfigException::class, 'Undefined driver');
});
