<?php

set_time_limit(0);
error_reporting(0);
require(".htconfig.php");
$db = @new mysqli($db_host,$db_user,$db_pass,$db_data);

header( "Content-type: text/html; charset=utf-8");
header( "Last-Modified: " . gmdate( "D, j M Y H:i:s" ) . " GMT" );
header( "Expires: " . gmdate( "D, j M Y H:i:s", time() ) . " GMT" );
header( "Cache-Control: no-store, no-cache, must-revalidate" ); // HTTP/1.1
header( "Cache-Control: post-check=0, pre-check=0", FALSE );
header( "Pragma: no-cache" ); // HTTP/1.0

$lang = 'en';

$offensive = $_GET['off'];
if($offensive == 'o')
  $adult = 2;
elseif($offensive == 'a')
  $adult = 1;
else
  $adult = 0;

$length = (($_GET['length']) ? intval($_GET['length']) : 0);
$numlines = ((intval($_GET['numlines'])) ? intval($_GET['numlines']) : 0); 
$cat = (($_GET['cat'] == '1') ? 1 : 0);
$equal = (($_GET['equal'] == '1') ? 1 : 0);
$stats = (($_GET['stats'] == '1') ? 1 : 0);

if(strlen($_GET['lang']))
  $lang = @$db->real_escape_string($_GET['lang']);

if(strlen($_GET['pattern']))
  $pattern = @$db->real_escape_string(urldecode($_GET['pattern']));

if(strlen($_GET['regex']))
  $regex = @$db->real_escape_string(urldecode($_GET['regex']));

if(strlen($_GET['db']))
  $table = @$db->real_escape_string(urldecode($_GET['db']));
else
  $table = '';

if($length < 0)
  $length = 0;
if($numlines < 0)
  $numlines = 0;

function do_query($table,$length,$numlines,$adult,$cat,$limit,$lang,$pattern,$regex,$equal) {
  global $db;
  $rnd = mt_rand();
  $r = array();

  $typesql   = (($table)  ? " WHERE `category` = '$table' " : " WHERE 1 ");
  $lengthsql = (($length) ? " AND LENGTH(`text`) < $length " : "" );

  if($adult == 2)
    $adultsql  = " AND offensive = 1 ";
  elseif($adult == 1)
    $adultsql = "";
  else
    $adultsql = " AND offensive = 0 ";


  if($numlines)
    $lengthsql .=
    " AND (LENGTH(`text`) - LENGTH(REPLACE(`text`,\"\n\",\"\"))) <= $numlines ";

  $langsql = " AND lang = '$lang' ";

  $patsql = '';
  if(strlen($pattern))
    $patsql = " AND MATCH text AGAINST ('$pattern' IN BOOLEAN MODE) ";

  $regexsql = '';
  if(strlen($regex))
    $regexsql = " AND text REGEXP '$regex' ";

  $eqsql = '';

  if($equal) {
    $catsavail = array();
    $res = @$db->query("SELECT DISTINCT ( `category` ) FROM `fortune` 
                           $typesql
                           $adultsql
                           $lengthsql
                           $langsql
                           $patsql 
                           $regexsql ");
    if($res->num_rows) {
      while($x = $res->fetch_array(MYSQL_ASSOC))
        $catsavail[] = $x['category'];
    
      $eqsql = " AND `category` = '"
        . $catsavail[mt_rand(0,$res->num_rows - 1)] . "' ";
   }
  }

  $result = @$db->query("SELECT `text`, `category` FROM `fortune` 
                         $typesql
                         $adultsql
                         $lengthsql
                         $langsql
                         $patsql
                         $regexsql
                         $eqsql
                         ORDER BY RAND($rnd) 
                         LIMIT $limit");

  if($result->num_rows) {
    while($x = $result->fetch_array(MYSQL_ASSOC))
      $r[] = fortune_to_html($x['text'])
        .(($cat) ? "<br />[{$x['category']}]<br />" : "");
  }
  return $r;
}


function do_stats($table,$length,$numlines,$adult,$cat,$limit,$lang,$pattern,$regex,$equal) {
  global $db;
  $rnd = mt_rand();
  $r = array();

  $typesql   = (($table)  ? " WHERE `category` = '$table' " : " WHERE 1 ");
  $lengthsql = (($length) ? " AND LENGTH(`text`) < $length " : "" );

  if($adult == 2)
    $adultsql  = " AND offensive = 1 ";
  elseif($adult == 1)
    $adultsql = "";
  else
    $adultsql = " AND offensive = 0 ";


  if($numlines)
    $lengthsql .=
    " AND (LENGTH(`text`) - LENGTH(REPLACE(`text`,\"\n\",\"\"))) <= $numlines ";

  $langsql = " AND lang = '$lang' ";

  $patsql = '';
  if(strlen($pattern))
    $patsql = " AND MATCH text AGAINST ('$pattern' IN BOOLEAN MODE) ";

  $regexsql = '';
  if(strlen($regex))
    $regexsql = " AND text REGEXP '$regex' ";

  $eqsql = '';

  $result = @$db->query("SELECT `text`, `category` FROM `fortune` 
                         $typesql
                         $adultsql
                         $lengthsql
                         $langsql
                         $patsql
                         $regexsql
                         $eqsql");


   echo '<br />' . $result->num_rows . ' matching quotations.<br />';


   $res = @$db->query("SELECT DISTINCT ( `category` ) FROM `fortune` 
                           $typesql
                           $adultsql
                           $lengthsql
                           $langsql
                           $patsql 
                           $regexsql ");
    if($res->num_rows) {
      echo '<br />Matching Databases:<br />';
      while($x = $res->fetch_array(MYSQL_ASSOC))
        echo $x['category'].'<br />';
    
   }
   else
     echo '<br />No matching databases using those search parameters - please refine your options.<br />';
   

}


function fortune_to_html($s) {

  // First pass - escape all the HTML entities, and while we're at it
  // get rid of any MS-DOS end-of-line characters and expand tabs to
  // 8 non-breaking spaces, and translate linefeeds to <br />.
  // We also get rid of ^G which used to sound the terminal beep or bell
  // on ASCII terminals and were humourous in some fortunes.
  // We could map these to autoplay a short sound file but browser support
  // is still sketchy and then there's the issue of where to locate the
  // URL, and a lot of people find autoplay sounds downright annoying.
  // So for now, just remove them.

  $s = str_replace(
    array("&",
          "<",
          ">",
          '"',
          "\007",
          "\t",
          "\r",
          "\n"),

    array("&amp;",
          "&lt;",
          "&gt;",
          "&quot;",
          "",
          "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",
          "",
          "<br />"),
    $s);
  // Replace pseudo diacritics
  // These were used to produce accented characters. For instance an accented
  // e would have been encoded by '^He - the backspace moving the cursor
  // backward so both the single quote and the e would appear in the same
  // character position. Umlauts were quite clever - they used a double quote
  // as the accent mark over a normal character.

  $s = preg_replace("/'\010([a-zA-Z])/","&\\1acute;",$s);
  $s = preg_replace("/\&quot;\010([a-zA-Z])/","&\\1uml;",$s);
  $s = preg_replace("/\`\010([a-zA-Z])/","&\\1grave;",$s);
  $s = preg_replace("/\^\010([a-zA-Z])/","&\\1circ;",$s);
  $s = preg_replace("/\~\010([a-zA-Z])/","&\\1tilde;",$s);

  // Ignore multiple underlines for the same character. These were
  // most useful when sent to a line printer back in the day as it
  // would type over the same character a number of times making it
  // much darker (e.g. bold). I think there are only one or two
  // instances of this in the current (2008) fortune cookie database.

  $s = preg_replace("/(_\010)+/","_\010",$s);
  // Map the characters which sit underneath a backspace.
  // If you can come up with a regex to do all of the following
  // madness  - be my guest.
  // It's not as simple as you think. We need to take something
  // that has been backspaced over an arbitrary number of times
  // and wrap a forward looking matching number of characters in
  // HTML, whilst deciding if it's intended as an underline or
  // strikeout sequence.

  // Essentially we produce a string of '1' and '0' characters
  // the same length as the source text.
  // Any position which is marked '1' has been backspaced over.

  $cursor = 0;
  $dst = $s;
  $bs_found = false;
  for($x = 0; $x < strlen($s); $x ++) {
    if($s[$x] == "\010" && $cursor) {
      $bs_found = true;
      $cursor --;
      $dst[$cursor] = '1';
      $dst[$x] = '0';
      $continue;
    }
    else {
      if($bs_found) {
        $bs_found = false;
        $cursor = $x;
      }
      $dst[$cursor] = '0';
      $cursor ++;
    }

  }

  $out = '';
  $strike = false;
  $bold = false;

  // Underline sequence, convert to bold to avoid confusion with links.
  // These were generally used for emphasis so it's a reasonable choice.
  // Please note that this logic will fail if there is an underline sequence
  // and also a strikeout sequence in the same fortune.

  if(strstr($s,"_\010")) {
    $len = 0;
    for($x = 0; $x < strlen($s); $x ++) {
      if($dst[$x] == '1') {
        $len ++;
        $bold = true;
      }
      else {
        if($bold) {
          $out .= '<strong>';
          while($s[$x] == "\010")
             $x ++;
          $out .= substr($s,$x,$len);
          $out .= '</strong>';
          $x = $x + $len - 1;
          $len = 0;
          $bold = false;
        }
        else
          $out .= $s[$x];
      }
    }
  }

  // These aren't seen very often these days - simulation of
  // backspace/replace. You could occasionally see the original text
  // on slower terminals before it got replaced. Once modems reached
  // 4800/9600 baud in the late 70's and early 80's the effect was
  // mostly lost - but if you find a really old fortune file you might
  // encounter a few of these.

  else {
    for($x = 0; $x < strlen($s); $x ++) {
      if($dst[$x] == '1') {
        if($strike)
          $out .= $s[$x];
        else
          $out .= '<strike>'.$s[$x];
        $strike = true;
      }
      else {
        if($strike)
          $out .= '</strike>';
        $strike = false;
        $out .= $s[$x];
      }
    }
  }

  // Many of the underline sequences are also wrapped in asterisks,
  // which was yet another way of marking ASCII as 'bold'.
  // So if it's an underline sequence, and there are asterisks
  // on both ends, strip the asterisks as we've already emboldened the text.

  $out = preg_replace('/\*(<strong>[^<]*<\/strong>)\*/',"\\1",$out);

  // Finally, remove the backspace characters which we don't need anymore.

  return str_replace("\010","",$out);
}

$result1 = do_query($table,$length,$numlines,$adult,$cat,1,$lang,$pattern,$regex,$equal);

if(count($result1))
  echo $result1[0];

if($stats)
  do_stats($table,$length,$numlines,$adult,$cat,1,$lang,$pattern,$regex,$equal);


