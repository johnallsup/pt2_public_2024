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
// COMMON
require_once("page_source.php");

$page_source_parsed = new PageSource($page_source);
$page_source = $page_source_parsed->src;
if( !is_null($navbar_source) ) {
  $navbar_source_parsed = new PageSource($navbar_source);
  $navbar_source = $navbar_source_parsed->src;
} else {
  $navbar_source_parsed = null;
}
$meta = $page_source_parsed->meta;
$options = $page_source_parsed->options;
$tags = $page_source_parsed->tags;
if( isset($meta['cls']) ) {
  $xs = preg_split('/\s+/',trim($meta['cls']));
  foreach($xs as $x) {
    $bodyclasses->add($x);
  }
}

if( isset($meta["title"]) ) {
  $headerTitle = $meta["title"];
}

// PAGE SPECIFIC
$ptmd = new PTMD($wiki);
if(! is_null($fontsize) ) {
  $sty = "```style
section.main {
font-size: {$fontsize}rem;
}
```
";
  $page_source = $sty."\n".$page_source;
}
$page_rendered = $ptmd->render($page_source,$options,$meta);
$uses = $ptmd->uses;
if( isset($uses["abc"]) ) {
  $scripts->add("<script src='/js/abcjs-basic-min.js'></script>");
  $scripts->add("<script src='/js/abc-auto.js'></script>");
}
if( isset($uses["math"]) ) {
  $scripts->add("<script>
MathJax = {
  tex: {
    inlineMath: [['\\\\(', '\\\\)']],
    displayMath: [['\\\\[', '\\\\]']]
  },
  svg: {
    fontCache: 'global'
  }
}
</script>");
  $scripts->add("<script type='text/javascript' id='MathJax-script' async
  src='https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js'></script>");
}
if( !is_null($navbar_source) ) {
  $navbar_rendered = "<nav>
  ".$ptmd->render($navbar_source,$options)."
</nav>";
} else {
  $navbar_rendered = "";
}
if( is_mobile )  {
  require("TemplateView_Mobile.php");
} else {
  require("TemplateView.php");
}
