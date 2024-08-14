<?php
#!/usr/bin/env php
# -*- coding: utf-8 -*-
# File start
# Copyright (c) 2023. Xifan. All Rights Reserved.
# @Date        : 2024/08/14
# @Author      : 重庆饭哥
# @微信公众号   : cq_xifan
# @description : 框架加载入口




declare(strict_types=1);

ini_set('display_errors', 'on');
error_reporting(E_ALL);

try {
  require_once __DIR__ . '/../vendor/autoload.php';
  if (version_compare(PHP_VERSION, '7.2.0', '<')) {
    exit("[ Xcms ] 环境异常：请使用PHP 7.2 以上版本运行该应用");
  }
  ignore_user_abort(true);
  $GLOBALS['__frame_start_time__'] = microtime(true);
  $GLOBALS['__memory_start_size__'] = memory_get_usage();
  define('BASE_PATH', __DIR__);
  Xcms\Core\App::run();
} catch (ExitApp $a) {
  // 捕获自定义的 ExitApp 异常
  echo "捕获自定义的 ExitApp 异常: " . $a->getMessage() . "\n" . "<br>";
  // 这里可以添加处理 ExitApp 异常的特定逻辑
} catch (Exception $e) {
  // 捕获其他所有 Exception 类型的异常
  // 获取异常消息
  $message = $e->getMessage();
  // 获取异常发生的文件路径
  $file = $e->getFile();
  // 获取异常发生的行号
  $line = $e->getLine();
  // 打印异常的详细信息
  echo "捕获到Exception异常: " . $message . "\n" . "<br>";
  echo "发生位置：文件 " . $file . " 在行号 " . $line;
  // 这里可以添加处理其他异常的通用逻辑
} catch (Throwable $t) {
  // 捕获所有 Throwable 类型的异常或错误，包括 Exception 之外的错误类型
  // 获取异常消息
  $message = $t->getMessage();
  // 获取异常发生的文件路径
  $file = $t->getFile();
  // 获取异常发生的行号
  $line = $t->getLine();
  // 打印异常的详细信息
  echo "捕获到Throwable异常: " . $message . "\n" . "<br>";
  echo "发生位置：文件 " . $file . " 在行号 " . $line;
  // 这里可以添加处理严重错误的逻辑
  // 注意：此处通常不会到达，除非抛出了非 Exception 类型的错误
} finally {
  // 无论是否发生异常，finally 块都会执行，但 die 和 exit 后不会执行 finally
  // echo "finally 块，但 die 和 exit 后不会执行 finally.\n";
  // 这里可以添加清理资源的代码，如关闭数据库连接、文件句柄等
  // 也可以记录日志等
  print_r("<br>--------" . BASE_PATH . "运行结束-------");
}
