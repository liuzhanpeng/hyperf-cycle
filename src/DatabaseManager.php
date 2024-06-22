<?php

declare(strict_types=1);

namespace Lzpeng\HyperfCycle;

use Cycle\Database\DatabaseInterface;
use Cycle\Database\DatabaseProviderInterface;

/**
 * 支持连接池的DatabaseManager
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
final class DatabaseManager implements DatabaseProviderInterface
{
    public function database(?string $database = null): DatabaseInterface
    {
    }
}
