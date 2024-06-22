<?php

declare(strict_types=1);

namespace Lzpeng\HyperfCycle\Pool;

/**
 * 数据库连接池工厂
 * 
 * 每个数据库独立使用一个连接池，通过此工厂创建连接池
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
class PoolFactory
{
    public function create(string $name): Pool
    {
    }
}
