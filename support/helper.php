<?php
# -*- coding: utf-8 -*-
# File helper
# Copyright (c) 2023. Xifan. All Rights Reserved.
# @Date        : 2024/08/14
# @Author      : 重庆饭哥
# @微信公众号   : cq_xifan
# @description : 助手函数

use support\Core\Config;

/**
 * 路径拼接
 * @param string $front
 * @param string $end
 * @return string
 */
function path_comb($front, $end)
{
  return $front . ($end ? (DIRECTORY_SEPARATOR . ltrim($end, DIRECTORY_SEPARATOR)) : $end);
}
/**
 * 网络协议获取
 * @return string
 */
function getHttpScheme()
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
 * 8.1以下函数兼容判断字符串
 * @param string $haystack
 * @param string $needle
 * @return string
 */
function startsWith($haystack, $needle)
{
  return strncmp($haystack, $needle, strlen($needle)) === 0;
}

/**
 * Runtime path
 * @param string $path
 * @return string
 */
function runtime_path($path = '')
{
  static $runtimePath = '';
  if (!$runtimePath) {
    $runtimePath = Config::get('app.runtime_path', BASE_PATH . DIRECTORY_SEPARATOR . 'runtime');
  }
  return path_comb($runtimePath, $path);
}
/**
 * public path
 * @param string $path
 * @return string
 */
function public_path($path = '')
{
  static $publicPath = '';
  if (!$publicPath) {
    $publicPath = Config::get('app.public_path', BASE_PATH . DIRECTORY_SEPARATOR . 'public');
  }
  return path_comb($publicPath, $path);
}
/**
 * 获取静态文件的content-type
 * @param string $filename
 * @return string
 */
function file_type($filename)
{
  $mime_types = array(
    'txt' => 'text/plain',
    'htm' => 'text/html',
    'html' => 'text/html',
    'php' => 'text/html',
    'css' => 'text/css',
    'js' => 'application/javascript',
    'json' => 'application/json',
    'xml' => 'application/xml',
    'swf' => 'application/x-shockwave-flash',
    'flv' => 'video/x-flv',
    // images
    'png' => 'image/png',
    'jpe' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'jpg' => 'image/jpeg',
    'gif' => 'image/gif',
    'bmp' => 'image/bmp',
    'ico' => 'image/vnd.microsoft.icon',
    'tiff' => 'image/tiff',
    'tif' => 'image/tiff',
    'svg' => 'image/svg+xml',
    'svgz' => 'image/svg+xml',
    // archives
    'zip' => 'application/zip',
    'rar' => 'application/x-rar-compressed',
    'exe' => 'application/x-msdownload',
    'msi' => 'application/x-msdownload',
    'cab' => 'application/vnd.ms-cab-compressed',
    // audio/video
    'mp3' => 'audio/mpeg',
    'qt' => 'video/quicktime',
    'mov' => 'video/quicktime',
    // adobe
    'pdf' => 'application/pdf',
    'psd' => 'image/vnd.adobe.photoshop',
    'ai' => 'application/postscript',
    'eps' => 'application/postscript',
    'ps' => 'application/postscript',
    // ms office
    'doc' => 'application/msword',
    'rtf' => 'application/rtf',
    'xls' => 'application/vnd.ms-excel',
    'ppt' => 'application/vnd.ms-powerpoint',
    // open office
    'odt' => 'application/vnd.oasis.opendocument.text',
    'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    "woff2" => 'font/woff2',
    "ttf" => 'font/ttf',
  );
  $extension = pathinfo($filename, PATHINFO_EXTENSION);
  $ext = strtolower($extension);
  if (array_key_exists($ext, $mime_types)) {
    return $mime_types[$ext];
  }
  return 'application/octet-stream';
}
