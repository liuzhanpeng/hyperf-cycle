<?php

declare(strict_types=1);

namespace Lzpeng\HyperfCycle\Pool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Pool as AbstractPool;

/**
 * 数据库连接池
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class Pool extends AbstractPool
{
    protected function createConnection(): ConnectionInterface
    {
    }
}
