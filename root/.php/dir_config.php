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

function get_config($wiki) {
  $storage = $wiki->storage;
  $subdir = $wiki->subdir;
  $configs = $storage->find_root_to_leaf($subdir,".config.".PAGE_EXT);
  $config_src = "";
  foreach($configs as $config) {
    $config_src .= $storage->get($config)."\n";
  }
  $lines = explode("\n",$config_src);
  $config = array();
  foreach($lines as $line) {
    if( preg_match("/^([a-zA-Z0-9_-]+)=(.*)$/",$line,$m) ) {
      $k = $m[1];
      $v = $m[2];
      $v = trim(explode(" #",$v)[0]); // remove comments
      $config[$k] = $v;
    }
  }
  return $config;
}

