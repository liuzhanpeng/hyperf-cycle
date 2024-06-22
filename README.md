# hyperf-cycle
Cycle ORM support for hyperf

使用Hyperf/Pool增加连接池功能

用法与CycleORM基本相同:

```php
use Lzpeng\HyperfCycle\DatabaseManager;
use Lzpeng\HyperfCycle\Config\DatabaseConfig;
use Lzpeng\HyperfCycle\Config\MySQLDriverConfig;
use Cycle\Database\Config\MySQL\TcpConnectionConfig;

$dbal = new DatabaseManager(
    new DatabaseConfig([
        'default' => 'default',
        'databases' => [
            'default' => ['connection' => 'mysql']
        ],
        'connections' => [
            'mysql' => new MySQLDriverConfig(
                connection: new TcpConnectionConfig(
                    database: 'spiral',
                    host: '127.0.0.1',
                    port: 3306,
                    user:'spiral',
                    password: '',
                ),
                queryCache: true,
                // 增加了连接池配置
                poolOptions: [ 
                    'min_connections' => 1,
                    'max_connections' => 20,
                    'connect_timeout' => 10.0,
                    'wait_timeout' => 3.0,
                    'heartbeat' => -1,
                    'max_idle_time' => 60.0,
                ]
            ),
        ]
    ])
);

$container = /** 获取容器 **/
$schemaConfig = /** schema配置 **/;

$schema = new Schema($schemaConfig);
$commandGenerator = new EventDrivenCommandGenerator($schema, $container);

$ormFactory = new ORMFactory($databaseManager, $schema, $commandGenerator);

//  获取ORM实例
$orm = $ormFactory->orm();

// 获取EntityManager实例
$em = $ormFactory->entityManager();
```