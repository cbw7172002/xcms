<?php
# -*- coding: utf-8 -*-
# File Engine
# Copyright (c) 2023. Xifan. All Rights Reserved.
# @Date        : 2024/08/14
# @Author      : 重庆饭哥
# @微信公众号   : cq_xifan
# @description : 

namespace support\Core;

use support\Engine\ViewEngine;


class Engine
{
  protected static $engine = null;
  /**
   * 获取渲染引擎
   * @return JsonEngine|CliEngine|ViewEngine|null
   */
  public static function getEngine()
  {
    //如果之前没有设置输出引擎，则启用文档引擎
    if (empty(self::$engine)) {
      self::setDefaultEngine(new ViewEngine());
    }
    return self::$engine;
  }

  /**
   * 设置引擎
   * @param $engine BaseEngine
   */
  static function setDefaultEngine($engine)
  {
    self::$engine = $engine;
  }
}
