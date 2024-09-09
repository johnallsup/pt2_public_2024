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
class PageSource extends stdclass {
  function __construct($src) {
    # strip off options: and tags: lines (even for .navbar)
    $lines = explode("\n",$src); # strip \r from files when saving
    $options = [];
    $tags = [];
    $meta = [];
    while(count($lines) > 0 && preg_match("/^[A-Za-z0-9-]+:/",$lines[0]) ) {
      $line = array_shift($lines);
      [ $k,$v ] = explode(":",$line, 2);
      $k = strtolower($k);
      $v = trim($v);
      if( isset($meta[$k]) ) {
        $meta[$k] .= " ".$v;
      } else {
        $meta[$k] = $v;
      }
    }
    if( isset($meta['options']) ) {
      $xs = preg_split('/\s+/',trim($meta['options']));
      array_unshift($xs);
      $options = [];
      foreach($xs as $y) {
        $ys = explode("=",$y,2);
        if( count($ys) == 2) {
          $options[$ys[0]] = $ys[1];
        } else {
          $options[$y] = true;
        }
      }
    }
    if( isset($meta['tags']) ) {
      $tags = preg_split('/\s+/',trim($meta['tags']));
    }
    if( isset($meta['title']) ) {
      $this->title = $meta['title'];
    } else {
      $this->title = null;
    }
    $src = ltrim(implode("\n",$lines));
    $this->meta = $meta;
    $this->options = $options;
    $this->tags = $tags;
    $this->src = $src;
  }
}
