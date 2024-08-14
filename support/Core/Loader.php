<?php
# -*- coding: utf-8 -*-
# File Loader
# Copyright (c) 2023. Xifan. All Rights Reserved.
# @Date        : 2024/08/14
# @Author      : 重庆饭哥
# @微信公众号   : cq_xifan
# @description : 自动加载

namespace support\Core;

class Loader
{
    /**
     * 已加载的文件数组
     *
     * @var array
     */
    private static $loadedFiles = [];

    /**
     * 注册自动加载
     */
    public static function register()
    {
        spl_autoload_register(__NAMESPACE__ . "\Loader::autoload", true, true);
    }

    /**
     * 框架本身的自动加载
     *
     * @param string $raw
     */
    public static function autoload($raw)
    {
        $realClass = str_replace("\\", DS, $raw) . ".php";
        // 拼接类名文件
        $file = BASE_PATH . DS . $realClass;
        // 存在就加载
        self::includeFile($file);
    }

    /**
     * 引入文件并记录日志
     *
     * @param string $file
     */
    private static function includeFile($file)
    {
        if (isset(self::$loadedFiles[$file])) {
            return; // 文件已加载
        }

        if (file_exists($file)) {
            include $file;
            self::$loadedFiles[$file] = true; // 将文件标记为已加载
        }
    }
}
