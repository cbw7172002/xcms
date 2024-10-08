<?php
# -*- coding: utf-8 -*-
# File Request
# Copyright (c) 2023. Xifan. All Rights Reserved.
# @Date        : 2024/08/14
# @Author      : 重庆饭哥
# @微信公众号   : cq_xifan
# @description : 服务响应类

namespace support\Http;

use Throwable;
use function filemtime;
use function gmdate;

class Response
{
  protected $exception = null; // 错误
  protected $data; // 原始数据
  protected $code = 200;  //状态
  protected $header = []; // header参数

  /**
   * 直接跳转
   * @param string $url 跳转路径
   * @param int $timeout 延时跳转
   */
  public static function location($url, $timeout = 0)
  {
    if (!str_starts_with($url, "http")) {
      $url = Response::getHttpScheme() . Request::getDomain() . $url;
    }
    if ($timeout !== 0) {
      header("refresh:$timeout," . $url);
    } else {
      http_response_code(302);
      header("Location:{$url}");
    }
    App::exit(sprintf("发生强制跳转：%s", $url));
  }

  /**
   * 获取浏览器的http协议
   * @return string
   */
  static function getHttpScheme()
  {
    if (
      isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == "https"
      || isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on"
      || isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443
      || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == "https"
    ) {
      return 'https://';
    } else {
      return 'http://';
    }
  }


  /**
   * 原始数据
   * @access   public
   * @param string $data 输出数据
   * @return Response
   */
  public function render($data = '')
  {

    $this->data = $data; //需要渲染的数据
    return $this;
  }

  /**
   * 发送的数据类型
   * @param string $content_type 响应类型
   * @param string $charset 编码
   * @return $this
   */
  public function contentType($content_type = 'text/html', $charset = 'utf-8')
  {
    $this->header['Content-Type'] = $content_type . '; charset=' . $charset;
    return $this;
  }

  /**
   * 发送HTTP状态
   * @param integer $code 状态码
   */
  public function code(int $code)
  {
    $this->code = $code;
    return $this;
  }

  /**
   * 设置缓存分钟数
   * @param $min
   * @return $this
   */
  public function cache($min)
  {
    $seconds_to_cache = $min * 60;
    $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
    $this->header["Expires"] = $ts;
    $this->header["Pragma"] = "cache";
    $this->header["Cache-Control"] = "max-age=$seconds_to_cache";
    return $this;
  }

  /**
   * 设置header头
   * @param $key
   * @param $value
   * @return $this
   */
  public function header($key, $value): Response
  {
    $this->header[$key] = $value;
    return $this;
  }

  public function setHeaders($header = []): static
  {
    $this->header = $header;
    return $this;
  }

  public function send(): void
  {
    $addr = Request::getAddress();
    $addr = strstr($addr, '?', true) ?: $addr;
    if (preg_match("/.*\.(gif|jpg|jpeg|png|bmp|swf|woff|woff2)?$/", $addr)) {
      $this->cache(60 * 24 * 365);
    } elseif (preg_match("/.*\.(js|css)?$/", $addr)) {
      $this->cache(60 * 24 * 180);
    }


    if (App::$debug) {
      $this->header[] = "Server-Timing: " .
        "Total;dur=" . round((microtime(true) - Variables::get("__frame_start__", 0)) * 1000, 4) .
        ",Route;dur=" . App::$route .
        ",Frame;dur=" . App::$frame .
        ",App;dur=" . App::$route .
        ",Db;dur=" . App::$db;
    }

    $this->header["Server"] = "Apache";
    // 监听response_send
    EventManager::trigger('__response_before_send__', $this);

    // 处理输出数据
    if (!headers_sent() && !empty($this->header)) {
      // 发送状态码
      http_response_code($this->code);
      // 发送头部信息
      foreach ($this->header as $name => $val) {
        if (!is_string($name)) {
          header($val);
        } else {
          header($name . ':' . $val);
        }
      }
    }
    if (is_file_exists($this->data)) {
      readfile($this->data);
    } else {
      echo $this->data;
    }

    self::finish();

    // 监听response_end
    EventManager::trigger('__response_after_send__', $this);

    App::exit("后端数据发送结束");
  }

  /**
   * 结束Http响应
   * @return void
   */
  static private function finish(): void
  {
    if (function_exists('fastcgi_finish_request')) {
      // 提高页面响应
      fastcgi_finish_request();
    }
    flush();
  }
}
