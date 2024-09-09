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
require_once("ptmd.php");
require_once("breadcrumbs.php");
$scripts->addscr("window.pageName = '".$wiki->pagename."'\nwindow.pagePath = '".$wiki->url."'");
$scripts->addscr("window.dir = ".gen_dir_json());

$a = basename($wiki->path);
$b = dirname($wiki->path);
$n = $wiki->pagename;
if( $n === "home" ) {
  if( $b !== "." ) {
    $n .= " <span class='homesuffix'>$b</span>";
  }
}
$headerTitle = $n;

// RENDER
if( $wiki->action === "view" ) {
  require("RenderView.php");
} else if( $wiki->action === "edit" ) {
  require("RenderEdit.php" );
} else if( $wiki->action === "typing" ) {
  require("RenderTyping.php");
} else {
  echo "Invalid action $wiki->action\n";
  exit();
}
