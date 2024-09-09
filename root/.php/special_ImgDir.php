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

require("PageLike.php");
require_once("mtimes.php");

$html = "<div class='image-directory'>\n";
$dn = $wiki->subdir;
if( $dn === "" ) {
  $ddn = "/";
} else {
  $ddn = $dn;
}
$headerTitle = "Images in $ddn";
$rows = [];

[ $dirs, $pages, $files ] = get_dir_contents($storage,$subdir);
$files = array_map(function($x) {
   return "<li class='img-dir-entry'><a href='$x'><img src='$x'/><span class='img-filename'>$x</span></a>"; },$files);

if( count($files) > 0 ) {
  $t = "<ol class='directory-list dir-images'>".implode(" ",$files)."</ol>";
} else {
  $t = "<p>Directory ".$ddn." is contains no images.</p>";
}

$page_source = "<Image Directory>";
$page_rendered = $t;

require("RenderPageLike.php");

