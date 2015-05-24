<?PHP
include "config.php";
session_start();  /*  valid session will be checked against uid_dg below  */
include "lib_map.php";

/*  This 'plug-in' generates output for dv_hiscore div from AJAX call  */

if (isset($_GET['ajax'])) {
	/*  0 render full cal div, 1 render just the calout div, unset ajax disabled  */
	$ajax = $_GET['ajax'];  //  this isn't confirmation of valid login, see elsewhere for that
	}
else {
	echo "[ ajax disabled ]";
	return;
	}

if (isset($_GET['did'])) {  /*  did, Dungeon ID :-)  */
	$dungeon_id = $_GET['did'];
	}

$actions = array();
$rec_dungeon = $data_dir.'/'.$dungeon_id.'.score';
echo "\n<p style=\"margin-top: 0px;\">".$dungeon_id." high scores";
get_map_score($rec_dungeon, &$actions);
foreach ($actions['scores'] as $ak =>$av) {
	echo "\n<br>&nbsp; ".$av.", ".$actions['names'][$ak];
	}
echo "\n</p> \n";
?>
