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

# This you must figure out for yourself.
# For a public wiki, is_auth("view") is true,
#       and is_auth("edit") is true if and only if the user is authenticated.
# I use cookies in one way or another, and on my instances of pt2,
# this file checks for the presence of those cookies.
# Obviously I'm not sharing the auth code I use here.
function is_auth($what) {
  switch(ACCESS) {
  case "wideopen":
    return true;
  case "public":
    switch($what) {
      case "view":
        return true;
      default:
        return false;
    }
  default:
    return false;
  }
}
