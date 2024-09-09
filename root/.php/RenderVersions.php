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

require_once("mtimes.php");

$versions = $storage->get_version_times($path);
if( count($versions) == 0 ) {
  http_response_code(404);
  $page_source = "There are no versions for $path";
} else {
  $t = "## Versions for $path\n";
  sort($versions,SORT_NUMERIC); 
  $versions = array_reverse($versions);
  foreach($versions as $version) {
    [ $mtime_fmt_long, $mtime_fmt_short ] = fmt_time($version);
    $t .= "* [$mtime_fmt_long]($wiki->pagename?version=$version)\n";
  }
  $page_source = $t;
}

$wiki->action = "view";
require("RenderPage.php");
