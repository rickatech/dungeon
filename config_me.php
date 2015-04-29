<?PHP
//  copy this to config.php, then customize to taste
$version = '0.2.1';
$isprod = false;
$title =  'Dungeon Test'
$data_dir = "data";
$appleurl = 'http://public.zaptech.org/dungeon.png';
$signup_msg = 'This is stable release signup form. <br>Development release uses a different <a href=http://portal.zaptech.org/dun_dev/>form</a>.';
$dungeons = array('dungeon');
$homemap_prefix = 'user';	//  user00000001.txt, 'user' + uid of user + '.txt', player's home map
				//  home.txt, new player home map 'template'
$maxhit = 3;

//  uncomment to enable various debug output
const DEBUG_FOO =      1;
const DEBUG_USR =     2;
const DEBUG_FRM =    4;
const DEBUG_ADM =   8;  //  rickatech extra status
const DEBUG_KEY =  16;  //  experiment for direct keyboard control
const DEBUG_SES = 32;   //  output session
//$debug_mask = DEBUG_ADM; 
//  FUTURE, only allow if NOT isprod?
if (isset($_SESSION['debug']))
	$debug_mask = $_SESSION['debug'];
else
	$debug_mask = 0; 

//  global, room, and 1:1 chat, fee or reputation needed
//  global_chat

//  'local' chat map file suffix
//  _chat

//  global trade log: item trades
//  trade_chat

//  action log map file suffix: player tags, player kicks
//  .recent, .log

//  available non-player maps
//  dungeon
?>
