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
echo $htmlmeta->join("\n")."\n\n";
?><script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/codemirror.min.js" defer></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/mode/javascript/javascript.min.js"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/mode/markdown/markdown.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/keymap/vim.min.js" defer></script>
<?php
echo $scripts->join("\n")."\n\n";
echo $styles->join("\n")."\n\n";
echo '<script>
window.ping_interval = 60*1000
window.page_mtime = '.$wiki->pagemtime.'
window.addEventListener("load",_ => {
  window.setInterval(_ => {
    let ajax = window.ptui.ajax
    let path = "'.$url.'"
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
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/codemirror.min.css">
<!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/theme/dracula.min.css">-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/theme/3024-night.min.css">
<script>
window.addEventListener("load",_ => {
  let editor_textarea = q("textarea.editor")

  function relativise(targetUrl) {
    // Links to the same subdomain are turned into
    // intra wiki links. Links to a subfolder
    // are turned into relative links, others
    // into links from the root.
    let targetUrl2 = targetUrl.split("?")[0]
    targetUrl2 = targetUrl2.replace(/\/home$/,"")
    function firstDifferingChar(x,y) {
      let l = Math.min(x.length,y.length)
      for(let i=0; i<l; i++) {
        let a = x[i]
        let b = y[i]
        if( a !== b ) return i
      }
      return l
    }
    function commonPrefix(x,y) {
      let i = firstDifferingChar(x,y)
      return x.substr(0,i)
    }
    function dirOf(x) {
      return x.replace(/[^\/]*$/,"",x)
    }
    let currentUrl = window.location.href.split("?")[0]
    let dirOfCurrent = dirOf(currentUrl)
    let dirOfTarget = dirOf(targetUrl2)
    let cp = commonPrefix(dirOfCurrent,dirOfTarget)
    let regex = /^https:\/\/[^/]+\//i;
    if( ! cp.match(regex) ) {
      // link is off site
      return targetUrl
    }
    // if link is in a subdir, use relative
    // else if link is within wiki, strip http and domain
    console.log({cp,dirOfTarget,a:cp.length,b:dirOfTarget.length})
    if( cp.length === dirOfTarget.length ) {
      return targetUrl2.replace(new RegExp("^https?://[^/]*/"),"/")
    }
    return targetUrl2.replace(regex,"/")
  }
  function getSelectedRange(cm) {
    return { from: editor.getCursor(true), to: editor.getCursor(false) };
  }
  function pasteLinkPlain(cm) {
    if(!navigator.clipboard) {
      console.log("pasteLink1 Can't access clipboard")
      return true // TODO test
    }
    console.log("pasteLinkPlain")    
    navigator.clipboard.readText()
      .then(paste => {
        paste = relativise(paste)
        let selectedRange = getSelectedRange(cm)
        let { from } = selectedRange
        let selected = cm.getSelection()
        let delta
        if( selected === "" ) {
          newText = `[]($paste})`
          delta = 1-newText.length
        } else {
          newText = `[{$selected}]({$paste})`
          delta = 0
        }
        cm.replaceSelection(newText)
        if( delta != 0 ) {
          selectedRange = getSelectedRange(cm)
          from = selectedRange.from
          from.ch += delta
          cm.setCursor(from)
        }
    })
  }
  
  /* to fix:
    * we want to store the lastSavedContent when we load the page,
    * and write to this when we successfully save, and we can
    * compare to this in the tick function.
   */
  window.startCodeMirror = function() {
    CodeMirror.commands.save = function() {
      window.ptui.save(false)
    }
    CodeMirror.Vim.map("jk","<Esc>","insert")
    let dirtyTickTimeout = null
    function dirtyTick() {
      if( dirtyTickTimeout ) clearTimeout(dirtyTickTimeout)
      let currentEditorContent = window.editor.getValue()
      if( currentEditorContent !== window.lastSavedSource ) {
        document.body.classList.add("dirty")
      }
      setTimeout(dirtyTick,1000)
    }
    setTimeout(dirtyTick,1000)
    window.editor = CodeMirror.fromTextArea(editor_textarea, {
      mode: "markdown", // Set the mode (e.g., JavaScript)
      lineNumbers: true,   // Display line numbers
      lineWrapping: true,
      theme: "3024-night",    // Set the dark theme
      styleActiveLine: true,  // Highlight the active line
      keyMap: "vim",
      extraKeys: {
        "Shift-Ctrl-V": function(cm) {
          return pasteLinkPlain(cm)
        },
        "Ctrl-C": function(cm) {
          console.log("Copy")
          const text = cm.getSelection()
          let clipboard = navigator.clipboard
          if( ! clipboard ) {
            console.warn("No clipboard")
            return
          }
          navigator.clipboard.writeText(text)
        },
      }
    })
    window.codeMirrorStarted = true
  }
  window.codeMirrorStarted = false
})
</script>
</head>
<body>
<div class="container">
<header>
<?php
$more_options = null;
$lines = explode("\n",$page_source,10);
$meta1 = [];
foreach($lines as $line) {
  if( preg_match('/^([a-zA-Z0-9]+):\s+(.*)$/',$line,$m) ) {
    $meta1[strtolower($m[1])] = $m[2];
  } else {
    break;
  }
}
if( isset($meta1['title']) ) {
  $headerTitle = $meta1['title'];
}
require("TemplateEdit_Header.php");
?>
</header>
<section class="main">
<textarea name='source' class="editor" autofocus><?php echo htmlspecialchars($page_source); ?></textarea>
</section>
</div>
</body>
</html>
