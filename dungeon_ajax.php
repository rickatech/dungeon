<?PHP 
/*  This 'plug-in' generates output for calout div from AJAX call.

/*  FUTURE
  - use case: user logged in and active, steps away from computer, back after an hour, clicks a command
    javascript timer to idle screen, reset session?  server side, invalid session after x minutes
    should refresh display, ignore command, indicate your next command will be ohonored if you don't step away again
  - use use: [ need more, build test plan ]
  - code coverage: lots of obsolete code, how to enable source code report that show what code is still active  */

/*  Invalid session okay here since login prompt if no user is detected  */
session_start();
date_default_timezone_set('America/Los_Angeles');

if (isset($_GET['ajax'])) {
	/*  0 render full cal div, 1 render just the calout div, unset ajax disabled  */
	if ($ajax = $_GET['ajax'] == 0) {
		/*  display: none, block  visibility: hidden, visible  */
		echo "\n\n<!--  calout  --><div id=\"calout\">";
		echo "[ calout ]</div><!--  calout  -->";
		return;
		}
	}
else {
	echo "[ ajax disabled ]";
	return;
	}

include "config.php";
if ($debug_mask & DEBUG_SES) { echo "\n<pre>"; print_r($_SESSION);  echo "</pre>\n"; }
include "lib_map.php";	/*  utils for loading/updating/saving map files  */
include "lofi.php";	/*  low resolution asc art patterns  */
include "render.php";	/*  backend view render setup  */

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


const FLAG_HOME_LOAD = 1;  //  home map loaded, existing
const FLAG_PLAY_OK =   2;  //  play map loaded, existing
const FLAG_HOME_NEW =  4;  //  home map loaded, new default
const FLAG_LOGIN_NO =  8;  //  login required
const FLAG_WELCOME =  16;  //  new user, no home, welcome
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
		$map_bits |= FLAG_WELCOME;
		}
	else {
		//  prompt to create new player home map
	   	if ($m_home = get_map($kkk = $data_dir.'/'.'home.txt')) {
			$map_bits |= FLAG_HOME_NEW;
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
if ($map_bits & (FLAG_HOME_LOAD + FLAG_HOME_NEW)) {
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
			//  got here because, existing away map or new away map loaded
			//  THIS IS IT, mark dungeon map is in play
			$m = &$m_play;
			if (!isset($m_play['user'][$_SESSION['uid_dg']])) {
				if (isset($m['left'][$_SESSION['uid_dg']]['hit'])) {
					if ($m['left'][$_SESSION['uid_dg']]['hit'] < 1) {
						$msg2 .= "YOU HAVE BEEN KICKED HOME!";
						$cmd = "giveup";  //  OVERRIDE USER COMMAND
						$map_bits |= FLAG_KICKED;
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
					//  FUTURE: if another player or dynamic object already at spawn location
					//          lower occupying objects hit points (damage from being in the way of door)
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
	if (!($map_bits & FLAG_KICKED)) {  //  PILOT
		$nyaw = $myaw = $m['user'][$_SESSION['uid_dg']]['yaw'];
		$nx   = $mx   = $m['user'][$_SESSION['uid_dg']]['x'];
		$ny   = $my   = $m['user'][$_SESSION['uid_dg']]['y'];
		}

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
		if ($map_bits & FLAG_KICKED) {
			$hit_save = 0;
			unset($m['left'][$_SESSION['uid_dg']]);
			$msg3 = "kicked";
			}
		else {
			//  away, user is no longer active in away map, return user to home map
			$hit_save = $m['user'][$_SESSION['uid_dg']]['hit'];  //  PILOT, moving this 7 lines down
			$m['left'][$_SESSION['uid_dg']] = array(
			  'handle' => $_SESSION['username_dg'],
			  'x'      => $m['user'][$_SESSION['uid_dg']]['x'],
			  'y'      => $m['user'][$_SESSION['uid_dg']]['y'],
			  'yaw'    => $m['user'][$_SESSION['uid_dg']]['yaw'],
			  'hit'    => $hit_save);
			$msg3 = "give up";
			$m_home['left'][$_SESSION['uid_dg']]['hit'] = $hit_save;  //  PILOT
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
		if ($debug_mask & DEBUG_FOO) {
			echo "\n<pre>"; print_r($trginf);  echo "</pre>\n";
			$msg3 = "do action: ".$_GET['act'].", ".$trginf[0];
			}
		if ($trginf[0] > 0) {
			$trg_id   = $trginf[0];
			$trg_type = $trginf[3];  //  'ply' | 'npc'

			}
		else
			$trg_id = 0;             //  $trg_type undefined
		$trgact = $_GET['act'];
		//  we should already have play map loaded, which means all active players and NPC's too
		//    - confirm the target is present in play map
		//    - check target range
		//    - if tag, increment target tag count:
		//      next tick for that characer they should get processed as giveup with appropriate messaging
		}

	/*  Okay, done with processing player command, let check environment for collisions  */
	$bonk = 0;
	$trgt_qty = 0;
/**/	if (!($map_bits & FLAG_KICKED)) {  //  PILOT, when kicked player in in limbo, has no location
	//  Dynamic objects
	//  Check if collide into another user or FUTURE other dynamic element
	foreach ($m['user'] as $ak => $av) {
		if ($ak != $_SESSION['uid_dg']) {
			//  FYI, if collide/bonk happens, unlikely a non-move is action has occurred
			if ($put == 1) {
				if ($av['x'] == $nx && $av['y'] == $ny)
					$bonk = 1;
				}
			}
		}
	//  Check if collide into npc
	foreach ($m['npc'] as $ak => $av) {
	//	if ($ak != $_SESSION['uid_dg']) {
			//  FYI, if collide/bonk happens, unlikely a non-move is action has occurred
			if ($put == 1) {
				if ($av['x'] == $nx && $av['y'] == $ny)
					$bonk = 1;
				}
	//		}
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

	//  Calculate targets ranges, assess hit/dammage dealt
	//  N P C
	foreach ($m['npc'] as $ak => $av) {
		$kicked = false;  //  assume npc is not getting kicked/removed this tick
		$rv = abs($av['x'] - $rx) + abs($av['y'] - $ry);  //  FUTURE: fast Pythagorean Theorem

		//  FUTURE: BREAK OUT TO SEPERATE FUNCTION?
		if (isset($trgact) && $trgact == 'tag' && $ak == $trg_id && $trg_type == 'npc') {
			//  attack action processing: against npc
			if ($debug_mask & DEBUG_USR) {
				echo "trg_id: ".$trg_id.", ".$trg_type." <pre style=\"font-size: smaller;\">";
				print_r($m['npc']); echo "</pre>";
				}
			if ($ak < 1) {
				$msg2 .= " WEIRD :-/ ";
				}
			else if ($rv < 2) {  //  FUTURE: tag default range is 1, but may need to support greater ranges
				$put = 1;
				if (!isset($m['npc'][$trg_id]['hit'])) {
					$m['npc'][$trg_id]['hit'] = 0;
					}
				if ($m['npc'][$trg_id]['hit'] > 0) {
					$m['npc'][$trg_id]['hit']--;
					}
				if ($m['npc'][$trg_id]['hit'] < 1) {
					$msg2 .= " KNOCK OUT HIT! ".$m['npc'][$trg_id]['hit']." put: ".$put;
					//  LOG knockout action
					append_map_log2($dungeons[0], $m['tick'][1], 'p>n', 'knock out',
					  $_SESSION['uid_dg'], $_SESSION['username_dg'],
					  $trg_id, $m['npc'][$trg_id]['handle']);
					//  RECENT activity update
					append_map_recent($dungeons[0], $m['tick'][1], 'p>n', 'knock out',
					  $_SESSION['uid_dg'], $_SESSION['username_dg'],
					  $trg_id, $m['npc'][$trg_id]['handle'],
					  3);

					//  REMOVE NPC FROM AWAY MAP, see SPAWN NPC as part of player kicked below
					unset($m['npc'][$trg_id]);
					$kicked = true;

						//  HI SCORE update
						//    - read in players score array
						//    - increment dominant player knock out tally
						//      resort
						//    - write out players score array
					}
				else
					$msg2 .= " HIT! ".$m['npc'][$trg_id]['hit']." put: ".$put;
				}
			else
				$msg2 .= " MISS! ".$m['npc'][$trg_id]['hit'];
			}
		if (!$kicked) {
			$trgt_val[$trgt_qty] = $ak.",".$av['handle'].",".$rv.",npc";
			$trgt_qty++;
			}
		}
	//  Calculate targets ranges, assess hit/dammage dealt
	//  O T H E R   P L A Y E R S
	foreach ($m['user'] as $ak => $av) {
		if ($ak != $_SESSION['uid_dg']) {
			$kicked = false;  //  assume player is not getting kicked/removed this tick
			$rv = abs($av['x'] - $rx) + abs($av['y'] - $ry);  //  FUTURE: fast Pythagorean Theorem

			//  FUTURE: BREAK OUT TO SEPERATE FUNCTION?
			if (isset($trgact) && $trgact == 'tag' && $ak == $trg_id && $trg_type = 'ply') {
				//  attack action processing: against player
				if ($debug_mask & DEBUG_USR) {
					echo "trg_id: ".$trg_id.", ".$trg_type." <pre style=\"font-size: smaller;\">";
					print_r($m['user']); echo "</pre>";
					}
				if ($ak < 1) {
					$msg2 .= " WEIRD :-/ ";
					}
				else if ($rv < 2) {  //  FUTURE: tag default range is 1, but may need to support greater ranges
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
						//  LOG knockout action
						append_map_log2($dungeons[0], $m['tick'][1], 'p>p', 'knock out',
						  $_SESSION['uid_dg'], $_SESSION['username_dg'],
						  $trg_id, $av['handle']);
						//  REMOVE USER FROM AWAY MAP
						unset($m['user'][$trg_id]);
						$kicked = true;

						//  SPAWN npc at kicked player location if no npc's active
	       					if (!isset($m['npc'][1])) {
						//  if (0) {
	       						$m['npc'][1] = array(
				       			  'handle' =>'zombie',
							  'x' =>      $m['left'][$trg_id]['x'],
	      				 		  'y' =>      $m['left'][$trg_id]['y'],
	       						  'yaw' =>    $m['left'][$trg_id]['yaw'],
				       			  'hit' =>    1);
							$trgt_val[$trgt_qty] =  1 .",".$m['npc'][1]['handle'].",".$rv.",npc";
					//		$trgt_val[$trgt_qty] = $ak.",".$av['handle']         .",".$rv.",ply";
							$trgt_qty++;
							}

						append_map_recent($dungeons[0], $m['tick'][1], 'p>p', 'knock out',
						  $_SESSION['uid_dg'], $_SESSION['username_dg'],
						  $trg_id, $av['handle'],
						  3);

						//  HI SCORE update
						//    - read in players score array
						//    - increment dominant player knock out tally
						//      resort
						//    - write out players score array
						$scr_dungeon = $data_dir.'/'.$dungeons[0].'.score';
						$actions = array();
						get_map_score($scr_dungeon, &$actions);
						$actions['names'][$_SESSION['uid_dg']] = $_SESSION['username_dg'];
						if (isset($actions['scores'][$_SESSION['uid_dg']]))
							$actions['scores'][$_SESSION['uid_dg']]++;
						else
							$actions['scores'][$_SESSION['uid_dg']] = 1;
						//$actions['scores'][1]++;
						arsort($actions['scores']);
						put_map_score($scr_dungeon, &$actions);
						}
					else
						$msg2 .= " HIT! ".$m['user'][$trg_id]['hit']." put: ".$put;
					}
				else
					$msg2 .= " MISS! ".$m['user'][$trg_id]['hit'];
				}
			if ($debug_mask & DEBUG_USR) {
			echo "1 ".($kicked ? "true" : "false")." <pre style=\"font-size: smaller;\">"; print_r($m['user']); print_r($m['npc']); echo "</pre>"; }
			//    abs($MAX + $av['x] - $rx) ...
			//  FUTURE: what about wrap around range edge case?
			//          dungeon flag if no wrap around?
			if (!$kicked) {
				$trgt_val[$trgt_qty] = $ak.",".$av['handle'].",".$rv.",ply";
				$trgt_qty++;
				}
			}
		}
/**/	}

	if ($put == 1) {
		$m['tick'][1]++;  //  FUTURE: don't increment for home if play map active?
		if ($debug_mask & DEBUG_USR) {
		echo "2 <pre style=\"font-size: smaller;\">"; print_r($m['user']); print_r($m['npc']); echo "</pre>"; }
		if ($map_bits & (FLAG_PLAY_OK | FLAG_PLAY_NEW))
			$file_put = $file_dungeon;
		else
			$file_put = $file_home;
		if (!put_map($file_put, $m)) {
			//  FUTURE, revert session updating x,y,yaw?
			$msg3 = 'write map error: '.$file_put;
			}
		}

	//  player leaving away map, returning to home map
	if ($put_home_return == 1) {
		if ($cmd == 'giveup') {
			$file_put = $file_home;
			if (!put_map($file_put, $m_home)) {
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
		}
	if ($msg3)
		$msg .= "\n<br>".$msg3;
	if ($msg2)
		$msg .= "\n<br>".$msg2;
	if (($debug_mask & DEBUG_ADM) && ($_SESSION['uid_dg'] == 1)) // admin/rickatech check
		$msg .= "\n<br><span style=\"font-size: smaller; color: #ff0000;\">".$_SERVER['REQUEST_URI']."</span>";

	//  FUTURE: lots of 'almost' identical code below, refactor?
	$near = array();
	/*  flag/mark nearby players
	/*  left<-ooo->right
	/*  mm nn  oo  pp qq
	/*     n   o   p
	/*         ^      */
	foreach ($m['user'] as $ak => $av) {
		//  FUTURE: user scan is done in code prior to here, consolidate?
		if (!$mapchanging && $av['x'] == $x && $av['y'] == $y)
			$m[$x][$y] = '*';  // modifying map for display purposes, NEVER SAVE THIS  */
		else {
			$m[$av['x']][$av['y']] = '+';  // modifying map for display purposes, NEVER SAVE THIS  */
			if (!$mapchanging) {
				if ($yaw < 90) {
					if ($av['x'] == $x && $av['y'] == stepwrap($y, $m['size'][2], -3))
						$near['ooo'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1], -2) && $av['y'] == stepwrap($y, $m['size'][2], -2))	
						$near['mm'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1], -1) && $av['y'] == stepwrap($y, $m['size'][2], -2))	
						$near['nn'] = $av['handle'][0];
					if ($av['x'] == $x && $av['y'] == stepwrap($y, $m['size'][2], -2))
						$near['oo'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1],  1) && $av['y'] == stepwrap($y, $m['size'][2], -2))	
						$near['pp'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1],  2) && $av['y'] == stepwrap($y, $m['size'][2], -2))	
						$near['qq'] = $av['handle'][0];
					if ($av['x'] == $x && $av['y'] == stepwrap($y, $m['size'][2], -1))
						$near['o'] =  $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1], -1) &&	
					    $av['y'] == stepwrap($y, $m['size'][2], -1))
						$near['n'] =  $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1],  1) &&	
					    $av['y'] == stepwrap($y, $m['size'][2], -1))
						$near['p'] =  $av['handle'][0];
					}
				else if ($yaw < 180) {
					if ($av['x'] == stepwrap($x, $m['size'][1],  3) && $av['y'] == $y)
						$near['ooo'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1],  2) && $av['y'] == stepwrap($y, $m['size'][2], -2))	
						$near['mm'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1],  2) && $av['y'] == stepwrap($y, $m['size'][2], -1))	
						$near['nn'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1],  2) && $av['y'] == $y)
						$near['oo'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1],  2) && $av['y'] == stepwrap($y, $m['size'][2],  1))	
						$near['pp'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1],  2) && $av['y'] == stepwrap($y, $m['size'][2],  2))	
						$near['qq'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1],  1) && $av['y'] == $y)
						$near['o'] =  $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1],  1) &&
					    $av['y'] == stepwrap($y, $m['size'][2], -1))
						$near['n'] =  $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1],  1) &&
					    $av['y'] == stepwrap($y, $m['size'][2],  1))
						$near['p'] =  $av['handle'][0];
					}
				else if ($yaw < 270) {
					if ($av['x'] == $x && $av['y'] == stepwrap($y, $m['size'][2],  3))	
						$near['ooo'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1],  2) && $av['y'] == stepwrap($y, $m['size'][2],  2))	
						$near['mm'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1],  1) && $av['y'] == stepwrap($y, $m['size'][2],  2))	
						$near['nn'] = $av['handle'][0];
					if ($av['x'] == $x && $av['y'] == stepwrap($y, $m['size'][2],  2))	
						$near['oo'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1], -1) && $av['y'] == stepwrap($y, $m['size'][2],  2))	
						$near['pp'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1], -2) && $av['y'] == stepwrap($y, $m['size'][2],  2))	
						$near['qq'] = $av['handle'][0];
					if ($av['x'] == $x && $av['y'] == stepwrap($y, $m['size'][2],  1))	
						$near['o'] =  $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1],  1) &&	
					    $av['y'] == stepwrap($y, $m['size'][2],  1))
						$near['n'] =  $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1], -1) &&	
					    $av['y'] == stepwrap($y, $m['size'][2],  1))
						$near['p'] =  $av['handle'][0];
					}
				else {
					if ($av['x'] == stepwrap($x, $m['size'][1], -3) && $av['y'] == $y)
						$near['ooo'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1], -2) && $av['y'] == stepwrap($y, $m['size'][2],  2))	
						$near['mm'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1], -2) && $av['y'] == stepwrap($y, $m['size'][2],  1))	
						$near['nn'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1], -2) && $av['y'] == $y)
						$near['oo'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1], -2) && $av['y'] == stepwrap($y, $m['size'][2], -1))	
						$near['pp'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1], -2) && $av['y'] == stepwrap($y, $m['size'][2], -2))	
						$near['qq'] = $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1], -1) && $av['y'] == $y)
						$near['o'] =  $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1], -1) &&	
					    $av['y'] == stepwrap($y, $m['size'][2],  1))
						$near['n'] =  $av['handle'][0];
					if ($av['x'] == stepwrap($x, $m['size'][1], -1) &&	
					    $av['y'] == stepwrap($y, $m['size'][2], -1))
						$near['p'] =  $av['handle'][0];
					}
				}
			}
		}
	foreach ($m['npc'] as $ak => $av) {
		//  FUTURE: user scan is done in code prior to here, consolidate?
		$m[$av['x']][$av['y']] = 'Z';  // modifying map for display purposes, NEVER SAVE THIS  */
		if (!$mapchanging) {
			if ($yaw < 90) {
				if ($av['x'] == $x && $av['y'] == stepwrap($y, $m['size'][2], -3))	
					$near['ooo'] = 'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1], -2) && $av['y'] == stepwrap($y, $m['size'][2], -2))	
					$near['mm'] =  'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1], -1) && $av['y'] == stepwrap($y, $m['size'][2], -2))	
					$near['nn'] =  'Z';
				if ($av['x'] == $x && $av['y'] == stepwrap($y, $m['size'][2], -2))	
					$near['oo'] =  'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1],  1) && $av['y'] == stepwrap($y, $m['size'][2], -2))	
					$near['pp'] =  'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1],  2) && $av['y'] == stepwrap($y, $m['size'][2], -2))	
					$near['qq'] =  'Z';
				if ($av['x'] == $x && $av['y'] == stepwrap($y, $m['size'][2], -1))	
					$near['o'] =   'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1], -1) &&	
				    $av['y'] == stepwrap($y, $m['size'][2], -1))	
					$near['n'] =   'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1],  1) &&	
				    $av['y'] == stepwrap($y, $m['size'][2], -1))	
					$near['p'] =   'Z';
				}
			else if ($yaw < 180) {
				if ($av['x'] == stepwrap($x, $m['size'][1],  3) && $av['y'] == $y) 
					$near['ooo'] = 'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1],  2) && $av['y'] == stepwrap($y, $m['size'][2], -2))	
					$near['mm'] =  'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1],  2) && $av['y'] == stepwrap($y, $m['size'][2], -1))	
					$near['nn'] =  'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1],  2) && $av['y'] == $y) 
					$near['oo'] =  'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1],  2) && $av['y'] == stepwrap($y, $m['size'][2],  1))	
					$near['pp'] =  'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1],  2) && $av['y'] == stepwrap($y, $m['size'][2],  2))	
					$near['qq'] =  'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1],  1) && $av['y'] == $y) 
					$near['o'] =   'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1],  1) &&
				    $av['y'] == stepwrap($y, $m['size'][2], -1))
					$near['n'] =   'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1],  1) &&
				    $av['y'] == stepwrap($y, $m['size'][2],  1))
					$near['p'] =   'Z';
				}
			else if ($yaw < 270) {
				if ($av['x'] == $x && $av['y'] == stepwrap($y, $m['size'][2],  3))	
					$near['ooo'] = 'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1],  2) && $av['y'] == stepwrap($y, $m['size'][2],  2))	
					$near['mm'] =  'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1],  1) && $av['y'] == stepwrap($y, $m['size'][2],  2))	
					$near['nn'] =  'Z';
				if ($av['x'] == $x && $av['y'] == stepwrap($y, $m['size'][2],  2))	
					$near['oo'] =  'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1], -1) && $av['y'] == stepwrap($y, $m['size'][2],  2))	
					$near['pp'] =  'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1], -2) && $av['y'] == stepwrap($y, $m['size'][2],  2))	
					$near['qq'] =  'Z';
				if ($av['x'] == $x && $av['y'] == stepwrap($y, $m['size'][2],  1))	
					$near['o'] =   'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1],  1) &&	
				    $av['y'] == stepwrap($y, $m['size'][2],  1))	
					$near['n'] =   'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1], -1) &&	
				    $av['y'] == stepwrap($y, $m['size'][2],  1))	
					$near['p'] =   'Z';
				}
			else {
				if ($av['x'] == stepwrap($x, $m['size'][1], -3) && $av['y'] == $y)
					$near['ooo'] = 'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1], -2) && $av['y'] == stepwrap($y, $m['size'][2],  2))	
					$near['mm'] =  'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1], -2) && $av['y'] == stepwrap($y, $m['size'][2],  1))	
					$near['nn'] =  'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1], -2) && $av['y'] == $y)
					$near['oo'] =  'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1], -2) && $av['y'] == stepwrap($y, $m['size'][2], -1))	
					$near['pp'] =  'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1], -2) && $av['y'] == stepwrap($y, $m['size'][2], -2))	
					$near['qq'] =  'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1], -1) && $av['y'] == $y)
					$near['o'] =   'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1], -1) &&	
				    $av['y'] == stepwrap($y, $m['size'][2],  1))
					$near['n'] =   'Z';
				if ($av['x'] == stepwrap($x, $m['size'][1], -1) &&	
				    $av['y'] == stepwrap($y, $m['size'][2], -1))
					$near['p'] =   'Z';
				}
			}
		}
	}

if (isset($msg))
	render($v, $msg, $nearwall, isset($yaw) ? $yaw : 0, &$near);
else
	render($v);  //  OBSOLETE?

	echo "\n<div style=\"display: none;\" id=\"log_activity\">\n";
if ($map_bits & (FLAG_PLAY_OK | FLAG_PLAY_NEW)) {
	//  report log knockout action, partially based on previous call to append_map_log
	//  FUTURE, if no away map, show log for home map?
	$rec_dungeon = $data_dir.'/'.$dungeons[0].'.recent';  //  FUTURE, this is set twice :-(
	$actions = array();
	get_map_recent($rec_dungeon, &$actions);
	if (0) {
		$log_report = "recent activity";
		foreach ($actions as $ak => $av) {
			$log_report .= "\n<br>";
			$log_report .= $ak;
			$log_report .= ", ".$av[1]." > ".$av[3].": ".$av[4];
			}
		echo "\n<p style=\"margin: 0px;\">".$log_report."</p>\n";
		}
	else {
		$log_report = "\n<tr><td colspan=2>recent activity </td>\n<td>tick</td></tr>";
		foreach ($actions as $ak => $av) {
			$log_report .= "\n<tr><td>";
			$log_report .= $av[4].",</td>\n<td>".$av[1]." > ".$av[3]."</td>";
			$log_report .= "\n<td>".$ak."</td></tr>";
			}
		echo "\n<table style=\"display: inline; padding: 0px; font-size: smaller;\">\n".$log_report."\n</table>\n";
		}
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

//  Place hidden DOM elements hinting at what targets and commands are available 
//    Typically, a javascript callback will be invoked after this page loads
//    to dynamically adjust navigation elements elsewhere on the page.
if ($debug_mask & DEBUG_FRM) $form_show = 'block'; else $form_show = 'hidden';
//  map_bits flag state
echo "\n\n<input id=\"map_bits\" type=".$form_show." value=\"".$map_bits."\">\n";
//  active dungeon map, for use by newmap_toggle(), a_reset
echo "\n\n<input id=\"map_name\" type=".$form_show." value=\"";
echo (isset($m_home['away'][3]) && ($map_bits & FLAG_PLAY_OK)) ? $m_home['away'][3] : "home";
echo "\">\n";
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

//  PLAYER TRIGGERED TICK - COMPLETED

//  NPC TURN TICK CHECK - BLOCK PLAYER TICKS?
 
?>
