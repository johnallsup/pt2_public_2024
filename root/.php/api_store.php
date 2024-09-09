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
require_once("utils.php");
require_once("mtimes.php");
if( !is_auth("edit") ) {
  serve_error_json("accessdenied","Access denied trying to store",401);
}
if( !isset($postdata["path"]) ) {
  serve_error_json("invalidstore","No path provided for store",400,[ "postdata" => $postdata]);
}
if( !isset($postdata["source"]) ) {
  serve_error_json("invalidstore","No source provided for store",400,[ "postdata" => $postdata]);
}
$path = $postdata["path"].".".PAGE_EXT;
$source = $postdata["source"];
$storage = $wiki->storage;
try {
  $source = trim($source);
  $source = str_replace("\r","",$source);
  if( $source === "" ) {
    $result = $storage->del($path);
    $when = time();
    [ $mtime_fmt_long, $mtime_fmt_short, $mtime_fmt_short_ago ] = fmt_time($when);
    if( $result ) {
      serve_json([
        "status" => "success", 
        "message" => "Deleted $path successfully",
        "mtime" => $when,
        "mtime_fmt_short" => $mtime_fmt_short,
        "mtime_fmt_long" => $mtime_fmt_long,
        "mtime_fmt_short_ago" => $mtime_fmt_short_ago
      ],
        200);
    } else {
      serve_json([
        "status" => "error", 
        "message" => "Failed to delete $path",
        "mtime" => $when,
        "mtime_fmt_short" => $mtime_fmt_short,
        "mtime_fmt_long" => $mtime_fmt_long,
        "mtime_fmt_short_ago" => $mtime_fmt_short_ago
      ],
        200);
    }
  } else {
    $when = $storage->store($path,$source);
    [ $mtime_fmt_long, $mtime_fmt_short, $mtime_fmt_short_ago ] = fmt_time($when);
    # write very recent
    $recent_writes_entry = "$when:$path";
    append_to_data("recent_writes.log",$recent_writes_entry."\n");
    serve_json([
      "status" => "success", 
      "message" => "Stored $path successfully",
      "mtime" => $when,
      "mtime_fmt_short" => $mtime_fmt_short,
      "mtime_fmt_long" => $mtime_fmt_long,
      "mtime_fmt_short_ago" => $mtime_fmt_short_ago
    ],
      200);
  }
} catch(Exception $e) {
  serve_error_json("storeerror","Failed to store",500,["exception" => $e->getMessage()]);
}
