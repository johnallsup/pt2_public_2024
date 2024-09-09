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
require_once("ptmd.php");

$navbar_source = "";
$page_source = "# Search

To search, use a url of the form:
* `/.w/search/terms` for pages matching at least one term
* `/.w/.a/search/terms` for pages matching all of the terms
* `/.t/tag` for pages with matching tags
* `/.c/Search/TeRms` for case insensitive search (can use `.a`)
";

$ptmd = new PTMD($wiki);
$page_rendered = $ptmd->render($page_source,[]);

require("RenderPageLike.php");
