# hyperf-cycle

(Cycle ORM)[https://cycle-orm.dev/] 在 Hyperf 中的连接池支持。

该库基于 `hyperf/pool` 为 Cycle ORM 提供协程安全的数据库连接复用能力，同时尽量保持与原生 Cycle ORM 一致的配置和使用方式。

## 特性

- 基于 `hyperf/pool` 的数据库连接池
- 按协程隔离 `Database` 与 `ORM` 实例
- 自动回收连接，减少长驻进程中的连接泄露风险
- 保持与 Cycle ORM 接近的配置结构
- 支持 MySQL、PostgreSQL、SQLite、SQL Server

## 安装

```bash
composer require lzpeng/hyperf-cycle
```

## 发布配置

安装后可发布默认配置：

```bash
php bin/hyperf.php vendor:publish lzpeng/hyperf-cycle
```

发布后会生成数据库配置文件，你可以在其中维护连接信息和连接池参数。

## 配置示例

```php
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
                host: env('DB_HOST', 'localhost'),
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
    ],
];
```

## 连接池参数

| 参数 | 说明 |
| --- | --- |
| `min_connections` | 最小连接数 |
| `max_connections` | 最大连接数 |
| `connect_timeout` | 建立连接超时时间 |
| `wait_timeout` | 从连接池获取连接的等待时间 |
| `heartbeat` | 心跳检测间隔 |
| `max_idle_time` | 连接最大空闲时间 |

## 使用方式

### 推荐：从容器获取

该库通过 `ConfigProvider` 注册了 `DatabaseManager` 与 `ORMFactory`，通常直接从容器取用即可：

```php
use Lzpeng\HyperfCycle\ORMFactory;

$ormFactory = $container->get(ORMFactory::class);

$orm = $ormFactory->orm();
$em = $ormFactory->entityManager();
```

### 手动创建

```php
use Cycle\Database\Config\MySQL\TcpConnectionConfig;
use Cycle\ORM\Entity\Behavior\EventDrivenCommandGenerator;
use Cycle\ORM\Schema;
use Lzpeng\HyperfCycle\Config\DatabaseConfig;
use Lzpeng\HyperfCycle\Config\MySQLDriverConfig;
use Lzpeng\HyperfCycle\DatabaseManager;
use Lzpeng\HyperfCycle\ORMFactory;

$databaseManager = new DatabaseManager(
    new DatabaseConfig([
        'default' => 'default',
        'databases' => [
            'default' => ['connection' => 'mysql'],
        ],
        'connections' => [
            'mysql' => new MySQLDriverConfig(
                connection: new TcpConnectionConfig(
                    database: 'spiral',
                    host: '127.0.0.1',
                    port: 3306,
                    user: 'spiral',
                    password: '',
                ),
                queryCache: true,
                poolOptions: [
                    'min_connections' => 1,
                    'max_connections' => 20,
                    'connect_timeout' => 10.0,
                    'wait_timeout' => 3.0,
                    'heartbeat' => -1,
                    'max_idle_time' => 60.0,
                ],
            ),
        ],
    ])
);

$container = /** 获取容器 */;
$schemaConfig = /** Cycle Schema 配置 */;

$schema = new Schema($schemaConfig);
$commandGenerator = new EventDrivenCommandGenerator($schema, $container);

$ormFactory = new ORMFactory(
    databaseManager: $databaseManager,
    schema: $schema,
    commandGenerator: $commandGenerator,
);

$orm = $ormFactory->orm();
$em = $ormFactory->entityManager();
```

## 说明

- 本库主要解决 Hyperf 场景下的 Cycle ORM 连接池与协程隔离问题。
- 实体映射、Schema、Repository 等能力仍遵循标准 Cycle ORM 用法。
- 数据库连接会在协程生命周期结束时自动释放。

## 注意事项

由于hyperf是一个长驻进程框架，连接对象不要作为对象类的属性进行持久化，否则会导致连接泄露问题或事务问题。建议在需要使用数据库连接的地方通过容器获取 `DatabaseManager` 或 `ORMFactory` 来获取连接实例，确保连接能够正确回收。

错误使用示例：

```php

/**
 * 产品数量管理器.
 */
class QuantityManager implements QuantityManagerInterface
{
    private DatabaseInterface $database;

    public function __construct(private DatabaseManager $databaseManager)
    {
        // 错误：作为属性持久化数据库连接会导致连接泄露问题或事务问题
        $this->database = $this->databaseManager->database();
    }

    public function decrease(Id $productId, Id $skuId, Quantity $quantity): void
    {
        $affected = $this->database->execute(
            'UPDATE `shop_product_sku` SET `quantity` = `quantity` - ? WHERE `id` = ? AND `product_id` = ? AND `quantity` > 0',
            [
                $quantity->toInt(),
                $skuId,
                $productId,
            ]
        );

        if ($affected === 0) {
            throw DomainException::from('库存不足，无法减少库存量', [
                'id' => $productId,
            ]);
        }
    }

    // ...
}

/**
 * 下单.
 */
class PlaceOrderCommand
{
    public function __construct(
        private SkuRepositoryInterface $skuRepository,
        private UserRepositoryInterface $userRepository,
        private OrderRepositoryInterface $orderRepository,
        private OrderFactory $orderFactory,
        private QuantityManagerInterface $quantityManager,
        private TransactionInterface $transaction,
    ) {
    }

    public function execute(
        Id $userId,
        int $productId,
        int $skuId,
        int $quantity,
        array $fulfillmentData,
    ): array {
        // ...

        $order = $this->orderFactory->create($user, $product, $sku, $quantity);

        $this->transaction->execute(function () use ($order, $user) {
            $this->orderRepository->save($order);

            // 由于QuantityManager将数据库连接作为属性, 使用的不是与orderRepository同一个连接, 导致事务失效
            $this->quantityManager->decrease($order->productId(), $order->skuId(), $order->quantity());
        });

        // ...
    }
}
```

正确例子：

```php

/**
 * 产品数量管理器.
 */
class QuantityManager implements QuantityManagerInterface
{
    public function __construct(private DatabaseManager $databaseManager)
    {
    }

    public function decrease(Id $productId, Id $skuId, Quantity $quantity): void
    {
        // 使用时才获取数据库连接，确保连接能够正确回收;
        // 同一协程内的连接实例是同一个，事务能够正常工作
        $affected = $this->databaseManager->database()->execute(
            'UPDATE `shop_product_sku` SET `quantity` = `quantity` - ? WHERE `id` = ? AND `product_id` = ? AND `quantity` > 0',
            [
                $quantity->toInt(),
                $skuId,
                $productId,
            ]
        );

        if ($affected === 0) {
            throw DomainException::from('库存不足，无法减少库存量', [
                'id' => $productId,
            ]);
        }
    }

    // ...
}
```