<?php
# -*- coding: utf-8 -*-
# File ViewEngine
# Copyright (c) 2023. Xifan. All Rights Reserved.
# @Date        : 2024/08/14
# @Author      : 重庆饭哥
# @微信公众号   : cq_xifan
# @description : 模板视图引擎引擎

namespace Xcms\Engine;

class ViewEngine
{
  private $__layout = "";
  private bool $__encode = true;
  private array $__data = [];
  private $__left_delimiter = "{";
  private $__right_delimiter = "}";
  private $__compile_dir;
  private $__template_dir;
}
