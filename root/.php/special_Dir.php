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

$html = "<div class='directory'>\n";
$dn = $wiki->subdir;
if( $dn === "" ) {
  $ddn = "/";
} else {
  $ddn = $dn;
}
$headerTitle = "Dir of $ddn";
$rows = [];

[ $dirs, $pages, $files ] = get_dir_contents($storage,$subdir);
if( $dir_type === "pages" ) {
  $dirs = [];
  $files = [];
} else if( $dir_type === "files" ) {
  $pages = [];
  $dirs = [];
} else if( $dir_type === "dirs" ) {
  $pages = [];
  $files = [];
}
$pages = array_map(function($x) { 
  $a = basename($x,".".PAGE_EXT); 
  return "<a href='$x' class='page'>$x</a>"; },$pages);
$dirs = array_map(function($x) {
   return "<a href='$x' class='dir'>$x</a>"; },$dirs);
$files = array_map(function($x) {
   return "<a href='$x' class='file'>$x</a>"; },$files);

$t = "";
if( count($pages) > 0 ) {
  $t .= "<h2>Pages</h2>\n";
  $t .= "<div class='directory-list dir-pages'>".implode(" ",$pages)."</div>";
  $t .= "\n\n";
}
if( count($dirs) > 0 ) {
  $t .= "<h2>Subdirectories</h2>\n";
  $t .= "<div class='directory-list dir-dirs'>".implode(" ",$dirs)."</div>";
  $t .= "\n\n";
}
if( count($files) > 0 ) {
  $t .= "<h2>Files</h2>\n";
  $t .= "<div class='directory-list dir-files'>".implode(" ",$files)."</div>";
  $t .= "\n\n";
}
if( $t === "" ) {
  $t = "<p>Directory ".$ddn." is empty.</p>";
}

$page_source = "<Directory>";
$page_rendered = $t;

require("RenderPageLike.php");

