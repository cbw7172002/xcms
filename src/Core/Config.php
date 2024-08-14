<?php
# -*- coding: utf-8 -*-
# File Config
# Copyright (c) 2023. Xifan. All Rights Reserved.
# @Date        : 2024/08/14
# @Author      : 重庆饭哥
# @微信公众号   : cq_xifan
# @description : 框架配置读取

namespace Xcms\Core;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Config
{

  protected static $config = [];
  protected static $configPath = '';
  protected static $loaded = false;


  public static function load($configPath, $excludeFile = [], $key = null)
  {
    self::$configPath = $configPath;
    if (!$configPath) {
      return;
    }
    self::$loaded = false;
    $config = self::loadFromDir($configPath, $excludeFile);
    if (!$config) {
      self::$loaded = true;
      return;
    }
    if ($key !== null) {
      foreach (array_reverse(explode('.', $key)) as $k) {
        $config = [$k => $config];
      }
    }

    self::$config = array_replace_recursive(self::$config, $config);
    self::$loaded = true;
  }

  /**
   * 加载某个目录的配置文件
   * @param string $configPath
   * @param array $excludeFile
   * @return array
   */
  public static function loadFromDir($configPath, $excludeFile = [])
  {
    $allConfig = [];
    $dirIterator = new RecursiveDirectoryIterator($configPath, FilesystemIterator::FOLLOW_SYMLINKS);
    $iterator = new RecursiveIteratorIterator($dirIterator);
    foreach ($iterator as $file) {
      if (is_dir($file) || $file->getExtension() != 'php' || in_array($file->getBaseName('.php'), $excludeFile)) {
        continue;
      }
      $appConfigFile = $file->getPath() . '/app.php';
      if (!is_file($appConfigFile)) {
        continue;
      }
      $relativePath = str_replace($configPath . DIRECTORY_SEPARATOR, '', substr($file, 0, -4));
      $explode = array_reverse(explode(DIRECTORY_SEPARATOR, $relativePath));
      if (count($explode) >= 2) {
        $appConfig = include $appConfigFile;
        if (empty($appConfig['enable'])) {
          continue;
        }
      }
      $config = include $file;
      foreach ($explode as $section) {
        $tmp = [];
        $tmp[$section] = $config;
        $config = $tmp;
      }
      $allConfig = array_replace_recursive($allConfig, $config);
    }
    return $allConfig;
  }

  /**
   * 重载配置
   * @param string $configPath
   * @param array $excludeFile
   * @return void
   * @deprecated
   */
  public static function reload($configPath, $excludeFile = [])
  {
    self::load($configPath, $excludeFile);
  }

  /**
   * 清除配置
   * @return void
   */
  public static function clear()
  {
    self::$config = [];
  }



  /**
   * Get.
   * @param string|null $key
   * @param mixed $default
   * @return array|mixed|void|null
   */
  public static function get($key = null, $default = null)
  {
    if ($key === null) {
      return self::$config;
    }
    $keyArray = explode('.', $key);
    $value = self::$config;
    $found = true;
    foreach ($keyArray as $index) {
      if (!isset($value[$index])) {
        if (self::$loaded) {
          return $default;
        }
        $found = false;
        break;
      }
      $value = $value[$index];
    }
    if ($found) {
      return $value;
    }
    return self::read($key, $default);
  }

  /**
   * Read.
   * @param string $key
   * @param mixed $default
   * @return array|mixed|null
   */
  protected static function read($key, $default = null)
  {
    $path = self::$configPath;
    if ($path === '') {
      return $default;
    }
    $keys = $keyArray = explode('.', $key);
    foreach ($keyArray as $index => $section) {
      unset($keys[$index]);
      if (is_file($file = "$path/$section.php")) {
        $config = include $file;
        return self::find($keys, $config, $default);
      }
      if (!is_dir($path = "$path/$section")) {
        return $default;
      }
    }
    return $default;
  }

  /**
   * Find.
   * @param array $keyArray
   * @param mixed $stack
   * @param mixed $default
   * @return array|mixed
   */
  protected static function find(array $keyArray, $stack, $default)
  {
    if (!is_array($stack)) {
      return $default;
    }
    $value = $stack;
    foreach ($keyArray as $index) {
      if (!isset($value[$index])) {
        return $default;
      }
      $value = $value[$index];
    }
    return $value;
  }
}
