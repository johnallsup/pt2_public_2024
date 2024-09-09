<?php
/*
* Purple Tree 2, my personal wiki
* Copyright (C) 2023-2024 John Allsup
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
require_once("docroot.php");
require_once("common.php");

$url = $_SERVER['REQUEST_URI'];
$url = preg_replace("@^/+@","",$url);
$storage = new VersionedStorage($docroot."/../files",$docroot."/../version");
if( !is_auth("view") ) {
  include("AccessDenied.php");
  exit();
}
if( $storage->has($url) ) {
  $mime = $storage->get_mime_type($url);
  $fpath = $storage->fpath($url);
  http_response_code(200);
  if( preg_match('/\.ptmd$/',$fpath) ) {
    header("Content-type: text/plain");
  } else {
    header("Content-type: $mime");
  }
  readfile($fpath);
  exit();
} else {
  $xs = explode("/",$url);
  $filename = array_pop($xs);
  $subdir = implode("/",$xs);
  if( $subdir == "" ) $subdir = "/";
  $paths = $storage->find_leaf_to_root($subdir,$filename);
  if( count($paths) > 0 ) {
    $path = $paths[0];
    $fpath = $storage->fpath($path);
    $mime = $storage->get_mime_type($path);
    http_response_code(200);
    header("Content-type: $mime");
    readfile($fpath);
    exit();
  }
}
http_response_code(404);
exit();
?>
