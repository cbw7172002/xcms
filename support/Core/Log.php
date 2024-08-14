<?php
# -*- coding: utf-8 -*-
# File Log
# Copyright (c) 2023. Xifan. All Rights Reserved.
# @Date        : 2024/08/14
# @Author      : 重庆饭哥
# @微信公众号   : cq_xifan
# @description : 日记记录

namespace support\Core;


use RuntimeException;
use support\Core\Config;
use support\Core\File;



class Log
{
  const TYPE_ERROR = 1;
  const TYPE_WARNING = 2;
  const TYPE_INFO = 3;

  public function __construct($temp)
  {
    $runtimeLogsPath = runtime_path() . DIRECTORY_SEPARATOR . 'logs';
    if (!file_exists($runtimeLogsPath) || !is_dir($runtimeLogsPath)) {
      if (!mkdir($runtimeLogsPath, 0777, true)) {
        throw new RuntimeException("Failed to create runtime logs directory. Please check the permission.");
      }
    }

    $runtimeViewsPath = runtime_path() . DIRECTORY_SEPARATOR . 'views';
    if (!file_exists($runtimeViewsPath) || !is_dir($runtimeViewsPath)) {
      if (!mkdir($runtimeViewsPath, 0777, true)) {
        throw new RuntimeException("Failed to create runtime views directory. Please check the permission.");
      }
    }
  }

  public static function record($tag, $msg, $type = self::TYPE_INFO)
  {
    self::getInstance($tag)->addTemp(self::getInstance($tag)->setType($type)->write($msg));
  }

  public static function recordAsLine($tag, $msg, $type = self::TYPE_INFO, string $pre = "")
  {
    foreach (explode("\n", $msg) as $item) {
      self::getInstance($tag)->addTemp(self::getInstance($tag)->setType($type)->write($pre . trim($item)));
    }
  }

  private function addTemp($msg): void
  {
    $handler = fopen($this->temp, 'a');
    fwrite($handler, $msg);
    fclose($handler);
  }

  public static function getInstance($tag, string $filename = "cleanphp"): Log
  {
    if (self::$instance == null) {
      self::$instance = new Log(uniqid());
    }
    self::$instance->tag = $tag;
    self::$instance->file = Variables::getLogPath(date('Y-m-d'), Variables::get("__frame_log_tag__", "") . $filename . '.log');
    File::mkDir(dirname(self::$instance->file));
    self::$validate = Config::getConfig("frame")["log"] ?? 30;
    return self::$instance;
  }

  protected function write($msg): string
  {
    $m_timestamp = sprintf("%.3f", microtime(true));
    $timestamp = floor($m_timestamp);
    $milliseconds = str_pad(strval(round(($m_timestamp - $timestamp) * 1000)), 3, "0");
    $type = $this->type === Log::TYPE_INFO ? "INFO" : ($this->type === Log::TYPE_ERROR ? "ERROR" : "WARNING");
    return '[ ' . date('Y-m-d H:i:s', $timestamp) . '.' . $milliseconds . ' ] [ ' . $type . ' ] [ ' . $this->tag . ' ] ' . $msg . "\n";
  }

  private function setType(int $type): Log
  {
    $this->type = $type;
    return $this;
  }
  public function getTempLog(): array
  {
    $lines = [];
    $lineCount = 0;
    $fileHandle = fopen($this->temp, 'r');

    if ($fileHandle) {
      while (($line = fgets($fileHandle)) !== false && $lineCount < 500) {
        $lines[] = $line;
        $lineCount++;
      }

      fclose($fileHandle);
    }

    return $lines;
  }


  public function __destruct()
  {
    $id = Variables::get("__async_task_id__", "");
    $start = "-----------[session $id start]-----------\n";
    $end = "-----------[session $id end]-----------\n\n";
    $handler = fopen(self::$instance->file, 'a');
    if (flock($handler, LOCK_EX)) {
      fwrite($handler, $start);
      $lines = file($this->temp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      fwrite($handler, implode("\n", $lines));
      unlink($this->temp);
      fwrite($handler, $end);
      flock($handler, LOCK_UN);
    }
    fclose($handler);
    $this->rm(date('Y-m-d', strtotime("- " . self::$validate . " day")));
  }

  private function rm($date = null)
  {
    File::del(Variables::getLogPath($date));
  }
}
