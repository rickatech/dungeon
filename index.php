<?PHP	date_default_timezone_set('America/Los_Angeles');  // otherwise PHP warnings
	include "session.php";  // identical session code used for daytimer?
	//include "dungeon_nav.php";  /// MUST pair with ajax.js 
	//  FUTURE: change session from ID to ID_DG so that daytimer doesn't use same session?
	//  rename ajax.js?
?><HTML>
<HEAD>
<meta name = "viewport" content = "width = 600"><!--  iPhone hint  -->
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

<body style="-webkit-text-size-adjust:none">
<TABLE WIDTH=100% BORDER=1><TR>
<TD VALIGN=TOP id="today" id="head-today"><?php  printf("%s today", date('Y-m-d'));  ?></TD>
<TD VALIGN=TOP ALIGN=CENTER id="head-title"><B>Dungeon</B></TD>
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
?>

<p style="font-size: smaller; font-style: italic; margin: 0px; text-align: center;">this
site brought to you by <A HREF=http://zaptech.com/>zap technologies</A></p>

<SCRIPT LANGUAGE="JavaScript"><!-- Begin
	var headhq;
 	headhq = new class_hq('head');
	headhq.url = head_display_file+'?ajax=0';
 	headhq.do_now();

	var navhq;
 	navhq = new class_hq('calnav');
	navhq.url = nav_display_file+'?ajax=0';
 	navhq.do_now();

 	head_set('head');

	var calhq;
 	calhq = new class_hq('cal');
	calhq.url = dungeon_display_file+'?ajax=0';
 	calhq.do_now();
 	cal_set('calout');
// End --></SCRIPT>

</BODY>
</HTML>

