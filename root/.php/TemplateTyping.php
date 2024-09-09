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

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo "$pagename - TYPING"; ?></title>
  <script src="/js/exp5.js"></script>
  <link rel="stylesheet" href="/css/exp5.css"/>
<?php 
$a = $source;
$a = str_replace("\r","",$a);
$a = preg_replace("@[“”]@",'"',$a);
$a = preg_replace("@[‘’]@","'",$a);
$a = preg_replace("@–@","--",$a);
$a = preg_replace("@—@","---",$a);
$lines = explode("\n",$a);
$outlines = [];
$from = null;
while(count($lines) > 0) {
  if( preg_match("@^(\w+):\s+(.*)$@",$lines[0],$m) ) {
    array_shift($lines);
    if( $m[1] === "from" ) {
      $from = "'$m[2]'";
    }
  } else {
    break;
  }
}
$a = implode("\n",$lines);
$a = preg_replace("/&/","&amp;",$a);
$a = preg_replace("/</","&lt;",$a);
$a = preg_replace("/>/","&gt;",$a);
$source = $a;

if( is_null($from) ) { $from = "null"; }
$from = "<script>
const attrib = $from
</script>";
$basefontsize = "<script>
    window.baseFontSize = 1.5
    </script>";

echo $from."\n";
?>
</head>
<body>
<?php
echo $source; 
?>
</body>
</html>
