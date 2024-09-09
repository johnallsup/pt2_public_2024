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
?><section class="topbar">
<span class="action hamburger icon block">&#9776;</span>
<a href="<?php echo $wiki->pagename;?>" target="_blank" class="action duplicate icon block">&CirclePlus;</a>
<a href="<?php echo $wiki->pagename.'?action=versions';?>" class="action versions icon block">&#9419;</a>
<span class="action show-goto-box icon block">G</span>
<span class="spacer block"></span>
<?php
if( $can_edit ) {
  ?><a class="action edit icon block" href="<?php echo pagename_with_action("edit"); ?>">&#x1F58A;</a><?php
}
?>
</section>
<section class="title">
<?php
require("Template_PageTitle.php");
?>
<div class="info spreadwide">
<span class="breadcrumbs"><?php
echo breadcrumbs($wiki->subdir);
?></span>
<?php
require('tags.php');
?>
  <span class="mtime">
<?php echo $mtime_fmt_short; ?></span>
</div>
</section>
<section subpage="hamburger" >
<div class="buttons spreadwide">
<span class="action touch-mode block">To</span>
</div>
<div class="info other-info spreadwide">
<span class="file-size"><?php
$src = $page_source;
$nchars = strlen($src);
$words = preg_split("@\s+@s",$src);
$nwords = count($words);
$lines = explode("\n",$src);
$nlines = count($lines);
echo "$nlines lines, $nwords words, $nchars chars";
?></span>
<span class="spacer"></span><span class="mtime">
<?php echo $mtime_fmt_long; ?></span>
</div>
</section>
