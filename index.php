<?PHP	date_default_timezone_set('America/Los_Angeles');  // otherwise PHP warnings
	include "config.php";
	include "session.php";  // identical session code used for daytimer?
	//include "dungeon_nav.php";  /// MUST pair with ajax.js 
	//  FUTURE: change session from ID to ID_DG so that daytimer doesn't use same session?
	//  rename ajax.js?
?><HTML>
<HEAD><?PHP
if (!isset($_GET['debug'])) {
	//  iPhone hint
//	$meta_viewport = "width=600";
//	$meta_viewport = "width=600, minimal-ui, user-scalable=no";
//	$meta_viewport = "width=520, minimal-ui, user-scalable=no>";
//	$meta_viewport = "width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no";
//	$meta_viewport = "width=600, initial-scale=1.0, maximum-scale=1.0, user-scalable=no";
//	$meta_viewport = "width=520,          initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui";
        $meta_viewport = "width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui";
	echo "\n<meta name=\"viewport\" content=\"".$meta_viewport."\">";
	}  ?>

<TITLE>Dungeon</TITLE>
<STYLE TYPE="TEXT/CSS"><!--
BODY {
	font-family: helvetica
	}
.FRAME {
	color:#FFFFFF;
	}
A {
	color:#007F00;
	text-decoration: none;
	}
A:HOVER {
	color:#11ff00;
	text-decoration: underline;
	}
UL.ALT {
	margin-top: 0px;
	margin-bottom: 0px;
	padding0: 0px;
	}
--></STYLE>

<script type="text/JavaScript" src="ajax.js"></script> 
</HEAD>
<?PHP
	if (isset($isprod) && $isprod)
		echo "<body style=\"-webkit-text-size-adjust:none;\">";
	else
		echo "<body style=\"-webkit-text-size-adjust:none; background: #ffdfdf;\">";  ?>

<TABLE WIDTH=100% BORDER=1><TR>
<TD VALIGN=TOP id="today" id="head-today"><?php  printf("%s today", date('Y-m-d'));  ?></TD>
<TD VALIGN=TOP ALIGN=CENTER id="head-title"><B><?PHP
	if (isset($title))
		echo $title; 
	else
		echo "Test Instance";  ?></B></TD>
<TD ALIGN=RIGHT><div id="head">[ loading ... ]</div></td>
</TR></TABLE>

<?php 
// main navigation controls div
//  echo "<div id=\"calnav\" style=\"display: block; text-align: center;\">";  //  IE is fussy about this
//  echo "<table border=0\n  cellspacing=0 cellpadding=0 style=\"margin-left: auto; margin-right: auto;\";><tr>";
//  dungeon_render();
//  echo "</tr></table></div>\n";
echo "\n\n<!--  calnav  --><div id=\"calnav\" style=\"display: block; text-align: center;\">[ loading ... ]</div><!--  calnav  -->\n";

// main display div
echo "\n\n<!--  cal  --><div id=\"cal\" style=\"display: block;\">[ calendar ]</div><!--  cal  -->\n";

// lower navigation, controls
echo "\n\n<div id=\"dgnav2\" style=\"display: block; text-align: center;\">[ loading ... ]</div>\n";

// lower map display
echo "\n\n<div id=\"dgnav3\" style=\"display: block; text-align: center;\">[ map loading ... ]</div>\n";
?>

<p style="font-size: smaller; font-style: italic; margin: 0px; text-align: center;">this
site brought to you by <A HREF=http://zaptech.com/>zap technologies</A></p>

<SCRIPT LANGUAGE="JavaScript"><!-- Begin
	var headhq;
 	headhq = new class_hq('head', 0);
	headhq.url = head_display_file+'?ajax=0';
 	headhq.do_now();

	var navhq;
 	navhq = new class_hq('calnav', 0);
	navhq.url = nav_display_file+'?ajax=0';
 	navhq.do_now();

//	head_set('head');

	var calhq;
 	calhq = new class_hq('cal', function() {  newmap_toggle('map_bits');});  //  FUTURE why have both div cal wrapped around div calout?
	calhq.url = dungeon_display_file+'?ajax=0';
 	calhq.do_now();
 	cal_set('calout');  //  1st pass (above) provide the 'cal' div with 'calout' div

//	var nv2hq;
// 	navhq = new class_hq('dgnav2', 0);
//	navhq.url = nav_display_file+'?ajax=0&nav=2';
	//  JJJJ
 	// navhq.do_now();

	//  FUTURE: put 'map' in seperate div
	//  have main dungaon ... php place map in hideen div, then copy if to this div?
	//  http://stackoverflow.com/questions/921290/is-it-possible-to-clone-html-element-objects-in-javascript-jquery

	//  Audio setup - experiemental
	//  see nav_ajax.php for test buttons
	//  http://www.online-convert.com/
	//  http://stackoverflow.com/questions/1933969/sound-effects-in-javascript-html5
	//  http://www.wavsource.com/sfx/sfx.htm
	//  http://html5doctor.com/native-audio-in-the-browser/
	//  http://diveintohtml5.info/detect.html
//  if (new Audio()).canPlayType("audio/ogg; codecs=vorbis")
//if (createElement.canPlayType("audio/ogg; codecs=vorbis")
//    var snd = new Audio("gong.ogg"); // buffers automatically when created
//else
//    var snd = new Audio("gong.wav"); // buffers automatically when created
//snd.play();`
// End --></SCRIPT>

</BODY>
</HTML>
