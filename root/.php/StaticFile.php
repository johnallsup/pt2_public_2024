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

if( !is_auth("view") ) {
  include("AccessDenied.php");
  exit();
}

$url = $_SERVER['REQUEST_URI'];
$url = preg_replace("@^/+@","",$url);
$staticroot = $docroot."/../static";
$fpath = $staticroot."/".$url;
if( is_file($fpath) ) {
  $mime = get_mime_type_for_fpath($fpath);
  http_response_code(200);
  header("Content-type: $mime");
  readfile($fpath);
} else {
  http_response_code(404);
  echo "File not found.";
  exit();
}
?>
