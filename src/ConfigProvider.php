<?php

declare(strict_types=1);

namespace Lzpeng\HyperfCycle;

use Cycle\ORM\Entity\Behavior\EventDrivenCommandGenerator;
use Cycle\ORM\Schema;
use Hyperf\Contract\ConfigInterface;
use Lzpeng\HyperfCycle\Config\DatabaseConfig;
use Psr\Container\ContainerInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                DatabaseManager::class => function (ContainerInterface $container) {
                    $config = $container->get(ConfigInterface::class);

                    return new DatabaseManager(new DatabaseConfig($config->get('databases')));
                },
                ORMFactory::class => function (ContainerInterface $container) {
                    $databaseManager = $container->get(DatabaseManager::class);
                    $config = $container->get(ConfigInterface::class);
                    $schema = new Schema($config->get('orm.schema'));
                    $defaultSchemaClassess = $config->get('orm.default_schema_classes');

                    $commandGenerator = new EventDrivenCommandGenerator($schema, $container);

                    return new ORMFactory(
                        $databaseManager,
                        $schema,
                        $defaultSchemaClassess,
                        $commandGenerator
                    );
                },
            ],
            'commands' => [],
            'publish' => [
                [
                    'id' => 'database',
                    'description' => 'The config for database.',
                    'source' => __DIR__ . '/../publish/databases.php',
                    'destination' => BASE_PATH . '/config/autoload/databases.php',
                ]
            ]
        ];
    }
}
