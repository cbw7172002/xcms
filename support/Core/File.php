<?php
# -*- coding: utf-8 -*-
# File File
# Copyright (c) 2023. Xifan. All Rights Reserved.
# @Date        : 2024/08/14
# @Author      : 重庆饭哥
# @微信公众号   : cq_xifan
# @description : 文件操作

namespace support\Core;

use RuntimeException;
use ZipArchive;
use function mkdir;
use function copy;
use function opendir;

class File
{
  public static function copy($src = '', $dst = '')
  {
    if (empty($src) || empty($dst)) {
      return false;
    }

    if (is_file($src)) {
      return copy($src, $dst);
    }

    $dir = opendir($src);
    self::mkDir($dst);
    while (false !== ($file = readdir($dir))) {
      if (!startsWith($file, ".")) {
        if (is_dir($src . '/' . $file)) {
          self::copy($src . '/' . $file, $dst . '/' . $file);
        } else {
          copy($src . '/' . $file, $dst . '/' . $file);
        }
      }
    }
    closedir($dir);

    return true;
  }

  public static function mkDir($path, $recursive = true)
  {
    clearstatcache();
    try {
      if (!empty($path) && !file_exists($path)) {
        return mkdir($path, 0777, $recursive);
      }
    } catch (RuntimeException $e) {
    }

    return true;
  }

  public static function zip($dir, $dst)
  {
    $zip = new ZipArchive();
    if ($zip->open($dst, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
      self::addZip($dir, $zip, $dir);
    }
  }

  public static function unzip($src, $dst)
  {
    $zip = new ZipArchive();
    if ($zip->open($src) === true) {
      File::mkDir($dst);
      $zip->extractTo($dst);
      $zip->close();
      return true;
    }
    return false;
  }


  private static function addZip($path, $zip, $replace)
  {
    $handler = opendir($path);
    while (($filename = readdir($handler)) !== false) {
      if (!startsWith($filename, ".")) {
        if (is_dir($path . "/" . $filename)) {
          self::addZip($path . "/" . $filename, $zip, $replace);
        } else {
          $zip->addFile($path . "/" . $filename);
          $zip->renameName($path . "/" . $filename, str_replace($replace, "", $path) . DIRECTORY_SEPARATOR . $filename);
        }
      }
    }
    closedir($handler);
  }
}
