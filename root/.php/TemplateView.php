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
$options_json = json_encode($options);
$scripts->addscr("window.pageOptions = $options_json");
echo $htmlmeta->join("\n")."\n\n";
echo $scripts->join("\n")."\n\n";
echo $styles->join("\n")."\n\n";
if( is_auth("edit") ) {
  echo '<script>
  window.ping_interval = 60*1000
  window.page_mtime = '.$wiki->pagemtime.'
  window.addEventListener("load",_ => {
    window.setInterval(_ => {
      let ajax = window.ptui.ajax
      let path = "'.$url.'"
      if( path.match(/\./) ) { console.log("speical so no mtime"); return }
      ajax.mtime(path,data => {
        console.log({data})
        let { mtime } = data
        console.log(mtime,page_mtime,mtime === page_mtime)
        if( mtime !== page_mtime ) {
          document.body.classList.add("stale")
        } else {
          document.body.classList.remove("stale")
        }
      },error => {
        console.log({error})
      })   
    },ping_interval)
  })
  </script>';
}
?>
</head>
<?php
$classes = $bodyclasses->join(" ");
if( $classes !== "" ) {
  echo "<body class='$classes'>\n";
} else {
  echo "<body>\n";
}?>
<div class="container">
<header>
<?php
  require("TemplateView_Header.php");
?>
</header>
<section class="main">
<?php echo $page_rendered; ?>
<div class='clearer'>&nbsp;</div>
</section>
</div>
<?php
include("TemplateView_Footer.php");
?>
</body>
</html>
