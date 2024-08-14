<?php
# -*- coding: utf-8 -*-
# config app
# Copyright (c) 2023. Xifan. All Rights Reserved.
# @Date        : 2024/08/14
# @Author      : 重庆饭哥
# @微信公众号   : cq_xifan
# @description : 

return [
  'debug' => true,
  'error_reporting' => E_ALL,
  'default_timezone' => 'Asia/Shanghai',
  'public_path' => BASE_PATH . DIRECTORY_SEPARATOR . 'public',
  'runtime_path' => BASE_PATH . DIRECTORY_SEPARATOR . 'runtime',
  'controller_suffix' => 'Controller',
  'controller_reuse' => false,
];
