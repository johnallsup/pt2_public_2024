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
require_once("write_log.php");
class Wiki extends stdclass {
  # Holds context data that we can pass to e.g. PTMD
  var $storage, $path, $subdir, $url, $action, $config;
  function log($message) {
    return write_log($message);
  }
  function valid_page_path($path) {
    if( preg_match('@^[st](?:$|/)@',$path) ) {
      return false; # s/ t/ reserved for tag/search
    }
    if( preg_match('@(?:^|/)home/@',$path) ) {
      # home is not a valid component of a directory name
      return false;
    }
    if( preg_match('/^([a-zA-Z0-9_+@=-]+\/+)*[a-zA-Z0-9_+%@=-]+$/',$path) ) {
      return true;
    }
    return false;
  }
  function valid_file_path($path) {
    if( preg_match('@^[st](?:$|/)@',$path) ) {
      return false; # s/ t/ reserved for tag/search
    }
    if( preg_match('/^([a-zA-Z0-9_+@=-]+\/+)*[a-zA-Z0-9_+%@=-]+\.[a-zA-Z0-9_+%@=-]+$/',$path) ) {
      return true;
    }
    return false;
  }
  function redirect($newpath) {
    header('Location: '.$newpath, true, 303);
    exit();
  }
}
