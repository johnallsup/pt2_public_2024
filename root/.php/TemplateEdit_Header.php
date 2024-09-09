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
<a href="<?php echo $wiki->pagename;?>" target="_blank" class="action duplicate block">Dup</a>
<a href="<?php echo $wiki->pagename;?>" target="_blank" class="action duplicate-edit block">DupEdit</a>
<a href="<?php echo $wiki->pagename.'?action=versions';?>" class="action versions block">Vers</a>
<a href="<?php echo $wiki->pagename;?>" target="_blank" class="action abort block">Abort</a>
<a href="<?php echo $wiki->pagename;?>" target="_blank" class="action show-preview block">Preview</a>
<span class="spacer block"></span>
<span class="action icon save block">&#128190;</span>
</section>
<section class="title">
<h1 class="page-title"><?php echo $headerTitle; ?></h1>
<div class="info spreadwide">
<span class="breadcrumbs"><?php
echo breadcrumbs($wiki->subdir);
?></span>
  
<?php
if( isset($_GET["version"]) ) {
  ?><span class="mtime" version="<?php echo $_GET["version"]; ?>">
    <span class="version-indicator">version:</span>
    <span class="time"><?php echo $mtime_fmt_short; ?></span>
    </span><?php
} else {
  ?><span class="mtime">
    <span class="time"><?php echo $mtime_fmt_short; ?></span>
    </span><?php
}
?>
</div>
</section>
<section subpage="hamburger">
<div class="text-size-options spread">
<button class='action editor-fixquotes'>Fq</button>
<button class='action editor-codemirror'>Cm</button>
<button class='action editor-normal-font'>Normal</button>
<button class='action editor-large-font'>Large</button>
<button class='action editor-huge-font'>Huge</button>
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
<?php
if( $more_options ) {
  ?><section subpage='more-options'>
    <div><?php echo $more_options;?></div>
    </section><?php
}
?>

