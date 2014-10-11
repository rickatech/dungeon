<?PHP 
/*  This 'plug-in' generates output for calout div from AJAX call.

/*  FUTURE
  - use case: user logged in and active, steps away from computer, back after an hour, clicks a command
    javascript timer to idle screen, reset session?  server side, invalid session after x minutes
    should refresh display, ignore command, indicate your next command will be ohonored if you don't step away again  */

/*  Invalid session okay here since login prompt if no user is detected  */
session_start();

//  service configuration parameters
$data_dir = "test";
$filename = $data_dir."/test.txt";
$homemap_prefix = 'user';
//  user00000001.txt, 'user' + uid of user + '.txt', player's home map
//  home.txt,                           new player home map 'template'

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
	echo "\n\n<!--  calout  --><div id=\"calout\">";
	echo "[ calout ]</div><!--  calout  -->";
	return;
	}

	//  lofi ASC viewpoint patterns,
	//  FUTURE z buffer matching array to allow easier composting of dynamic elements?
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
	$w[ 7][2] = "    &nbsp; ";
	$w[ 8][0] = "    &nbsp; ";
	$w[ 8][1] = "    &nbsp; ";
	$w[ 8][2] = "    &nbsp; ";
	$w[ 8]['z'] = 0;
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
	$w[21][0] = "_    /";
	$w[21][1] = "_|##  ";
	$w[21][2] = "     \\";
	$w[22][0] = "____ /";
	$w[22][1] = "____| ";
	$w[22][2] = "     \\";
	$w[23][0] = "____  ";
	$w[23][1] = "____|#";
	$w[23][2] = "     &nbsp;";
	$w[24][0] = "\ __  ";
	$w[24][1] = " |__|#";
	$w[24][2] = "/     ";
	$w[25][0] = "  __ /";
	$w[25][1] = "#|__| ";
	$w[25][2] = "     \\";
	$w[26][0] = "\    /";
	$w[26][1] = " |##  ";
	$w[26][2] = "/    \\";
	$w[27][0] = "\    /";
	$w[27][1] = "  ##| ";
	$w[27][2] = "/    \\";
	$w[28][0] = "\    /";
	$w[28][1] = "  ##  ";
	$w[28][2] = "/    \\";
	$w[29][0] = "_    /";
	$w[29][1] = "_|##| ";
	$w[29][2] = "     \\";
	$w[30][0] = "! * @ ";
	$w[30][1] = " BONK!";
	$w[30][2] = " ~ %  ";


	function near_far($f) {
		//  there may be a way to fix code near and far seperate
		//  then 'green screen' near over far over background
		$v = 8;
		if ($f ==  0) $v =  4;  /*       left center right  */ 
		if ($f ==  1) $v =  1;  /* far    40    20    10 */
		if ($f ==  2) $v =  8;  /* near    4     2     1 */
		if ($f ==  3) $v =  8;
		if ($f ==  4) $v =  2;
		if ($f ==  5) $v =  0;
		if ($f ==  7) $v =  8;
		if ($f == 10) $v = 10;
		if ($f == 11) $v =  5;
		if ($f == 12) $v =  8;
		if ($f == 13) $v =  8; 
		if ($f == 14) $v = 12;
		if ($f == 15) $v = 26;
		if ($f == 16) $v =  8;
		if ($f == 17) $v =  8;
		if ($f == 20) $v =  7;
		if ($f == 21) $v = 25;
		if ($f == 24) $v = 11;
		if ($f == 25) $v =  3;
		if ($f == 30) $v = 17;
		if ($f == 31) $v = 25;
		if ($f == 34) $v = 14;
		if ($f == 35) $v =  3;
		if ($f == 40) $v =  9;
		if ($f == 41) $v = 29;
		if ($f == 44) $v =  6;
		if ($f == 45) $v = 27;
		if ($f == 50) $v = 18;
		if ($f == 51) $v = 21;
		if ($f == 54) $v = 13;
		if ($f == 55) $v = 28;
		if ($f == 56) $v =  8;
		if ($f == 57) $v =  8;
		if ($f == 60) $v = 23;
		if ($f == 61) $v = 22;
		if ($f == 64) $v = 11;
		if ($f == 65) $v =  3;
		if ($f == 67) $v =  8;
		if ($f == 70) $v = 15;
		if ($f == 71) $v = 22;
		if ($f == 74) $v = 14;
		if ($f == 75) $v =  3;
		return $v;
		}

	function render($v, $message = NULL, $b = 0, $o = NULL, $oo = NULL) {
		/*  this may be client side javascript at some point?  */
		global $w;
		global $z;
		global $bonk;
		//  adjust border color to hint at presence of nearby walls
		$bs  = $b & 1 ? 'border-top: solid 4px;' :       'border-top: solid 4px; border-top-color: #9FFF9F;';
		$bs .= $b & 2 ? 'border-left: solid 4px;' :     'border-left: solid 4px; border-left-color: #9FFF9F;';
		$bs .= $b & 4 ? 'border-right: solid 4px;' :   'border-right: solid 4px; border-right-color: #9FFF9F;';
		$bs .= $b & 8 ? 'border-bottom: solid 4px;' : 'border-bottom: solid 4px; border-bottom-color: #9FFF9F;';
		$r0 = $w[$v][0];
		$r1 = $w[$v][1];
		$r2 = $w[$v][2];
		if (!$bonk) {
			//  show other player!!!
			if ($o)
				$r1[3] = $o;
			if ($oo) {
				$r1[2] = $oo; $r1[3] = $oo;
				$r2[2] = $oo; $r2[3] = $oo; }
			}
		echo "<center><table style=\"margin: auto:\"><tr>\n<td id=\"rentab\" style=\"".$bs."\">\n";
		printf("<pre style=\"font-size: 72px; margin-bottom: 0px;\">%s\n%s\n%s</pre>",
		    $r0, $r1, $r2);
		echo "</td>\n</tr></table>\n";
		if ($message)
			echo "\n".$message."\n";
		echo "</center>";
		}

	function stepwrap($v, $m, $s) {
		// torus 'wrap around' world baby!
		// v current value
		// m max, wrap around value
		// s step amount, assumed less than m
		if      ($v + $s < 0)        $r = $v + $s + $m;
		else if ($v + $s > ($m - 1)) $r = $v - $m + $s;
		else 	                     $r = $v + $s;
		return ($r);
		//  what if s > m?
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
else $x = 2 + $day;
if (isset($_GET["y"]))
	$y = $_GET["y"];
else $y = 2 + $month;

//  HEY, here's where we detect display no action refresh, or command + display refresh!!!
//  if no command ... skip command processing ... get map, return display
//  if command, get map, process command, WRITE map
//  if write okay, then display current map
//  else post error message, but also get updated map, display it

//  turns
//  when processing command, 1st make an array of active users in a map,
//  try to move too soon, warning to wait ... seconds for your turn
//  check player vs map tick
//    map will have last move tick / player.  
//    1 player  only, no turn delays, ticks advances as 1 player moves
//    2+ player, must wait for other player to move or wait 20 x [ n players - 1] seconds, 
//               ticks advance as each player moves (or forfiets their move (by timeout)

//  COMMANDS
//  enter/switch map
//  forward, slide left, slide right, backward, turn left, turn right, turn left + forward, turn right + forwad
//  push block, pull block (25% chance destroy block, spawns it nearby),
//  move fails if it squishes a player (but does 1hp dammage to themi, respans them if last hp)
//  player coma (idle, left game), if 0 hp don't, respawn when they login next time
//  move into a wall, 50% chance player bumped
//  move into another players position, both player bumped
//  need to deal with 'torus world' x/y wrap around
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

if (!isset($_SESSION['username']) || !isset($_SESSION['uid']))
	$v = 20;  // please login
//  open 'home' map + character info
//  open 'active' map
else if (0 && !($m_home = get_map($kkk = $homemap_prefix.sprintf('%08d.txt', $_SESSION['uid'])))) {
	$v = 19;  // error
	$msg = "Could not open home map file: ".$kkk;
	}
else if (!($m = get_map($filename))) {
	$v = 19;  // error
	$msg = "Could not open dungeon map file.";
	}
else if (!isset($m['tick'])) {
	$v = 19;  // error
	$msg = "Dungeon has no tick.";
	}
else if (!isset($m['size'])) {
	$v = 19;  // error
	$msg = "Dungeon has no size.";
	}
else if (!isset($m['user'][$_SESSION['uid']]) ||
         !isset($m['user'][$_SESSION['uid']]['handle']) ||
         !isset($m['user'][$_SESSION['uid']]['x']) ||
         !isset($m['user'][$_SESSION['uid']]['y']) ||
         !isset($m['user'][$_SESSION['uid']]['yaw'])) {
	//  FUTURE: set local variable = $_SESSION['uid']?
	$v = 19;  // error
	$msg = "No active dungeon for ".$_SESSION['username'];
	}
else {
	$msg2 = NULL;
	if (isset($_GET['cmd'])) {
		$cmd = $_GET["cmd"];
		if ($_SESSION['uid'] == 1) // admin/rickatech check
			$msg2 = 'cmd: '.$_GET['cmd'];
		}
	else
		$cmd = 'refresh';

	$put = 0;
	$msg3 = NULL;
	//  FUTURE: check ticks, is it user's turn yet?
	$nyaw = $myaw = $m['user'][$_SESSION['uid']]['yaw'];
	$nx   = $mx   = $m['user'][$_SESSION['uid']]['x'];
	$ny   = $my   = $m['user'][$_SESSION['uid']]['y'];
	if      ($cmd == 'stepforw') {
		//  strobe lock file?
		if ($myaw < 90)        //  0
			$ny = stepwrap($my, $m['size'][2], -1);
		else if ($myaw < 180) //  90
			$nx = stepwrap($mx, $m['size'][1],  1);
		else if ($myaw < 270) // 180
			$ny = stepwrap($my, $m['size'][2],  1);
		else                // 270
			$nx = stepwrap($mx, $m['size'][1], -1);
		$put = 1;
		}
	else if ($cmd == 'stepback') {
		//  strobe lock file?
//		$m['user'][$_SESSION['uid']]['y'] =
//		  stepwrap($m['user'][$_SESSION['uid']]['y'], $m['size'][2],  1);
		if ($myaw < 90)        //  0
			$ny = stepwrap($my, $m['size'][2],  1);
		else if ($myaw < 180) //  90
			$nx = stepwrap($mx, $m['size'][1], -1);
		else if ($myaw < 270) // 180
			$ny = stepwrap($my, $m['size'][2], -1);
		else                // 270
			$nx = stepwrap($mx, $m['size'][1],  1);
		$put = 1;
		}
	else if ($cmd == 'stepleft') {
		//  strobe lock file?
//		$m['user'][$_SESSION['uid']]['x'] =
//		  stepwrap($m['user'][$_SESSION['uid']]['x'], $m['size'][1], -1);
		if ($myaw < 90)        //  0
			$nx = stepwrap($mx, $m['size'][1], -1);
		else if ($myaw < 180) //  90
			$ny = stepwrap($my, $m['size'][2], -1);
		else if ($myaw < 270) // 180
			$nx = stepwrap($mx, $m['size'][1],  1);
		else                // 270
			$ny = stepwrap($my, $m['size'][2],  1);
		$put = 1;
		}
	else if ($cmd == 'steprght') {
		//  strobe lock file?
//		$m['user'][$_SESSION['uid']]['x'] =
//		  stepwrap($m['user'][$_SESSION['uid']]['x'], $m['size'][1],  1);
		if ($myaw < 90)        //  0
			$nx = stepwrap($mx, $m['size'][1],  1);
		else if ($myaw < 180) //  90
			$ny = stepwrap($my, $m['size'][2],  1);
		else if ($myaw < 270) // 180
			$nx = stepwrap($mx, $m['size'][1], -1);
		else                // 270
			$ny = stepwrap($my, $m['size'][2], -1);
		$put = 1;
		}
	else if ($cmd == 'turnleft') {
		//  strobe lock file?
		$nyaw = stepwrap($myaw, 360, -90);
//		  stepwrap($m['user'][$_SESSION['uid']]['yaw'], 360, -90);
		$put = 1;
		}
	else if ($cmd == 'turnrght') {
		//  strobe lock file?
		$nyaw = stepwrap($myaw, 360,  90);
//		  stepwrap($m['user'][$_SESSION['uid']]['yaw'], 360,  90);
		$put = 1;
		}
	else if ($cmd == 'passwait') { //  FUTURE
		//  strobe lock file?
		//  FUTURE: allow 'no nothing' command, advance map tick
		$put = 1;
		}
	//  player command?
	$bonk = 0;
	if ($put == 1) {
		$m['tick'][1]++;
		//  collide into a block or FUTURE other dynamic element
		foreach ($m['user'] as $ak => $av) {
			if ($ak != $_SESSION['uid']) {
				if ($av['x'] == $nx && $av['y'] == $ny)
					$bonk = 1;
				}
			}
		if ($bonk == 0 && $m[$nx][$ny] == 0) {
			$m['user'][$_SESSION['uid']]['yaw'] = $nyaw;
			$m['user'][$_SESSION['uid']]['x']   = $nx;
			$m['user'][$_SESSION['uid']]['y']   = $ny;
			}
		else {
			//  $msg2 = "BONK! ".$msg2;
			$bonk = 1;
			$v = 30;
			}
		if (put_map($filename, $m))
			$msg3 = 'write map successful';
		else {
			//  FUTURE, revert session updating x,y,yaw?
			$msg3 = 'write map error';
			}
		}

	//  setting session here is very important
	//  when user clicks move/attack/... button
	//  it will check map tick vs session before making request
	$_SESSION['tick'] = $m['tick'][1];
	$x   = $m['user'][$_SESSION['uid']]['x'];
	$y   = $m['user'][$_SESSION['uid']]['y'];
	$yaw = $m['user'][$_SESSION['uid']]['yaw'];

	/*  get_map_players  */
	//  [ new code goes here ]
	//  get tick, read each active player x, y - from map or map_extra file?

	//  wrap around 'torus' world
	//  yaw   0: x-1,y-2 | x,y-2 | x+1,y-2
	//           x-1,y-1 | x,y-1 | x+1,y-1
	//  yaw  90: x+2,y-1 | x+2,y | x+2,y+1
	//           x+1,y-1 | x+1,y | x+1,y+1
	//  yaw 180: x+1,y+2 | x,y+2 | x-1,y+2
	//           x+1,y+1 | x,y+1 | x-1,y+1
	//  yaw 270: x-2,y+1 | x-2,y | x-2,y-1
	//           x-1,y+1 | x-1,y | x-1,y-1
	$nearwall = 0;
	if ($yaw < 90) {  //   0
		$nearwall |= $m[$x                             ][stepwrap($y, $m['size'][2], -1)] ? 1 : 0;
		$nearwall |= $m[stepwrap($x, $m['size'][1], -1)][$y                             ] ? 2 : 0;
		$nearwall |= $m[stepwrap($x, $m['size'][1],  1)][$y                             ] ? 4 : 0;
		$nearwall |= $m[$x                             ][stepwrap($y, $m['size'][2],  1)] ? 8 : 0;
		$view['a'][1] = $m[stepwrap($x, $m['size'][1], -1)][stepwrap($y, $m['size'][2], -1)];
		$view['b'][1] = $m[$x                             ][stepwrap($y, $m['size'][2], -1)];
		$view['c'][1] = $m[stepwrap($x, $m['size'][1],  1)][stepwrap($y, $m['size'][2], -1)];
		$view['a'][2] = $m[stepwrap($x, $m['size'][1], -1)][stepwrap($y, $m['size'][2], -2)];
		$view['b'][2] = $m[$x                             ][stepwrap($y, $m['size'][2], -2)];
		$view['c'][2] = $m[stepwrap($x, $m['size'][1],  1)][stepwrap($y, $m['size'][2], -2)];
		}
	else if ($yaw < 180) {  // 90
		$nearwall |= $m[stepwrap($x, $m['size'][1],  1)][$y                             ] ? 1 : 0;
		$nearwall |= $m[$x                             ][stepwrap($y, $m['size'][2], -1)] ? 2 : 0;
		$nearwall |= $m[$x                             ][stepwrap($y, $m['size'][2],  1)] ? 4 : 0;
		$nearwall |= $m[stepwrap($x, $m['size'][1], -1)][$y                             ] ? 8 : 0;
		$view['a'][1] = $m[stepwrap($x, $m['size'][1],  1)][stepwrap($y, $m['size'][2], -1)];
		$view['b'][1] = $m[stepwrap($x, $m['size'][1],  1)][$y                             ];
		$view['c'][1] = $m[stepwrap($x, $m['size'][1],  1)][stepwrap($y, $m['size'][2],  1)];
		$view['a'][2] = $m[stepwrap($x, $m['size'][1],  2)][stepwrap($y, $m['size'][2], -1)];
		$view['b'][2] = $m[stepwrap($x, $m['size'][1],  2)][$y                             ];
		$view['c'][2] = $m[stepwrap($x, $m['size'][1],  2)][stepwrap($y, $m['size'][2],  1)];
		}
	else if ($yaw < 270) {  //180 
		$nearwall |= $m[$x                             ][stepwrap($y, $m['size'][2],  1)] ? 1 : 0;
		$nearwall |= $m[stepwrap($x, $m['size'][1],  1)][$y                             ] ? 2 : 0;
		$nearwall |= $m[stepwrap($x, $m['size'][1], -1)][$y                             ] ? 4 : 0;
		$nearwall |= $m[$x                             ][stepwrap($y, $m['size'][2], -1)] ? 8 : 0;
		$view['a'][1] = $m[stepwrap($x, $m['size'][1],  1)][stepwrap($y, $m['size'][2],  1)];
		$view['b'][1] = $m[$x                             ][stepwrap($y, $m['size'][2],  1)];
		$view['c'][1] = $m[stepwrap($x, $m['size'][1], -1)][stepwrap($y, $m['size'][2],  1)];
		$view['a'][2] = $m[stepwrap($x, $m['size'][1],  1)][stepwrap($y, $m['size'][2],  2)];
		$view['b'][2] = $m[$x                             ][stepwrap($y, $m['size'][2],  2)];
		$view['c'][2] = $m[stepwrap($x, $m['size'][1], -1)][stepwrap($y, $m['size'][2],  2)];
		}
	else {                  //270
		$nearwall |= $m[stepwrap($x, $m['size'][1], -1)][$y                             ] ? 1 : 0;
		$nearwall |= $m[$x                             ][stepwrap($y, $m['size'][2],  1)] ? 2 : 0;
		$nearwall |= $m[$x                             ][stepwrap($y, $m['size'][2], -1)] ? 4 : 0;
		$nearwall |= $m[stepwrap($x, $m['size'][1],  1)][$y                             ] ? 8 : 0;
		$view['a'][1] = $m[stepwrap($x, $m['size'][1], -1)][stepwrap($y, $m['size'][2],  1)];
		$view['b'][1] = $m[stepwrap($x, $m['size'][1], -1)][$y                             ];
		$view['c'][1] = $m[stepwrap($x, $m['size'][1], -1)][stepwrap($y, $m['size'][2], -1)];
		$view['a'][2] = $m[stepwrap($x, $m['size'][1], -2)][stepwrap($y, $m['size'][2],  1)];
		$view['b'][2] = $m[stepwrap($x, $m['size'][1], -2)][$y                             ];
		$view['c'][2] = $m[stepwrap($x, $m['size'][1], -2)][stepwrap($y, $m['size'][2], -1)];
		}
	$xl = ($x < 1) ?                 $m['size'][1] - 1 :      $x - 1;
	$xr = ($x > $m['size'][1] - 2) ? 0 :                      $x + 1;
	$y2 = ($y < 2) ?                 $m['size'][2] - 2 + $y : $y - 2;
	$y1 = ($y < 1) ?                 $m['size'][2] - 1      : $y - 1;
//	if ($y == 0) $y1 = $m['size'][2] - 1; else $y1 = $y - 1;
	//  near walls 
	$f = 0;
	//  crude z-bufffer
	$z = array(9, 9, 9);
	if ($view['a'][2] == 1) { $f = $f + 40;  $z[0] = 2; }
	if ($view['b'][2] == 1) { $f = $f + 20;  $z[1] = 2; }
	if ($view['c'][2] == 1) { $f = $f + 10;  $z[2] = 2; }
	if ($view['a'][1] == 1) { $f = $f +  4;  $z[0] = 1; }
	if ($view['b'][1] == 1) { $f = $f +  2;  $z[1] = 1; }
	if ($view['c'][1] == 1) { $f = $f +  1;  $z[2] = 1; }

	if ($bonk != 1)
		$v = near_far($f);
	//  $msg = "view: ".$v.", field: ".$f." tick: ".$m['tick']." x, y = ".$x.", ".$y;
	$msg = "view: ".$v.", field: ".$f." tick: ".$_SESSION['tick']." x, y = ".$x.", ".$y." ".$yaw;
	if ($msg3)
		$msg .= "\n<br>".$msg3;
	if ($msg2)
		$msg .= "\n<br>".$msg2;
	if ($_SESSION['uid'] == 1) // admin/rickatech check
		$msg .= "\n<br><span style=\"font-size: smaller; color: #ff0000;\">".$_SERVER['REQUEST_URI']."</span>";
	/*  modifying map for display purposes,
        /*  NEVER SAVE THIS?  */
	//  $m[$x][$y] = '*';
	$o = NULL;
	$oo = NULL;
	foreach ($m['user'] as $ak => $av) {
		if ($av['x'] == $x && $av['y'] == $y)
			$m[$x][$y] = '*';
		else {
			$m[$av['x']][$av['y']] = '+';
			if ($yaw < 90) {
				if ($av['x'] == $x && $av['y'] == stepwrap($y, $m['size'][2], -2))	
					$o = $av['handle'][0];
				if ($av['x'] == $x && $av['y'] == stepwrap($y, $m['size'][2], -1))	
					$oo = $av['handle'][0];
				}
			else if ($yaw < 180) {
				if ($av['y'] == $y && $av['x'] == stepwrap($x, $m['size'][1],  2))	
					$o = $av['handle'][0];
				if ($av['y'] == $y && $av['x'] == stepwrap($x, $m['size'][1],  1))	
					$oo = $av['handle'][0];
				}
			else if ($yaw < 270) {
				if ($av['x'] == $x && $av['y'] == stepwrap($y, $m['size'][2],  2))	
					$o = $av['handle'][0];
				if ($av['x'] == $x && $av['y'] == stepwrap($y, $m['size'][2],  1))	
					$oo = $av['handle'][0];
				}
			else {
				if ($av['y'] == $y && $av['x'] == stepwrap($x, $m['size'][1], -2))	
					$o = $av['handle'][0];
				if ($av['y'] == $y && $av['x'] == stepwrap($x, $m['size'][1], -1))	
					$oo = $av['handle'][0];
				}
			}
		}
	}

if (isset($msg))
render($v, $msg, $nearwall, $o, $oo);
else
render($v);

if (isset($m)) {
	if ($_SESSION['uid'] == 1) // admin/rickatech check
		print_map($m);
	}
} ///
?>
