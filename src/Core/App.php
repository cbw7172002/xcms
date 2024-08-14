<?php
# -*- coding: utf-8 -*-
# File App
# Copyright (c) 2023. Xifan. All Rights Reserved.
# @Date        : 2024/08/14
# @Author      : 重庆饭哥
# @微信公众号   : cq_xifan
# @description : 单一加载入口

namespace Xcms\Core;

use Xcms\Core\Config;
use Xcms\Core\Error;
use Xcms\Core\Log;
use Xcms\Core\Engine;
use Xcms\Http\Request;
use Xcms\Engine\CliEngine;


use const DIRECTORY_SEPARATOR;

class App
{
  public static $debug = false;
  public static $cli = false;
  public static $time = [];


  /**
   * Run.
   * @return void
   */
  public static function run()
  {
    //如果开启调试,清除所有缓存
    if (App::$debug && function_exists("opcache_reset")) {
      opcache_reset(); //OPCACHE_RESET_NONE (默认值)：仅清除用户空间的缓存。OPCACHE_RESET_ALL：清除所有缓存，包括用户空间和系统范围的缓存
    }

    define("DS", DIRECTORY_SEPARATOR); //定义斜杠符号
    define("APP_CORE", BASE_PATH . DS . 'Core' . DS); //定义程序的核心目录

    self::$cli = PHP_SAPI === 'cli';
    if (self::$cli) {
      $_SERVER["SERVER_NAME"] = "127.0.0.1";
      $_SERVER["REQUEST_METHOD"] = "GET";
    }

    self::loadAllConfig(['route', 'container']);

    include BASE_PATH . DIRECTORY_SEPARATOR . "helper.php"; //载入内置助手函数
    include APP_CORE . "Loader.php"; // 加载自动加载器
    Loader::register(); // 注册自动加载,也可以用composer的自动加载
    self::loadAllConfig();



    try {
      if (self::$cli) {
        Engine::setDefaultEngine(new CliEngine());
      }
      if (self::$debug) {
        if (self::$cli)
          Log::record("Request", "命令行启动框架", Log::TYPE_WARNING);
        else {
          Log::record("Request", "收到请求：" . $_SERVER["REQUEST_METHOD"] . " " . $_SERVER["REQUEST_URI"]);
        }
      }
    } catch (ExitApp $exit_app) { //执行退出

    } catch (Throwable $exception) {
    } finally {
    }
  }

  /**
   * 注册配置信息
   * @param array $excludes
   * @return void
   */
  public static function loadAllConfig($excludes = [])
  {
    Config::load(BASE_PATH . DIRECTORY_SEPARATOR . "Config", $excludes);
  }
}
