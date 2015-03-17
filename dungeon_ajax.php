<?PHP 
/*  This 'plug-in' generates output for calout div from AJAX call.

/*  FUTURE
  - use case: user logged in and active, steps away from computer, back after an hour, clicks a command
    javascript timer to idle screen, reset session?  server side, invalid session after x minutes
    should refresh display, ignore command, indicate your next command will be ohonored if you don't step away again
  - use use: [ need more, build test plan ]
  - code coverage: lots of obsolete code, how to enable source code report that show what code is still active  */

/*  Invalid session okay here since login prompt if no user is detected  */
include "config.php";
session_start();

//  service configuration parameters
$data_dir = "data";
$dungeons = array('dungeon');
$homemap_prefix = 'user';
$maxhit = 3;
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
	$w[31][0] = "welcom";
	$w[31][1] = "e to d";
	$w[31][2] = "ungeon";
	$w[32][0] = "refres";
	$w[32][1] = "h to p";
	$w[32][2] = "roceed";


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

	function render($v, $message = NULL, $b = 0, $o = NULL, $oo = NULL, $or = 0) {
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
		//  change background based on player orientation, FUTURE: 3D background map
		if ($or < 90)
			$bg_tc = '#ffffff';  //  'south', bright
		else if ($or < 180)
			$bg_tc = '#ffefff';  //  'west', purple
		else if ($or < 270)
			$bg_tc = '#dfdfff';  //  'north', bluish/periwinkle/cyan
		else
			$bg_tc = '#efffff';  //  'east', green/mint
		echo "<center><table style=\"margin: auto;  background: ".$bg_tc."\"><tr>\n<td id=\"rentab\" style=\"".$bs."\">\n";
		//  echo "<center><table style=\"margin: auto;  background: #ffffff;\"><tr>\n<td id=\"rentab\" style=\"".$bs."\">\n";
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


//   1  home map loaded, existing
//   4  home map loaded, new default
const FLAG_LOGIN_NO =  8;  //  login required
//  16  new user, no home, welcome
const FLAG_PLAY_OK =   2;  //  play map loaded, existing
const FLAG_PLAY_NEW = 32;  //  play map loaded, new
const FLAG_KICKED =   64;  //  player was kicked from map
//  ??  generate new map, FUTURE
$map_bits = 0;

if (!isset($_SESSION['username_dg']) || !isset($_SESSION['uid_dg'])) {
	$map_bits |= FLAG_LOGIN_NO;
	}
else {  //****

$msg2 = NULL;
$put = 0;

if (isset($_GET['cmd'])) {
	$cmd = $_GET["cmd"];
	if ($_SESSION['uid_dg'] == 1) // admin/rickatech check
		$msg2 = 'cmd: '.$_GET['cmd'];
	}
else
	$cmd = 'refresh';

//  attempt to open existing home map
//  if file exist but can't open or invalid present error
//  if no file exists, then offer prompt to create new home map
$file_home = $data_dir.'/'.$homemap_prefix.sprintf('%08d.txt', $_SESSION['uid_dg']);
if ($m_home = get_map($kkk = $file_home)) {
	//  home file was found, read
	$map_bits |= 1;
	//  THIS IS IT, mark home map is in play
	$m = &$m_home;
	}
else {
	if ($cmd != 'newmap') {
		//  check tick, size, ... make part of get_map?
		//  $msg = "Could not open home map file: ".$kkk;
//		$msg = "Welcome ";
		$map_bits |= 16;
		}
	else {
		//  prompt to create new player home map
	   	if ($m_home = get_map($kkk = $data_dir.'/'.'home.txt')) {
			$map_bits |= 4;
			//  THIS IS IT, mark home map is in play
			$m = &$m_home;
         		$m['user'][$_SESSION['uid_dg']]['handle'] = $_SESSION['username_dg'];
         		$m['user'][$_SESSION['uid_dg']]['x'] =      $m['user'][0]['x'];
         		$m['user'][$_SESSION['uid_dg']]['y'] =      $m['user'][0]['y'];
         		$m['user'][$_SESSION['uid_dg']]['yaw'] =    $m['user'][0]['yaw'];
			$m['user'][$_SESSION['uid_dg']]['hit'] =  1;  //  FUTURE: $maxhit
         		unset($m['user'][0]);
			//  echo "<pre>"; print_r($m); echo "</pre>";
			$put = 1;
			}
		else
			$msg = "Could not open new home map file: ".$kkk;
		}
	}
if ($map_bits & 5) {
	//  got here because, existing home map or new home map loaded
	if (isset($m_home['away']) && isset($m_home['away'][3])) {  //  8888
		//  check for play map (non-home) map active
		//  determine name of map in play (if any map in play)
		$file_dungeon = $data_dir.'/'.$m_home['away'][3].'.txt';
		if ($m_play = get_map($file_dungeon))
			$map_bits |= FLAG_PLAY_OK;
		else {
			//  prompt to create new player home map
		   	if ($m_play = get_map($kkk = $data_dir.'/'.'dungeon.txt'))
				$map_bits |= FLAG_PLAY_NEW;
			else
				$msg = "Could not open play map file: ".$kkk;
			}
		if ($map_bits & (FLAG_PLAY_OK | FLAG_PLAY_NEW)) {
			//  got here because, existing away map or or new away map loaded
			//  THIS IS IT, mark dungeon map is in play
			$m = &$m_play;
			if (!isset($m_play['user'][$_SESSION['uid_dg']])) {
				if (isset($m['left'][$_SESSION['uid_dg']]['hit'])) {
					if ($m['left'][$_SESSION['uid_dg']]['hit'] < 1) {
						$msg2 .= "YOU HAVE BEEN KICKED HOME!";
						$cmd = "giveup";  //  OVERRIDE USER COMMAND
						$map_bits |= FLAG_KICKED;
						unset($m['user'][$_SESSION['uid_dg']]);  //  ???
						}
					}
				if (!($map_bits & FLAG_KICKED)) {
	       	  			$m['user'][$_SESSION['uid_dg']]['handle'] = $_SESSION['username_dg'];
					if (isset($m['left'][$_SESSION['uid_dg']])) {
						//  PLACE USER IN MAP AT LAST LOCATION, FUTURE, collision?
	       					$m['user'][$_SESSION['uid_dg']]['x'] =      $m['left'][$_SESSION['uid_dg']]['x'];
	      			 		$m['user'][$_SESSION['uid_dg']]['y'] =      $m['left'][$_SESSION['uid_dg']]['y'];
	       					$m['user'][$_SESSION['uid_dg']]['yaw'] =    $m['left'][$_SESSION['uid_dg']]['yaw'];
			       			$m['user'][$_SESSION['uid_dg']]['hit'] =    $m_home['left'][$_SESSION['uid_dg']]['hit'];
						//  REMOVE LEFT USER FROM AWAY MAP
						unset($m['left'][$_SESSION['uid_dg']]);
						}
					else {
						//  FUTURE: new location hints, algorytm?
			       			$m['user'][$_SESSION['uid_dg']]['x'] = $m['size'][1] >> 1;  //   7
			       			$m['user'][$_SESSION['uid_dg']]['y'] = $m['size'][2] >> 1;  //  7;
			       			$m['user'][$_SESSION['uid_dg']]['yaw'] =    0;
			       			$m['user'][$_SESSION['uid_dg']]['hit'] =    $m_home['left'][$_SESSION['uid_dg']]['hit'];
						}
					$put = 1;
					}
				}
			}
	//  !!! check play map tick, size, ... make part of get_map?
	//  if give up command
	//    - dungeon map, unset user location, set put = 1 (maybe place NPC?)
	//    - home map,    unset away, unset last, set player location, set home put = 1
		}  //  8888
	else if (isset($m['left'][$_SESSION['uid_dg']])) {
		//  enter here if user returning from away map, i.e. recently gave up or kicked
		//  PLACE USER IN MAP AT LAST LOCATION, FUTURE, collision?
       		$hit_heal = $m['left'][$_SESSION['uid_dg']]['hit'];
       		if ($hit_heal < $maxhit)
			$hit_heal++;
       	  	$m['user'][$_SESSION['uid_dg']]['handle'] = $m['left'][$_SESSION['uid_dg']]['handle'];
       		$m['user'][$_SESSION['uid_dg']]['x'] =      $m['left'][$_SESSION['uid_dg']]['x'];
       		$m['user'][$_SESSION['uid_dg']]['y'] =      $m['left'][$_SESSION['uid_dg']]['y'];
       		$m['user'][$_SESSION['uid_dg']]['yaw'] =    $m['left'][$_SESSION['uid_dg']]['yaw'];
       		$m['user'][$_SESSION['uid_dg']]['hit'] =    $hit_heal;
		//  REMOVE LEFT USER FROM AWAY MAP
		//  unset($m['left']);
		unset($m['left'][$_SESSION['uid_dg']]);
		$put = 1;
		}
	}

}  //****

//  open 'home' map + character info
//  open 'active' map
//else if (1 && !($m_home = get_map($kkk = $homemap_prefix.sprintf('%08d.txt', $_SESSION['uid_dg'])))) {
if ($map_bits & 8)
	$v = 20;  // login please
else if ($map_bits & 16)
	$v = 31;  // welcome
else if (($map_bits & 5) == 0)
	$v = 19;  // error, couldn't load home map
else if (isset($m_home['away']) && ($map_bits & (FLAG_PLAY_OK | FLAG_PLAY_NEW)) == 0)
	$v = 19;  // error, couldn't load away map 
else if (!isset($m['tick'])) {
	$v = 19;  // error
	$msg = "Dungeon has no tick. ".$map_bits;
	}
else if (!isset($m['size'])) {
	$v = 19;  // error
	$msg = "Dungeon has no size.";
	}
//  ZZZZ
else if (!($map_bits & FLAG_KICKED) && (
	 !isset($m['user'][$_SESSION['uid_dg']]) ||
         !isset($m['user'][$_SESSION['uid_dg']]['handle']) ||
         !isset($m['user'][$_SESSION['uid_dg']]['x']) ||
         !isset($m['user'][$_SESSION['uid_dg']]['y']) ||
         !isset($m['user'][$_SESSION['uid_dg']]['yaw']))
	 ) {
	//  FUTURE: set local variable = $_SESSION['uid_dg']?
	$v = 19;  // error
	$msg = "No active dungeon for ".$_SESSION['username_dg'].", ".$map_bits;
	}
else {
	$put_home_return = 0;
	$bonk_1 = 0;
	$msg3 = NULL;
	//  FUTURE: check ticks, is it user's turn yet?
	$nyaw = $myaw = $m['user'][$_SESSION['uid_dg']]['yaw'];
	$nx   = $mx   = $m['user'][$_SESSION['uid_dg']]['x'];
	$ny   = $my   = $m['user'][$_SESSION['uid_dg']]['y'];

	//  opponent, check if opponent is targeted AND action command
	//  load up opponent home map

	//  player commands, after maps loaded and validated
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
		$put = 1;
		}
	else if ($cmd == 'turnrght') {
		//  strobe lock file?
		$nyaw = stepwrap($myaw, 360,  90);
		$put = 1;
		}
	else if ($cmd == 'passwait') { //  FUTURE
		//  strobe lock file?
		//  FUTURE: allow 'do nothing' command, advance map tick
		$put = 1;
		}
	else if ($cmd == 'dungeon') { //  FUTURE
		//  strobe lock file?
		//  PLACE USER AWAY FROM HOME MAP, CITE AWAY MAP
		$m_home['away'] = array('away', $_SESSION['uid_dg'], $_SESSION['username_dg'], $dungeons[0]);
		//  left, location user was last active on home map
		//  SSS
		//  FUTURE: left needs to be like user, allow mulitple lefts/map
		$m_home['left'][$_SESSION['uid_dg']] = array(
		  'handle' => $_SESSION['username_dg'],
		  'x'      => $m['user'][$_SESSION['uid_dg']]['x'],
		  'y'      => $m['user'][$_SESSION['uid_dg']]['y'],
		  'yaw'    => $m['user'][$_SESSION['uid_dg']]['yaw'],
		  'hit'    => $m['user'][$_SESSION['uid_dg']]['hit']);
		$bonk_1 = 1;  //  OVERRIDE VIEW, user is switching maps
		//  echo "0 <pre>"; print_r($m['user']); echo "</pre>";
		//  REMOVE USER FROM HOME MAP
		unset($m['user'][$_SESSION['uid_dg']]);
		$msg3 = "enter dungeon";
		$put = 1;
		//  echo "1 <pre>"; print_r($m['user']); echo "</pre>";
		}
	else if ($cmd == 'giveup') { //  FUTURE
		//  in some cases user command will be overrided with this (e.g. kicked), see above
		//  strobe lock file?
		$bonk_1 = 1;
		$hit_save = $m['user'][$_SESSION['uid_dg']]['hit'];
		if ($map_bits & FLAG_KICKED) {
			unset($m['left'][$_SESSION['uid_dg']]);
			$msg3 = "kicked";
			}
		else {
			//  away, user is no longer active in away map, return user to home map
			$m['left'][$_SESSION['uid_dg']] = array(
			  'handle' => $_SESSION['username_dg'],
			  'x'      => $m['user'][$_SESSION['uid_dg']]['x'],
			  'y'      => $m['user'][$_SESSION['uid_dg']]['y'],
			  'yaw'    => $m['user'][$_SESSION['uid_dg']]['yaw'],
			  'hit'    => $hit_save);
			$msg3 = "give up";
			}
		//  REMOVE USER FROM AWAY MAP
		unset($m['user'][$_SESSION['uid_dg']]);
		$put = 1;
		//  REMOVE USER AWAY FROM HOME MAP
		unset($m_home['away']);
		$m_home['left'][$_SESSION['uid_dg']]['hit'] = $hit_save; 
		$put_home_return = 1;
		}
	else if ($cmd == 'newmap') {
		//  skip turn as though 'refresh' cmd
		$newfile = $data_dir.'/'.$homemap_prefix.sprintf('%08d.txt', $_SESSION['uid_dg']);
		if (put_map($newfile, $m_new)) {
			unset($m_new);
			if ($m_new = get_map($newfile))
				$msg3 = "write new map successful";
			else
				$msg3 = "read new map error";
			}
		else
			$msg3 = "write new map error";
		}
	else if ($cmd == 'doaction') {
		//  FUTURE: enable logging for all actions
		//          [timedate stamp][map file name][tick][uid][action][notes]
		//  else if (isset($_POST['username_dg']) && isset($_POST['password']) && (!isset($_SESSION['username_dg']))) {
		$trginf = explode(',', $_GET['han']);
		$msg3 = "do action: ".$_GET['act'].", ".$trginf[0];
		if ($trginf[0] > 0)
			$trg_id = $trginf[0];
		else
			$trg_id = 0;
		$trgact = $_GET['act'];
		//  we should already have play map loaded, which means all active players and NPC's too
		//    - confirm the target is present in play map
		//    - check target range
		//    - if tag, increment target tag count:
		//      next tick for that characer they should get processed as giveup with appropriate messaging
		}

	$bonk = 0;
	//  Dynamic objects
	//  Check if collide into another user or FUTURE other dynamic element
	//  TTTT, prepare list of available 'targets'
	foreach ($m['user'] as $ak => $av) {
		if ($ak != $_SESSION['uid_dg']) {
			//  FYI, if collide/bonk happens, unlikely a non-move is action has occurred
			if ($put == 1) {
				if ($av['x'] == $nx && $av['y'] == $ny)
					$bonk = 1;
				}
			}
		}
	$rx = $mx;  $ry = $my;
	//  Static objects
	//  Check if collide into environment
	if ($bonk == 0 && $put == 1 && $bonk_1 == 0) {
		//  if not already collided, and put request, and not proactively leaving map
		if ($m[$nx][$ny] == 0) {
			$m['user'][$_SESSION['uid_dg']]['yaw'] = $nyaw;
			$m['user'][$_SESSION['uid_dg']]['x']   = $rx = $nx;
			$m['user'][$_SESSION['uid_dg']]['y']   = $ry = $ny;
			}
		else
			$bonk = 1;
		}
	if ($bonk > 0)
		$v = 30;  //  $msg2 = "BONK! ".$msg2;
	//  Calculate targets ranges
	$trgt_qty = 0;
	foreach ($m['user'] as $ak => $av) {
		if ($ak != $_SESSION['uid_dg']) {
			$rv = abs($av['x'] - $rx) + abs($av['y'] - $ry);  //  FUTURE: fast Pythagorean Theorem
			if (isset($trgact) && $trgact == 'tag' && $ak == $trg_id) {
				if ($debug_mask & DEBUG_USR) {
				echo "trg_id: ".$trg_id." <pre style=\"font-size: smaller;\">"; print_r($m['user']); echo "</pre>"; }
				if ($ak < 1) {
					$msg2 .= " WEIRD :-/ ";
					}
				else if ($rv < 2) {  //  tag range is 1
					//  ZZZZ
					$put = 1;
					if (!isset($m['user'][$trg_id]['hit'])) {
						$m['user'][$trg_id]['hit'] = 0;
						}
					if ($m['user'][$trg_id]['hit'] > 0) {
						$m['user'][$trg_id]['hit']--;
						}
					if ($m['user'][$trg_id]['hit'] < 1) {
						$msg2 .= " KNOCK OUT HIT! ".$m['user'][$trg_id]['hit']." put: ".$put;
						//  make target user no longer active in away map
						//  target user next turn, detect hit < 1, return them to their home map
						$m['left'][$trg_id] = array(
						  'handle' => $m['user'][$trg_id]['handle'],
						  'x'      => $m['user'][$trg_id]['x'],
						  'y'      => $m['user'][$trg_id]['y'],
						  'yaw'    => $m['user'][$trg_id]['yaw'],
						  'hit'    => 0);
						//  REMOVE USER FROM AWAY MAP
						unset($m['user'][$trg_id]);
						//  LOG knockout action
						//  FUTURE, do this later, after tick has been updated (see below)
						$action = array(
						  date('Y-m-d'),
						  date('H:i:s'),
						  $m['tick'][1],
						  $_SESSION['uid_dg'],
						  $_SESSION['username_dg'],
						  $trg_id,
						  $av['handle'],
						  'knock out');
						$log_dungeon = $data_dir.'/'.$dungeons[0].'.log';
						append_map_log($log_dungeon, &$action);
						//  get number of players active
						$actions = array();
						$action2 = array();
						$rec_dungeon = $data_dir.'/'.$dungeons[0].'.recent';  //  FUTURE, this is set twice :-(
						get_map_recent($rec_dungeon, &$actions);
						//  append action
						//  FUTURE: edge case, this tick already has been recorded, just overwrites?
						$actions[$m['tick'][1]] = array(
						  $_SESSION['uid_dg'],
						  $_SESSION['username_dg'],
						  $trg_id,
						  $av['handle'],
						  'knock out');
						count($actions2);
						/* array_push($actions,
						  $m['tick'][1],
						  $_SESSION['uid_dg'],
						  $_SESSION['username_dg'],
						  $trg_id,
						  $av['handle'],
						  'knock out');  */
						$max_count = count($actions);
						//  echo "<pre>past actions: ".$max_count.", targets: ".$trgt_qty."\n"; print_r($actions); echo "</pre>";
						//  sort action by tick
						krsort($actions);  //  most recent first
						if ($max_count > 3)
							array_pop($actions);
						//  snip off oldest action
						put_map_recent($rec_dungeon, &$actions);
						}
					else
						$msg2 .= " HIT! ".$m['user'][$trg_id]['hit']." put: ".$put;
					}
				else
					$msg2 .= " MISS! ";
				}
			if ($debug_mask & DEBUG_USR) {
			echo "1 <pre style=\"font-size: smaller;\">"; print_r($m['user']); echo "</pre>"; }
			//    abs($MAX + $av['x] - $rx) ...
			//  FUTURE: what about wrap around range edge case?
			//          dungeon flag if no wrap around?
			$trgt_val[$trgt_qty] = $ak.",".$av['handle'].",".$rv;
			$trgt_qty++;
			}
		}

	if ($put == 1) {
		$m['tick'][1]++;  //  FUTURE: don't increment for home if play map active?
		if ($debug_mask & DEBUG_USR) {
		echo "2 <pre style=\"font-size: smaller;\">"; print_r($m['user']); echo "</pre>"; }
		if ($map_bits & (FLAG_PLAY_OK | FLAG_PLAY_NEW))
			$file_put = $file_dungeon;
		else
			$file_put = $file_home;
		//  echo "\n[active]\n<pre>"; print_r($m); echo "</pre>";
		if (put_map($file_put, $m))
			$msg3 = 'write map successful: '.$file_put;
		else {
			//  FUTURE, revert session updating x,y,yaw?
			$msg3 = 'write map error: '.$file_put;
			}
		}

	//  player leaving away map, returning to home map
	if ($put_home_return == 1) {
		if ($cmd == 'giveup') {
			$file_put = $file_home;
			//  echo "\n[home]\n<pre>"; print_r($m_home); echo "</pre>";
			if (put_map($file_put, $m_home))
				$msg3 .= 'write map successful: '.$file_put;
			else {
				//  FUTURE, revert session updating x,y,yaw?
				$msg3 .= 'write map error: '.$file_put;
				}
			}
		}

	//  opponent map, update (only allow one for now)

	//  FUTURE, if possible consolidate log file writing to occur here, after tick may have been incremented

	$put_away_chain = 0;
	//  FUTURE: proceed from one away map to another away map
	if ($put_away_chain == 1) {
		}

	if ($bonk_1 == 1) {
		$v = 32;  //  show blank wall, entering dungeon!
		//  FUTURE: load in new non-home map?
		}

	if ($map_bits & (FLAG_PLAY_OK | FLAG_PLAY_NEW)) {
		//  give up button
		//  any hmoe map updates needed?
		}

	//  setting session here is very important, FUTURE: is it?  backend will catch anyway?
	//  when user clicks move/attack/... button
	//  it will check map tick vs session before making request
	$_SESSION['tick'] = $m['tick'][1];
	$nearwall = 0;
	if (!isset($m['user'][$_SESSION['uid_dg']])) {
		//  when player is switching maps, player has 'no location' until they refresh
		//  echo "<pre> you are not active </pre>";
		$mapchanging = 1;
		}

	else {
		$mapchanging = 0;
		//  ZZZZ

	$x   = $m['user'][$_SESSION['uid_dg']]['x'];
	$y   = $m['user'][$_SESSION['uid_dg']]['y'];
	$yaw = $m['user'][$_SESSION['uid_dg']]['yaw'];
	$hit = $m['user'][$_SESSION['uid_dg']]['hit'];

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
	//  echo "<pre style=\"font-size: smaller;\">"; print_r($view); echo "</pre>";
	//  FUTURE: map value > 1, < 8 obstruction block for now
	if ($view['a'][2] & 7) { $f = $f + 40;  $z[0] = 2; }
	if ($view['b'][2] & 7) { $f = $f + 20;  $z[1] = 2; }
	if ($view['c'][2] & 7) { $f = $f + 10;  $z[2] = 2; }
	if ($view['a'][1] & 7) { $f = $f +  4;  $z[0] = 1; }
	if ($view['b'][1] & 7) { $f = $f +  2;  $z[1] = 1; }
	if ($view['c'][1] & 7) { $f = $f +  1;  $z[2] = 1; }

	if ($bonk != 1 && $bonk_1 != 1)
		$v = near_far($f);
		}
	//  $msg = "view: ".$v.", field: ".$f." tick: ".$m['tick']." x, y = ".$x.", ".$y;
	$msg = "tick: ".$_SESSION['tick']." x:".(isset($x) ? $x : 'N/A')." y:".(isset($y) ? $y : 'N/A').
	       " ".(isset($yaw) ? $yaw : 'N/A').
	       "&deg; hp:".(isset($hit) ? $hit : 'N/A');
	if ($debug_mask & DEBUG_ADM) {
		$msg = "view: ".(isset($v) ? $v : 'N/A').
	               ", field: ".(isset($f) ? $f : 'N/A')." ".$msg;
		if ($msg3)
			$msg .= "\n<br>".$msg3;
		if ($msg2)
			$msg .= "\n<br>".$msg2;
		}
	if (($debug_mask & DEBUG_ADM) && ($_SESSION['uid_dg'] == 1)) // admin/rickatech check
		$msg .= "\n<br><span style=\"font-size: smaller; color: #ff0000;\">".$_SERVER['REQUEST_URI']."</span>";

	/*  modifying map for display purposes,
        /*  NEVER SAVE THIS?  */
	//  $m[$x][$y] = '*';
	$o = NULL;
	$oo = NULL;
	foreach ($m['user'] as $ak => $av) {
		//  FUTURE: user scan is done in code prior to here, consolidate?
		if (!$mapchanging && $av['x'] == $x && $av['y'] == $y)
			$m[$x][$y] = '*';
		else {
			$m[$av['x']][$av['y']] = '+';
			if (!$mapchanging) {
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
	}

if (isset($msg))
	render($v, $msg, $nearwall, $o, $oo, isset($yaw) ? $yaw : 0);
else
	render($v);

	echo "\n<div style=\"display: none;\" id=\"log_activity\">\n";
if ($map_bits & (FLAG_PLAY_OK | FLAG_PLAY_NEW)) {
	//  report log knockout action, partially based on previous call to append_map_log
	//  FUTURE, if no away map, show log for home map?
	$rec_dungeon = $data_dir.'/'.$dungeons[0].'.recent';  //  FUTURE, this is set twice :-(
	get_map_recent($rec_dungeon, &$actions);
	$log_report = "recent activity";
	foreach ($actions as $ak => $av) {
		$log_report .= "\n<br>";
		$log_report .= $ak;
		$log_report .= ", ".$av[1]." > ".$av[3].": ".$av[4];
		}
	echo "\n<p style=\"margin: 0px;\">".$log_report."</p>\n";
	}
else {
	echo "\n<p style=\"margin: 0px;\"> ... </p>\n";
	}
	echo "</div>\n";

if (0) {
//if (isset($_SESSION['uid_dg']) && ($_SESSION['uid_dg'] == 1)) { // admin/rickatech check
//if (isset($_SESSION['uid_dg']) && ($_SESSION['uid_dg'] < 3)) { // admin/rickatech check
	//echo "\n<div style=\"display: none;\" id=\"map_hidden_0\">\n";
	echo "\n<div style=\"display: table; margin: 0px auto; border: 1px solid;\" id=\"map_hidden_0\">\n";
	if (isset($m_new)) {
		print_map($m_new);
		}
	else {
		if (isset($m)) {
			print_map($m);
			//  echo "<pre>"; print_r($m); echo "</pre>";
		//	if (isset($m_home)) {
		//		if ($m != $m_home)
		//		print_map($m_home);
		//		}
			}
		}
	echo "\n</div>\n";
	}

if ($debug_mask & DEBUG_FRM) $form_show = 'block'; else $form_show = 'hidden';
//  typically, a javascript callback will be invoked after
//  this page loads to adjust navigation button elsewhere on the page
echo "\n\n<input id=\"map_bits\" type=".$form_show." value=\"".$map_bits."\">\n";
//  available targets
echo "\n\n<input id=\"trgt_qty\" type=".$form_show." value=\"".$trgt_qty."\">\n";
for ($i = 0; $i < $trgt_qty; $i++)
	echo "\n\n<input id=\"trgt_val_".$i."\" type=".$form_show." value=\"".$trgt_val[$i]."\">\n";
echo "\n\n<input id=\"actn_qty\" type=".$form_show." value=\"1\">\n";
echo "\n\n<input id=\"actn_val_0\" type=".$form_show." value=\"tag\">\n";
//  FUTURE: add additional elements for:
//    - active players on this map (including range/stats?
//    - available actions
//  FUTURE: replace input, with JSON?
//  http://viralpatel.net/blogs/dynamic-add-textbox-input-button-radio-element-html-javascript/
//  http://stackoverflow.com/questions/9338205/javascript-explode-equivilent
//  FUTURE: target: id,handle,range
?>
