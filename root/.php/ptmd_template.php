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
require_once("protect.php");
require_once("Parsedown.php");
require_once("truthy.php");
require_once("defs.php");

# Parser for PTMD -- most of the work is done by Parsedown
# but we want to turn WikiWords to links provided they occur in text (not headings, nor code, nor maths etc.)

# We need storage to get directories.
# So we do want a $wiki object to accumulate stuff.
class PTMD extends stdclass {
  function __construct($wiki) {
    $this->wiki = $wiki;
    $this->parsedown = new Parsedown();
    $this->uses = [];
  }
  function get_option_bool($optname,$default=false) {
    $options = &$this->options;
    return truthy(array_get($options,$optname,$default),$default);
  }
  function WikiWord_to_link($match) {
    $word = $match[0];
    if( preg_match("/[a-z]/",$word) ) {
      return "[$word]($word)";
      #return "<span class='WikiWord'>$word</span>";
    } else {
      return $word;
    }
  }
  ### INLINE SPECIALS
  function special_inline_youtube($what,$args) {
    # Turn [[y:youtube-id]] into embedded youtube videos
    return "<iframe width='420' height='315' src='https://www.youtube.com/embed/$args'></iframe>";
  }
  function char_out_expletive($word) {
    $chars = '$@#%&!';
    $c = strlen($chars);
    $hash = hash("sha256",$chars);
    $l = strlen($word) - 2;
    if( $l < 1 ) return $l;
    $a = $word[0];
    $z = $word[$l+1];
    $s = null;
    for( $i = 0; $i < $l; $i++ ) {
      $h = $hash[$i%strlen($hash)];
      $h = intval($h,16);
      $h %= $c - 1;
      if( ! is_null($s) ) {
        $cs = str_replace("$s","",$chars);
      } else {
        $cs = $chars;
      }
      $x = $cs[$h];
      $s = $x;
      $a .= $s;
      }
    $a .= $z;
    return $a;
  }
  function special_inline_expletive($what,$args) {
    $args = trim($args);
    $l = strlen($args);
    if( $l < 3) {
      return "**expletive**";
    } else {
      return "**".$this->char_out_expletive($args)."**";
    }
  }
  function special_inline_dir($what,$args) {
    $wiki = $this->wiki;
    $subdir = $wiki->subdir;
    $storage = $wiki->storage;
    $pages = $storage->getglob($subdir."/*.".PAGE_EXT);
    $files = $storage->getglob($subdir."/*.*");
    $dirs = $storage->getglob($subdir."/*");
    $dirs = array_filter($dirs, function ($x) use($storage) { return $storage->isdir($x); } );
    $files = array_filter($files,function ($x) { return !preg_match('/\\.'.PAGE_EXT.'$/',$x); });
    $dirs = array_map(function($x) { return basename(trim($x,"/")); },$dirs);
    $dirs = array_filter($dirs,function($x) { return $x !== "" ; });
    $files = array_map(function($x) { return basename(trim($x,"/")); },$files);
    $pages = array_map(function($x) { return basename(trim($x,"/")); },$pages);
    $pages = array_filter($pages, function($x) { return preg_match('/^[^\.]+\.'.PAGE_EXT.'$/',$x); });
    $files = array_diff($files,$pages);
    
    $result = [];
    $opts = ["pages"=>false,"dirs"=>false,"files"=>false,"images"=>false,"regex"=>null,"fmt"=>null,"except"=>null];
    $args = preg_split("/\s*,\s*/",trim($args));
    foreach($args as $x) {
      $xs = explode("=",$x,2);
      if( count($xs) == 1 ) {
        array_push($xs,true);
      } else {
        if( ! preg_match('/^(regex|except)$/',$xs[0]) ) {
          $xs[1] = truthy($xs[1],false);
        }
      }
      $opts[$xs[0]] = $xs[1];
    }
    if( $opts["pages"] ) {
      foreach($pages as $page) {
        $page = preg_replace('/\.'.PAGE_EXT.'$/','',$page);
        if( is_string($opts['except']) && preg_match("/".$opts['except']."/",$page ) ) {
          continue;
        }
        if( is_string($opts['regex']) ) {
          if( preg_match("/".$o['regex']."/",$page ) ) {
            array_push($result,"[$page](".urlencode($page).")");
          }
        } else {
          array_push($result,"[$page](".urlencode($page).")");
        }
      }
    }
    if( $opts["dirs"] ) {
      foreach($dirs as $dir) {
        if( is_string($opts['except']) && preg_match("/".$opts['except']."/",$dir ) ) {
          continue;
        }
        if( is_null($opts['regex']) || preg_match("/".$opts['regex']."/",$dir ) ) {
          array_push($result,"[$dir](".urlencode($dir).")");
        }
      }
    }
    if( $opts["files"] ) {
      foreach($files as $file) {
        if( is_string($opts['except']) && preg_match("/".$opts['except']."/",$file ) ) {
          continue;
        }
        if( is_null($opts['regex']) || preg_match("/".$opts['regex']."/",$file ) ) {
          array_push($result,"[$file](".urlencode($file).")");
        }
      }
    }
    if( $opts["images"] && ! $opts["files"] ) {
      foreach($files as $file) {
        if( is_string($opts['except']) && preg_match("/".$opts['except']."/",$file ) ) {
          continue;
        }
        if( preg_match(IMAGE_REGEX,$file) ) {
          if( is_null($opts['regex']) || preg_match("/".$opts['regex']."/",$file ) ) {
            array_push($result,"[$file](".urlencode($file).")");
          }
        }
      }
    }
    return implode(" ",$result); 
  }
  function special_inline_jucedocl($what,$args) {
    # juce class docs
    $args = trim($args);
    # once Parsedown meets html, it stops expanding markdown, so we need to protect this from the parser
    # return "<a href='https://docs.juce.com/master/class{$args}.html' class='inline special juce class'><span class='juce-prefix cpp-prefix'>juce::</span><span class='class-name'>$args</span></a>";
    return "[juce::$args](https://docs.juce.com/master/class{$args}.html)";
  }
  ### END INLINE SPECIALS
  ### BLOCK SPECIALS
  function special_block_aside($what,$options,$content) {
    $content_html = $this->parsedown->text($content);
    return "<div class='aside'>$content_html</div>";
  }
  function special_block_htmlcomment($what,$options,$content) {
    return "<!--\n$content\n-->";
  }
  function special_block_NoteToSelf($what,$options,$content) {
    $content_html = $this->parsedown->text($content);
    return "<div class='note-to-self'>$content_html</div>";
  }
  function special_block_defn($what,$options,$content) {
    #$content_html = $this->parsedown->text($content);
    $options = trim($options);
    if( $options == "" ) $terms = "";
    else {
      $terms = implode(" ",preg_split('/\s+/',trim($options)));
      $terms = " terms='$terms'";
    }
    $paras = preg_split('/\n{2,}/',trim($content));
    $t = "";
    $first = true;
    foreach($paras as $para) {
      $t .= $this->parsedown->text($para);
    }
    return "<div class='definition' $terms>$t</div>";
  }
  function special_block_indblock($what,$options,$content) {
    #$content_html = $this->parsedown->text($content);
    $options = trim($options);
    $paras = preg_split('/\n{2,}/',trim($content));
    $t = "";
    $first = true;
    foreach($paras as $para) {
      $t .= $this->parsedown->text($para);
    }
    return "<div class='indblock'><p><span class='heading'>".trim($options).".</span></p>\n$t</div>";
  }
  function special_block_warning($what,$options,$content) {
    $a = trim($options);
    $paras = preg_split('/\n{2,}/',trim($content));
    $first_para = array_shift($paras);
    if( $a === "" ) { $a = "Warning"; }
    $t = "<div class='warning'>\n";
    $t .= "<div class='warning-para'><span class='warning-prefix'>$a:</span>&nbsp;<span class='warning-text'>$first_para</span></div>\n";
    foreach($paras as $para) {
      $t .= "<div class='warning-para'><span class='warning-text'>$para</span></div>\n";
    }
    $t .= "</div>\n";
    return $t;
  }
  function special_block_prayer1($what,$options,$content) {
    $paras = preg_split('/\n{2,}/',trim($content));
    $t = "<div class='prayer1'>\n";
    foreach($paras as $para) {
      [$who,$what] = explode(".",$para,2);
      $lines = explode("\n",$what);
      $first_line = array_shift($lines);
      $paras = [];
      array_push($paras, "<span class='who'>$who</span> $first_line");
      foreach($lines as $line) {
        $line = preg_replace('/\*\*(.*?)\*\*/',"<strong>$1</strong>",$line);
        $line = preg_replace('/\*(.*?)\*/',"<em>$1</em>",$line);
        array_push($paras,$line);
      }
      $paras = array_map(function($x) { return "<div class='prayer-para'>$x</div>"; },$paras);
      $s = implode("\n",$paras);
      $who = trim($who);
      $what = trim($what);
      $t .= "<div class='prayer' for='$who'>\n$s\n</div>";
    }
    $t .= "</div>";
    return $t;
  }
  function special_block_multi($what,$options,$content) {
    $lines = explode("\n",$content);
    $t = "";
    $line = preg_split("/\\s+/",trim(array_shift($lines)));
    $s = "";
    foreach($line as $word) {
      $s .= "<a href='$word'>$word</a> ";
    }
    $t .= "<p class='hello'>".trim($s)."</p>\n";
    if( count($lines) == 0 ) return $t;
    $line = trim(array_shift($lines));
    $t .= $this->special_block_duolingo("duolingo","apple orange","$line\n$line\n$line\n\n$line\n$line")."\n";
    if( count($lines) == 0 ) return $t;
    $t .= $this->special_block_poem("poem","",implode("\n",$lines));
    return $t;
  }
  function special_block_poetry($what,$options,$content) {
    return $this->special_block_poem("poetry",$options,$content);
  }
  function special_block_quote2($what,$options,$content) {
    return $this->special_block_poem("quote2",$options,$content);
  }
  function special_block_axioms($what,$options,$content) {
    return $this->special_block_poem("axioms",$options,$content);
  }
  function special_block_duolingo($what,$options,$content) {
    # for formatting the pairs of sentences we find in duolingo
    $t = "";
    $options = trim($options);
    if( $options === "" ) {
      $meta = $this->meta;
      if( isset($meta["duolingo"]) ) {
        $options = trim($meta["duolingo"]);
      }
    }
    
    $tlang = "";
    $slang = "";
    if( preg_match('/(\S+)\s+(\S+)/',$options,$m) ) {
      [ $all, $tlang, $slang ] = $m;
      $tlang = "<span class='target-lang'>$tlang</span>: ";
      $slang = "<span class='source-lang'>$slang</span>: ";
    }
    $sentences = preg_split('/\n{2,}/',$content);
    $t .= "<div class='duolingo-sentences'>\n";
    foreach($sentences as $s) {
      $lines = explode("\n",$s);
      $t .= "<div class='duolingo-sentence'>\n";
      $ts = array_shift($lines);
      $t .= "<div class='duolingo-target-sentence'>$tlang<span class='sentence'>$ts</span></div>\n";
      if( count($lines) > 0 ) {
        $ss = array_shift($lines);
        $t .= "<div class='duolingo-source-sentence'>$slang<span class='sentence'>$ss</span></div>\n";
      }
      if( count($lines) > 0 ) {
        $s = implode("\n",$lines);
        $t .= "<p class='duolingo-comment'>$s</p>\n";
      }
      $t .= "</div>\n";
    }
    $t .= "</div>\n";
    return $t;
  }
  function special_block_langue1($what,$options,$content) {
    # for formatting pairs of translated language (one line foreign, the other english)
    if( trim($content) === "" ) return "";
    $paras = preg_split('/\n{2,}/',trim($content));
    $out_paras = "";
    foreach($paras as $para) {
      $out_para = "<table class='langue1-table'>\n";
      $lines = explode("\n",$para);
      foreach($lines as $line) {
        if( $line[0] === "#" ) {
          $line = trim(substr(ltrim($line,"#"),1));
          $out_para .= "<tr><td class='heading' colspan='2'>$line</td></tr>\n";
        } else if( preg_match('/^(.*?)\s+---\s+(.*)$/',$line,$m) ) {
          [ $all, $for, $eng ] = $m;
          $out_para .= "<tr class='langue-item'><td class='foreign'>$for</td><td class='english'>$eng</td></tr>\n";
        } else {
          $out_para .= "<tr class='langue-item'><td class='foreign' colspan='2'>$line</td></tr>\n";
        }
      }
      $out_para .= "</table>\n";
      $out_paras .= $out_para;
    }
    return "<div class='langue1'>\n$out_paras</div>\n";
  }
  function special_block_keyboardshortcuts($what,$options,$content) {
    # for formatting lists of keyboard shortcuts
    $lines = explode("\n",trim($content));
    $t = "<table class='keyboard-shortcuts'>\n";
    foreach($lines as $line) {
      if( preg_match("/^(.*?)\s+---\s+(.*)$/",$line,$m) ) {
        [ $all, $combo, $desc ] = $m;
        $t .= "<tr><td class='combo'>$combo</td><td class='description'>$desc</td></tr>\n";
      } else {
        $t .= "<tr><td class='comment' colspan='2'>$line</td></tr>\n";
      }
    }
    $t .= "</table>\n";
    return $t;
  }
  function special_block_poem($what,$options,$content) {
    # format a poem
    $meta = [];
    foreach($this->meta as $k => $v) {
      if( preg_match('/^poem-(\w+)/',$k,$m) ) {
        $meta[$m[1]] = trim($v);
      }
    }
    $lines = explode("\n",trim($content));
    while( count($lines) > 0 && preg_match("/: /",$lines[0]) ) {
      [ $k, $v ] = explode(":",array_shift($lines),2);
      $v = trim($v);
      $meta[$k] = $v;
    }
    $content = implode("\n",$lines);
    $verses = preg_split('/\n{2,}/',trim($content));
    $verses = array_map(function($x) { return preg_replace('/\*\*(.*?)\*\*/','<strong>\1</strong>',$x); },$verses);
    $verses = array_map(function($x) { return preg_replace('/\*(.*?)\*/','<em>\1</em>',$x); },$verses);
    $verses = array_map(function($x) { return "<p class='verse'>$x</p>"; },$verses);
    $verses = implode("\n",$verses);
    if( count($meta) > 0 ) {
      $t = "<div class='meta poem-meta'>\n";
      foreach($meta as $k => $v) {
        $t .= "<div class='meta-item'><span class='key'>$k</span>: <span class='value'>$v</span></div>\n";
      }
      $t .= "</div>";
      $meta = $t;
    } else {
      $meta = "";
    }

    return "<div class='poem block-special'>\n".$meta.$verses."\n</div>";
  }
  function special_block_script($type,$options,$content) {
    # inline script
    if( $options !== "" ) $options = " ".$options;
    return "<script$options>\n".trim($content)."\n</script>\n";
  }
  function special_block_style($type,$options,$content) {
    # inline css
    if( $options !== "" ) $options = " ".$options;
    return "<style$options>\n".trim($content)."\n</style>\n";
  }
  function special_block_quotescr($type,$options,$content) {
    $paras = preg_split('/\n{2,}/',trim($content));
    $t = "<div class='quotes-script'>\n";
    foreach($paras as $para) {
      if( preg_match('/^(\S+):\s+(.*)$/s',$para,$m) ) {
        [ $all, $who, $line ] = $m;
        $t .= "<div class='script-line with-who'><span class='who'>$who:</span> <span class='script-quote'>$line</span></div>\n";
      } else if( preg_match('/^---\s+(.*)$/s',$para,$m) ) {
        [ $all, $line ] = $m;
        $t .= "<div class='attribution'><span class='dashes'>&mdash;</span> <span class='quote-source'>$line</span></div>\n";
      } else {
        $t .= "<div class='script-comment'>$line</div>\n";
      }
    }
    $t .= "</div>\n";
    return $t;
  }
  function special_block_bible1($type,$options,$content) {
    # quotes
    $quotes = explode("\n\n",$content);
    $t = "<div class='bible1'>\n";
    foreach($quotes as $quote) {
      if( preg_match("/^(.*)\s+---\s+(.*?)$/s",$quote,$m) ) {
        [ $all, $text, $verseref ] = $m;
        $text = trim($text);
      } else {
        $verseref = trim($options);
        $text = trim($quote);
      }
      if( $verseref !== "" ) {
        $ftext = $this->parsedown->text($text);
        $ftext = preg_replace('/^<p>/',"",$ftext);
        $ftext = preg_replace('@</p>$@',"",$ftext);
        $ftext = preg_replace('/((\d+:)?\d+)/','<span class="verse-number">\1</span>',$ftext);
        $t .= "<p class='quote'><span class='quote-text'>$ftext</span> &mdash; <span class='verseref'>$verseref</span></p>\n";
      } else {
        $ftext = $this->parsedown->text($quote);
        $ftext = preg_replace('/^<p>/',"",$ftext);
        $ftext = preg_replace('/((\d+:)?\d+)/','<span class="verse-number">\1</span>',$ftext);
        $t .= "<p class='quote'><span class='quote-text'>$ftext</span></p>\n";
      }
    }
    $t .= "</div>";
    return $t;
  }
  function special_block_quotes1($type,$options,$content) {
    # quotes
    $quotes = explode("\n\n",$content);
    $t = "<div class='quotes1'>\n";
    foreach($quotes as $quote) {
      if( preg_match("/^(.*)\s+---\s+(.*?)$/s",$quote,$m) ) {
        [ $all, $text, $author ] = $m;
        $text = trim($text);
      } else {
        $author = trim($options);
        $text = trim($quote);
      }
      if( $author !== "" ) {
        $t .= "<p class='quote'><span class='quote-mark'>&#x201C;</span><span class='quote-text'>$text</span><span class='quote-mark'>&#x201D;</span> &mdash; <span class='author'>$author</span></p>\n";
      } else {
        $t .= "<p class='quote'><span class='quote-mark'>&#x201C;</span><span class='quote-text'>$quote</span><span class='quote-mark'>&#x201D;</span></p>\n";
      }
    }
    $t .= "</div>";
    return $t;
  }
  function special_block_quotes2($type,$options,$content) {
    # quotes
    $quotes = explode("\n\n",$content);
    $t = "<div class='quotes2'>\n";
    foreach($quotes as $quote) {
      if( preg_match("/^(.*)\s+---\s+(.*?)$/s",$quote,$m) ) {
        [ $all, $text, $author ] = $m;
        $text = trim($text);
      } else {
        $author = trim($options);
        $text = trim($quote);
      }
      if( $author !== "" ) {
        $t .= "<p class='quote'><span class='quote-mark'>&#x201C;</span><span class='quote-text'>$text</span><span class='quote-mark'>&#x201D;</span> &mdash; <span class='author'>$author</span></p>\n";
      } else {
        $t .= "<p class='quote'><span class='quote-mark'>&#x201C;</span><span class='quote-text'>$quote</span><span class='quote-mark'>&#x201D;</span></p>\n";
      }
    }
    $t .= "</div>";
    return $t;
  }
  function special_block_bookquote($type,$options,$content) {
    # quotes
    $attrib = trim($options);
    $paras = preg_split('/\n{2,}/',trim($content));
    $t = "<div class='bookquote'>\n";
    foreach($paras as $para) {
      $h = $this->parsedown->text($para);
      $h = preg_replace('@^<p>(.*)</p>$@s','\1',$h);
      $t .= "<div class='para'>$h</div>\n";
    }
    $t .= "<div class='attrib'>$attrib</div>\n";
    $t .= "</div>";
    return $t;
  }
  function special_block_plain($type,$options,$content)  {
    # plain text, do not turn into pre code, but use a pre to preserve whitespace
    $content = preg_replace('/^\s+$/m','',$content);
    $paras = preg_split('/\n{2}/',trim($content));
    $xs = explode(":",$options,2);
    $cls = "plain";
    if( count($xs) === 2 ) {
      $cs = trim($xs[0]);
      if( $cs !== "" ) {
        $cls .= " ".$cs;
      }
      $opts = trim($xs[1]);
    } else {
      $opts = trim($options);
    }
    if( $opts !== "" ) { $opts = " ".$opts; }
    $t = "";
    foreach($paras as $para) {
      $t .= "<div class='plain-para'>$para</div>\n";
    }
    return "<div class='$cls'$opts>$t\n</div>";
  }
  function special_block_abc($type,$options,$content) {
    # sheet music in ABC notation
    $this->uses["abc"] = true; 
    $content = trim($content);
    return "<div class='abc'>\n$content\n</div>\n";
  }
  function special_block_abcd($type,$options,$content) {
    # sheet music in ABC notation with default options
    $options = trim($options);
    $content = trim($content);
    $a = "";
    $a .= "X:1\n";
    $a .= "L:1/4\n";
    if( $options !== "" ) {
      $a .= "T:".$options."\n";
    }
    $a .= "M:4/4
K:C
$content";
    return $this->special_block_abc("abc","",$a);
  }
  ### END BLOCK SPECIALS
  # Renderer
  function render($source,$options,$meta=[],$wikiwords=true) {
    $this->options = &$options;
    $this->meta = &$meta;
    $x = $source;
    # protect from WikiWords and transform things like [[these]]

    $protect = new ProtectRegex();
    
    # protect->add_block("nohightlight",$callback,"nohighlight blocks);
    $protect->add("/^(```nohighlight\\s(.*?)^```)/ms",function($match) {
              return "<div class='nohightlight'>$match[2]</div>"; });
    # options need to be true/false/auto
    # if true or auto, add this rule
    # use($options) and if pattern happens, set $options[$abc"] = "true"
    # has issues for truthy
    # later replace this with a more flexible
    # and extensible block transfers system
    # ```blocktype args....\n\n```
    # ```blocktype(XX) args....\n\nXX``` # arbitrary delimiter
    
    $uses = &$this->uses;
    $protect->add('/^(```+)(.*?)?^\1/ms',function($match) use(&$uses) { 
      [ $block ] = $match;
      if( ! preg_match('/^(```+)(\S+)(.*?)$(.*)^\1/ms',$block,$m) ) {
        return $block;
      }
      [ $all, $ticks, $what, $options, $content ] = $m;
      $content = trim($content);
      $options = trim($options);

      if( preg_match('/^[A-Za-z]/',$what) ) {
        if( method_exists($this,$method="special_block_$what") ) {
          $block = $this->$method($what,$options,$content);
          $block .= "\n";
        }
      } else {
        $what_e = preg_replace('/^[a-zA-Z0-9_\.@#?-]/','_',$what);
        $all = htmlentities($all);
        $options_e = htmlentities($options);
        return "<div class='special block-special client-side-block' special='$what_e'><span class='options'>$options_e</span><div class='block-content'>$content</div></div>";
      }

      return $block;
    });

    $protect->add('/(`+)(.*?)\\1/');
    $re_bracket = '/\\\\\\[.*?\\\\\\]/s';
    $re_paren = '/\\\\\\(.*?\\\\\\)/s';
    $options["math"] = false;
    # worth adding a 'uses' flag, like 'math', so that
    # protect will handle adding entries indicating what's been used
    
    $protect->add($re_bracket,function($m) use(&$uses) { $uses["math"] = true; return $m[0];});
    $protect->add($re_paren,function($m) use(&$uses) { $uses["math"] = true; return $m[0];});

    $protect->add('@<a .*?</a>@is',function($match) { return $match[0]; } );
    //$source = preg_replace_callback(BIBLE_REGEX,[$this,"protect_bible"],$source);
    $protect->add(BIBLE_REGEX,function($match) {
      [$m,$ref,$text] = $match;
      return "<p class='bible_quote'><span class='ref'>$ref</span>&nbsp;<span class='text'>$text</span></p>";
    });
    $protect->add(HEADER_REGEX);
    //$protect->add(YOUTUBE_REGEX);
    $this->special_inline_shorthands = [
      "y" => "youtube"
    ];
    $protect->add(DBL_BRACKET_LINK_REGEX,function($match) use(&$uses) { 
      $a = $match[1];
      $b = explode(":",$a,2);
      if( preg_match('/^([^:]+):(.*)$/',$a,$m) ) {
        $what = $m[1];
        $args = $m[2];
        if( array_key_exists($what,$this->special_inline_shorthands) ) {
          $what = $this->special_inline_shorthands[$b[0]];
        }
        if( preg_match('/^[A-Za-z]/',$what) ) {
          if( method_exists($this,$method="special_inline_$what") ) {
            return $this->$method($what,$args);
          }
        } else {
          $what_e = preg_replace('/^[a-zA-Z0-9_\.@#?-]/','_',$what);
          $all = htmlentities($a);
          return "<span class='special inline-special client-side-inline' special='$what_e'>$all</span>";
        }
      }
      # we fall through if the special isn't matched
      $a_encoded = urlencode($a);
      $a_encoded = str_replace("%2F","/",$a_encoded); # we don't want to escape slashes in links
      return "[$a]($a_encoded)";
    });
    $protect->add(MD_IMGLINK_REGEX);
    $protect->add(MD_LINK_QUOTE_REGEX,function($match) {
        pre_dump("Link quote regex",$match);
        return "[$match[1]]($match[2])"; });
    $protect->add(MD_LINK_REGEX);
    $protect->add(URL_REGEX);
    $protect->add(BRACES_REGEX, function($match) { return $match[1]; });

    $x = $protect->do_protect($x);
    # do_protect will do all other transforms even if we don't want WikiWords
    # apply WikiWord transform
    if( $wikiwords ) {
      # TODO we're going to replace WikiWords with some client-side Javascript
      $x = preg_replace_callback(WIKIWORD_REGEX,[$this,"WikiWord_to_link"],$x);
    }
    # unprotect
    $x = $protect->un_protect($x);

    # protect from Parsedown
    $protect = new ProtectRegex();
    
    if( $this->get_option_bool("abc",true) ) {
      $protect->add("/^(?:```abc\\s(.*?)^```)/ms",function($match) {
        return "<div class='abc'>\n$match[1]\n</div>";
      });
    }
    if( $this->get_option_bool("math",true) ) {
      $re_bracket = '/\\\\\\[.*?\\\\\\]/s';
      $re_paren = '/\\\\\\(.*?\\\\\\)/s';
      $protect->add($re_bracket);
      $protect->add($re_paren);
    }

    $x = $protect->do_protect($x);

    # apply Parsedown->text
    $x = $this->parsedown->text($x);

    # unprotect
    $x = $protect->un_protect($x);

    # done
    return $x;
  }

  /// DIR STUFF
  function fmt_dir_ol($xs) {
    $t = "\n";
    foreach($xs as $x) {
      $t .= "1. $x\n";
    }
    $t .= "\n\n";
    #var_dump($t);
    return $t;
  }
  function fmt_dir_ul($xs) {
    $t = "\n";
    foreach($xs as $x) {
      $t .= "* $x\n";
    }
    $t .= "\n\n";
    #var_dump($t);
    return $t;
  }
  function makeDirOf($path,$opts) {
    $storage = $this->wiki->storage;
    $dirname = dirname($path);
    if( $dirname == "." ) { $dirname = ""; }

    
    $dirhandler = new WikiHandlerDir($this->wiki);
    $dirhandler->get_dir_contents();
    $dirs = $dirhandler->dirs;
    $pages = $dirhandler->pages;
    $files = $dirhandler->files;
    $result = [];
    $o = ["pages"=>false,"dirs"=>false,"files"=>false,"images"=>false,"regex"=>null,"fmt"=>null,"except"=>null];
    $os = preg_split("/\s*,\s*/",trim($opts));
    
    foreach($os as $ox) {
      $xs = explode("=",$ox,2);
      if( count($xs) == 1 ) {
        array_push($xs,true);
      } else {
        if( ! preg_match('/^(regex|except)$/',$xs[0]) ) {
          $xs[1] = truthy($xs[1],false);
        }
      }
      $o[$xs[0]] = $xs[1];
    }
    if( $o["pages"] ) {
      foreach($pages as $page) {
        $page = preg_replace('/\.ptmd$/','',$page);
        if( is_string($o['except']) && preg_match("/".$o['except']."/",$page ) ) {
          continue;
        }
        if( is_string($o['regex']) ) {
          if( preg_match("/".$o['regex']."/",$page ) ) {
            array_push($result,"[$page](".urlencode($page).")");
          }
        } else {
          array_push($result,"[$page](".urlencode($page).")");
        }
      }
    }
    if( $o["dirs"] ) {
      foreach($dirs as $dir) {
        if( is_null($o['regex']) || preg_match("/".$o['regex']."/",$dir ) ) {
          array_push($result,"[$dir](".urlencode($dir).")");
        }
      }
    }
    if( $o["files"] ) {
      foreach($files as $file) {
        if( is_null($o['regex']) || preg_match("/".$o['regex']."/",$file ) ) {
          array_push($result,"[$file](".urlencode($file).")");
        }
      }
    }
    if( $o["images"] ) {
      foreach($files as $file) {
        if( preg_match(IMAGE_REGEX,$file) ) {
          if( is_null($o['regex']) || preg_match("/".$o['regex']."/",$file ) ) {
            array_push($result,"[$file](".urlencode($file).")");
          }
        }
      }
    }
    $fmt = "fmt_dir_".$o['fmt'];
    if( method_exists($this,$fmt) ) {
      #var_dump($this->$fmt($result));
      return $this->$fmt($result);
    }
    return implode(" ",$result);
  }
}
