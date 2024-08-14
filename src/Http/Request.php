<?php
# -*- coding: utf-8 -*-
# File Request
# Copyright (c) 2023. Xifan. All Rights Reserved.
# @Date        : 2024/08/14
# @Author      : 重庆饭哥
# @微信公众号   : cq_xifan
# @description : 客户端请求处理

namespace Xcms\Http;



use function filter_var;
use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_NO_PRIV_RANGE;
use const FILTER_FLAG_NO_RES_RANGE;
use const FILTER_VALIDATE_IP;

class Request
{
  /**
   * 是否PJAX请求
   * @return bool
   */
  public static function isPjax()
  {
    return (isset($_SERVER['HTTP_X_PJAX']) && $_SERVER['HTTP_X_PJAX'] == 'true');
  }


  /**
   * 是否AJAX请求
   * @return bool
   */
  public static function isAjax()
  {
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
  }


  /**
   * 是否GET请求
   * @return bool
   */
  public static function isGet()
  {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
  }

  /**
   * 是否POST请求
   * @return bool
   */
  public static function isPost()
  {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
  }
  /**
   * 请求客户端是否能接收json
   * @return bool
   */
  public static function isBackJson()
  {
    return false !== strpos(self::getHeader('accept'), 'json');
  }
  /**
   * 获取域名,例如：example.com 或 example.com:8088
   * @return string
   */
  public static function getDomain()
  {
    return $_SERVER["HTTP_HOST"];
  }
  /**
   * 获取域名，无端口,例如：example.com
   * @return string
   */
  public static function getDomainNoPort()
  {
    return $_SERVER["SERVER_NAME"];
  }

  /**
   * 获取根域名 例如hot.baidu.com,获取到是baidu.com
   * @return string
   */
  public static function getRootDomain()
  {
    $url = getHttpScheme() . $_SERVER['HTTP_HOST'];
    $hosts = parse_url($url);
    $host = $hosts['host'];
    //查看是几级域名
    $data = explode('.', $host);
    $n = count($data);
    //判断是否是双后缀
    $preg = '/\w.+\.(com|net|org|gov|edu)\.cn$/';
    if (($n > 2) && preg_match($preg, $host)) {
      //双后缀取后3位
      $host = $data[$n - 3] . '.' . $data[$n - 2] . '.' . $data[$n - 1];
    } else {
      //非双后缀取后两位
      $host = $data[$n - 2] . '.' . $data[$n - 1];
    }
    return $host;
  }

  /**
   * 获取当前访问的地址,例如：https://example.com/index/main
   * @return string
   */
  public static function getAddress()
  {
    return getHttpScheme() . $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'];
  }


  /**
   * 网络IP
   * @param string $ip
   * @return bool
   */
  public static function isTrueIp($ip)
  {
    // Not validate ip .
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
      return false;
    }
    // Is intranet ip ? For IPv4, the result of false may not be accurate, so we need to check it manually later .
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
      return true;
    }
    // Manual check only for IPv4 .
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
      return false;
    }
    // Manual check .
    $reservedIps = [
      1681915904 => 1686110207, // 100.64.0.0 -  100.127.255.255
      3221225472 => 3221225727, // 192.0.0.0 - 192.0.0.255
      3221225984 => 3221226239, // 192.0.2.0 - 192.0.2.255
      3227017984 => 3227018239, // 192.88.99.0 - 192.88.99.255
      3323068416 => 3323199487, // 198.18.0.0 - 198.19.255.255
      3325256704 => 3325256959, // 198.51.100.0 - 198.51.100.255
      3405803776 => 3405804031, // 203.0.113.0 - 203.0.113.255
      3758096384 => 4026531839, // 224.0.0.0 - 239.255.255.255
    ];
    $ipLong = ip2long($ip);
    foreach ($reservedIps as $ipStart => $ipEnd) {
      if (($ipLong >= $ipStart) && ($ipLong <= $ipEnd)) {
        return true;
      }
    }
    return false;
  }

  /**
   * 获取header头部某值
   * @param $headName
   * @return mixed|null
   */
  public static function getHeader($headName)
  {
    if (function_exists('getallheaders')) return getallheaders();
    $headers = [];
    foreach ($_SERVER as $key => $value) {
      if (startsWith($key, 'HTTP_')) {
        $headers[ucfirst(strtolower(str_replace('_', '-', substr($key, 5))))] = $value;
      }
      if (isset($_SERVER['PHP_AUTH_DIGEST'])) {
        $headers['AUTHORIZATION'] = $_SERVER['PHP_AUTH_DIGEST'];
      } elseif (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        $headers['AUTHORIZATION'] = base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
      }
      if (isset($_SERVER['CONTENT_LENGTH'])) {
        $headers['CONTENT-LENGTH'] = $_SERVER['CONTENT_LENGTH'];
      }
      if (isset($_SERVER['CONTENT_TYPE'])) {
        $headers['CONTENT-TYPE'] = $_SERVER['CONTENT_TYPE'];
      }
    }
    if (isset($headers[$headName])) {
      return $headers[$headName];
    }
    return null;
  }
}
