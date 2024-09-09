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
fmt_pagemtime($wiki->pagemtime);

$scripts->add("<script src='/js/wiki_edit.js'></script>");

// TODO: REFACTOR ONCE DONE, MOVE COMMON STUFF OUTSIDE THE SPECIFIC TEMPLATES
?><!DOCTYPE html>
<html>
<head>
  <meta charset='utf8'/>
  <title><?php echo "$pagename : /$subdir : ".SITE_SHORT_TITLE; ?></title>

<?php
require("favicon.php");
?>
<?php
require("localconfig.php");
?>
<?php
echo $htmlmeta->join("\n")."\n\n";
echo $scripts->join("\n")."\n\n";
echo $styles->join("\n")."\n\n";
?>
</head>
<body>
<div class="container">
<header>
<?php
$more_options = "<span class='action mo-leftarrow block'>&#x2190;</span>
<span class='action mo-rightarrow block'>&#x2192;</span>
<span class='action mo-prevheader block'>#-</span>
<span class='action mo-nextheader block'>#+</span>
<span class='action mo-prevline block'>&#x2191;</span>
<span class='action mo-nextline block'>&#x2193;</span>";
require("TemplateEdit_Header_Mobile.php");
?>
</header>
<section class="main">
<textarea name='source' class="editor" cols='80' rows='25' autofocus><?php echo htmlspecialchars($page_source); ?></textarea>
</section>
</div>
</body>
</html>
