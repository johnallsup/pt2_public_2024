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
?><h1 class='page-title'><?php echo $headerTitle; ?></h1>
<?php
if( isset($meta['subtitle'] ) ) {
  echo "<h2 class='page-subtitle'>".$meta['subtitle']."</h2>\n";
}
if( ! is_null($navbar_source) ) {
  echo $navbar_rendered; 
}
