<?php

declare(strict_types=1);

namespace Lzpeng\HyperfCycle;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [],
            'commands' => [],
            'publish' => [
                [
                    'id' => 'dabase',
                    'description' => 'The config for database.',
                    'source' => __DIR__ . '/../publish/databases.php',
                    'destination' => BASE_PATH . '/config/autoload/databases.php',
                ]
            ]
        ];
    }
}
