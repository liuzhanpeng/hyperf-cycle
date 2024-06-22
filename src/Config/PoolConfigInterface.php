<?php

declare(strict_types=1);

namespace Lzpeng\HyperfCycle\Config;

/**
 * 连接池配置接口
 * 
 * @author lzpeng <liuzhanpeng@gmail.com>
 */
interface PoolConfigInterface
{
    /**
     * 返回连接池配置参数
     *
     * @return array
     */
    public function poolOptions(): array;
}
