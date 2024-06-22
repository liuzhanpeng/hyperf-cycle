<?php

declare(strict_types=1);

namespace Lzpeng\HyperfCycle\Pool;

use Hyperf\Pool\Connection as AbstractConnection;

/**
 * 表示一个数据库连接池连接, 封装了DatabaseInterface实例
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class Connection extends AbstractConnection
{
    public function reconnect(): bool
    {
    }

    public function close(): bool
    {
    }

    public function getActiveConnection()
    {
    }
}
