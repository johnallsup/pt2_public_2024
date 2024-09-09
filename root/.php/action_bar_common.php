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
function render_action_bar_mtime($wiki) {
  $pagemtime = $wiki->pagemtime;
  if( $pagemtime !== 0 ) {
    $d = new DateTime('@'.$pagemtime);
    $dt = "    ".$d->format('l Y-m-d H:i:s T');
  } else {
    $dt = "    Page '<span class='pagename'>".$wiki->pagename."</span>' does not exist.";
  }
  return "<span class='mtime'>$dt</span>";
}
