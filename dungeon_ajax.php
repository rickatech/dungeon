<?PHP 
/*  This 'plug-in' generates output for calout div from AJAX call.

/*  Invalid session okay here since empty calendar will be output if no user is detected.
/*  However, still need to setup up session to handle logged in users.  */
//  include "session_check.php";
session_start();

//  service configuration parameters
$data_dir = "test";
$filename = $data_dir."/test.txt";

include "lib_map.php";

function checkevent($y, $m, $d, $row) {
	//  return -1 too soon
	//          0 ready
	//          1 not yet
	$t = strtotime($row);
	$ey = date('Y', $t);
	$em = date('m', $t);
	$ed = date('d', $t);
	if ($ey > $y)
		$r = 1;
	else if ($ey < $y)
		$r = -1;
	else if ($em > $m)
		$r = 1;
	else if ($em < $m)
		$r = -1;
	else if ($ed > $d)
		$r = 1;
	else if ($ed < $d)
		$r = -1;
	else
		$r = 0;
	return ($r);
	};

function previous_week($DY, $WD, $MO, $YR, $PM, $PY) {
	if ($DY - $WD - 7 >= 1) {
	        $SD = mktime(0, 0, 0, $MO, $DY - $WD - 7, $YR);
	        }
	else {
	        $ML = mktime(0, 0, 0, $PM, 1, $PY);
	        $SD = mktime(0, 0, 0, $PM, date("t", $ML) + $DY - $WD - 7, $PY);
	        }
    //  echo "\n  <BR>".date('Y-m-d', $SD)." previous week";
        return $SD;
	};

function event($row) {
	echo "\n<BR>. .";
	};

date_default_timezone_set('America/Los_Angeles');
if (isset($_GET['bill']))
	$bill = $_GET['bill'];
else
	$bill = 0;
if (isset($_GET['year']))
	$year = $_GET['year'];
else
	$year = 0;
if (isset($_GET['month']))
	$month = $_GET['month'];
else
	$month = 0;
if (isset($_GET['day']))
	$day = $_GET['day'];
else
	$day = 0;
if (isset($_GET['filter']))
	$filter = $_GET['filter'];
else
	$filter = 0;
if (isset($_GET['ajax']))
	/*  0 render full cal div, 1 render just the calout div, unset ajax disabled  */
	$ajax = $_GET['ajax'];
else {
	echo "[ ajax disabled ]";
	return;
	}

/*  display: none, block  visibility: hidden, visible  */
if ($ajax == 0) {
	echo "\n\n&lt;calout><div id=\"calout\">";
	echo "[ calout ]</div>&lt;/calout>";
	return;
	}

/*  For ajax request, database connection is not already in place.  Future: move settings to config file.  */
//mysql_connect("localhost", "daytimer", "cottageread") or die("mysql_connect() failed");
//mysql_select_db("daytimer") or die("mysql_select_db() failed");

/*  Future Optimization: narrow select to date range, and possible also specific UID values  */
//$query = "select * from appointments order by date, time";
//$result = mysql_query($query) or die(mysql_error());
//$colqty = mysql_num_fields($result);

echo "<p style=\"font-size: smaller;\">request 0: ".$_SERVER['REQUEST_URI']."</p>\n";

	$w[ 0][0] = "\    /";
	$w[ 0][1] = " |##| ";
	$w[ 0][2] = "/    \\";  /*  ! \"  */
	$w[ 1][0] = "     /";
	$w[ 1][1] = "####| ";
	$w[ 1][2] = "     \\";  /*  ! \"  */
	$w[ 2][0] = "\     ";
	$w[ 2][1] = " |####";
	$w[ 2][2] = "/     ";
	$w[ 3][0] = "\ __ /";
	$w[ 3][1] = " |__| ";
	$w[ 3][2] = "/    \\";  /*  ! \"  */
	$w[ 4][0] = "     &nbsp;";
	$w[ 4][1] = "######";
	$w[ 4][2] = "     &nbsp;";
	$w[ 5][0] = "     /";
	$w[ 5][1] = "####  ";
	$w[ 5][2] = "     \\";  /*  ! \"  */
	$w[ 6][0] = "\     ";
	$w[ 6][1] = "  ####";
	$w[ 6][2] = "/     ";
	$w[ 7][0] = "  __  ";
	$w[ 7][1] = "#|__|#";
	$w[ 7][2] = "     &nbsp;";
	$w[ 8][0] = "    &nbsp; ";
	$w[ 8][1] = "    &nbsp; ";
	$w[ 8][2] = "    &nbsp; ";
	$w[ 9][0] = "_     ";
	$w[ 9][1] = "_|####";
	$w[ 9][2] = "    &nbsp; ";
	$w[10][0] = "     _";
	$w[10][1] = "####|_";
	$w[10][2] = "     &nbsp;";
	$w[11][0] = "\ __  ";
	$w[11][1] = " |__|#";
	$w[11][2] = "/     ";
	$w[12][0] = "\    _";
	$w[12][1] = " |##|_";
	$w[12][2] = "/     ";
	$w[13][0] = "\    _";
	$w[13][1] = "  ##|_";
	$w[13][2] = "/     ";
	$w[14][0] = "\ ____";
	$w[14][1] = " |____";
	$w[14][2] = "/     ";
	$w[15][0] = "______";
	$w[15][1] = "______";
	$w[15][2] = "     &nbsp;";
	$w[16][0] = "____  ";
	$w[16][1] = "____|#";
	$w[16][2] = "     &nbsp;";
	$w[17][0] = "  ____";
	$w[17][1] = "#|____";
	$w[17][2] = "     &nbsp;";
	$w[18][0] = "_    _";
	$w[18][1] = "_|##|_";
	$w[18][2] = "     &nbsp;";
	$w[19][0] = "    &nbsp; ";
	$w[19][1] = "error!";
	$w[19][2] = "    &nbsp; ";
	$w[20][0] = "    &nbsp; ";
	$w[20][1] = "please";
	$w[20][2] = "login ";

	function near_far($f) {
		//  there may be a way to fix code near and far seperate
		//  then 'green screen' near over far over background
		$v = 8;
		if ($f ==  0) $v =  4;  /*       left center right  */ 
		if ($f ==  1) $v =  2;  /* far    10    20    40 */
		if ($f ==  2) $v =  8;  /* near    1     2     4 */
		if ($f ==  3) $v =  8;
		if ($f ==  4) $v =  5;
		if ($f ==  5) $v =  0;
		if ($f ==  6) $v =  8;
		if ($f ==  7) $v =  8;
		if ($f == 10) $v =  9;
		if ($f == 11) $v =  6;
		if ($f == 12) $v =  8;
		if ($f == 13) $v =  8; 
		if ($f == 14) $v =  8;
		if ($f == 15) $v = 13;
		if ($f == 16) $v =  8;
		if ($f == 17) $v =  8;
		if ($f == 20) $v =  7;
		if ($f == 21) $v = 11;
		if ($f == 30) $v = 16;
		if ($f == 35) $v =  3;
		if ($f == 40) $v =  2;
		if ($f == 50) $v = 18;
		if ($f == 51) $v =  1;
		if ($f == 52) $v =  8;
		if ($f == 53) $v =  3;
		if ($f == 54) $v =  8;
		if ($f == 55) $v =  3;
		if ($f == 56) $v =  8;
		if ($f == 57) $v =  8;
		if ($f == 60) $v = 17;
		if ($f == 67) $v =  8;
		if ($f == 70) $v = 15;
		if ($f == 77) $v =  8;
		return $v;
		}

	function render($v, $message = NULL) {
		/*  this may be client side javascript at some point?  */
		global $w;
		echo "<center><table style=\"margin: auto:\"><tr>\n<td style=\"border: solid 4px;\">\n";
		printf("<pre style=\"font-size: 72px; margin-bottom: 0px;\">%s\n%s\n%s</pre>",
		    $w[$v][0], $w[$v][1], $w[$v][2]);
		echo "</td>\n</tr></table>\n";
		if ($message)
			echo "\n<br>".$message."\n";
		echo "</center>";
		}

if (isset($_GET["field"])) {
	$f = $_GET["field"];
	$v = near_far($f);
		}
else if (isset($_GET["view"]))
	$v = $_GET["view"];
else
	$v = 4;

if (isset($_GET["x"]))
	$x = $_GET["x"];
else $x = 2;
if (isset($_GET["y"]))
	$y = $_GET["y"];
else $y = 2;

if (isset($_GET["newmap"])) {
	echo "\nNEW MAP</br>\n";
	$newfile = $data_dir."/new.txt";
	$m = gen_map($m);
	if (put_map($newfile, $m)) {
		echo "\n<br>write map successful\n";
		$m2 = get_map($newfile);
		print_map($m2);
		}
	else
		echo "\n<br>write map error\n";
	}
else { ///

if (!isset($_SESSION['username']))
	$v = 20;  // please login
else if ($m = get_map($filename)) {
	$f = 0;
	if ($m[$x - 1][$y - 1] == 1) $f = $f +  1;
	if ($m[$x    ][$y - 1] == 1) $f = $f +  2;
	if ($m[$x + 1][$y - 1] == 1) $f = $f +  4;
	if ($m[$x - 1][$y - 2] == 1) $f = $f + 10;
	if ($m[$x    ][$y - 2] == 1) $f = $f + 20;
	if ($m[$x + 1][$y - 2] == 1) $f = $f + 40;
	$v = near_far($f);
	$m[$x][$y] = '*';
	$msg = "view: ".$v.", field: ".$f." x, y = ".$x.", ".$y;
	}
else {
	$v = 19;
	$msg = "Could not open dungeon map file.";
	}
render($v, $msg);
if (isset($m)) {
	print_map($m);
	}
} ///
?>


