<?php

declare(strict_types=1);

use Cycle\Database\Config\MySQL\TcpConnectionConfig;
use Lzpeng\HyperfCycle\Config\MySQLDriverConfig;

use function Hyperf\Support\env;

return [
    'default' => 'default',
    'databases' => [
        'default' => ['connection' => 'default'],
    ],
    'connections' => [
        'default' => new MySQLDriverConfig(
            connection: new TcpConnectionConfig(
                database: env('DB_DATABASE', 'test'),
                host: env('DB_HOST', 'local'),
                port: env('DB_PORT', 3306),
                user: env('DB_USER', 'test'),
                password: env('DB_PASSWORD', ''),
                charset: env('DB_CHARSET', 'utf8mb4'),
            ),
            reconnect: true,
            queryCache: true,
            options: [],
            poolOptions: [
                'min_connections' => env('DB_MIN_CONNECTIONS', 1),
                'max_connections' => env('DB_MAX_CONNECTIONS', 10),
                'connect_timeout' => env('DB_CONNECT_TIMEOUT', 10.0),
                'wait_timeout' => env('DB_WAIT_TIMEOUT', 3.0),
                'heartbeat' => env('DB_HEARTBEAT', -1),
                'max_idle_time' => env('DB_MAX_IDLE_TIME', 60.0),
            ],
        ),
    ]
];
