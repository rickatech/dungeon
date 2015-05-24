<?PHP

session_start();
//  FUTURE, only allow if NOT isprod?
if (isset($_GET['debug'])) {
	if ($_GET['debug'] < 1)
		unset($_SESSION['debug']);
	else
		$_SESSION['debug'] = $_GET['debug'];
	}
if (!isset($_POST['logout']) && !isset($_GET['logout']) && isset($_SESSION['username_dg'])) {
	/*  preserve session unless logout detected  */
	$mode = 1;
	}
else if (isset($_POST['username_dg']) && isset($_POST['password']) && (!isset($_SESSION['username_dg']))) {
	/*  login attempt detected, setup session username_dg  */
	$mode = 2;
	$_SESSION['username_dg'] = $_POST['username_dg'];
	}
else if (isset($_GET['username_dg']) && isset($_GET['password']) && (!isset($_SESSION['username_dg']))) {
	/*  login attempt detected, setup session username_dg  */
	$mode = 2;
	$_SESSION['username_dg'] = $_GET['username_dg'];
	}
else {
	$mode = 0;
	}
if ($mode < 1) {
	/*  logout or inconsistent session detected, clear session username_dg, uid_dg  */
	if (isset($_SESSION['username_dg']))
		unset($_SESSION['username_dg']);
	if (isset($_SESSION['uid_dg']))
		unset($_SESSION['uid_dg']);
	}

function login_check($u, $p) {
	/*  Check login credentials against subscription database.  */
	/*  return 0 and set $_SESSION['uid_dg'] if username/password accepted
	/*  otherwise return an error string  */
	/*  Future 1: stop storing password in session  */
	/*  Future 2: still using http for page display, have login  */
	/*            use AJAX https call to authentication service  */
	if (0) {  // ***
		$query = "select * from users where login=\"".$u."\"";
		if (!mysql_connect("localhost", "subscribe", "..."))
			return ( "no db login, no db subscription ");
		else {
			if (!mysql_select_db("subscription"))
				return ("db login ok, no db subscription ");
			else {
				if (!$result = mysql_query($query))
					return ("db result empty");
				else if (!$row = mysql_fetch_row($result))
					return ("bad username");
				else if ($row[1] !=  md5($p))
					return ("bad password");
				else {
					$_SESSION['uid_dg'] = $row[3];
					return (0);
					}
				}
			}
		}
	else { // ***
		global $data_dir;
		$file_profiles = $data_dir.'/profiles_dev.txt';
		//  $file_profiles = $data_dir.'/profiles_dev.txt';
		$user_profiles = array();
		if (get_user_profiles($file_profiles, &$user_profiles)) {
			foreach ($user_profiles as $ua => $ub) {
				foreach ($ub as $uk => $uu) {
					if ((isset($ub['handle'])) && $ub['handle'] == $u) {
						//  echo "\n<br>found: ".$ua." <- ...";
						$_SESSION['uid_dg'] = $ua;
						return (0);
						}
					}
				}
			return ("bad username");
			}
		else
			return ("no user profiles file");
		} // ***
	}

function login_state() {
	/*  Assumes a form with name=login, type=post is being used.  */
	/*  return 1  login accepted - output logout  */
	/*  return 0  invalid login - output username, password form  */
	if (isset($_POST['username_dg']) && isset($_POST['password'])) {  // clasic POST form
		unset($_SESSION['uid_dg']);
		/*  login_check will set $_SESSION['uid_dg'] for valid login  */
		if ($msg = login_check($_POST['username_dg'], $_POST['password'])) {
			//  echo "<span style=\"color: #ff0000;\">".$msg."</span> ";
			unset($_SESSION['username_dg']);
			}
		}
	if (isset($_GET['username_dg']) && isset($_GET['password'])) {  // new AJAX form
		unset($_SESSION['uid_dg']);
		/*  login_check will set $_SESSION['uid_dg'] for valid login  */
		if ($msg = login_check($_GET['username_dg'], $_GET['password'])) {
			//  echo "<span style=\"color: #ff0000;\">".$msg."</span> ";
			unset($_SESSION['username_dg']);
			}
		}
	if (isset($_SESSION['uid_dg'])) {
		echo "\n<form method=\"POST\" action=\"\" name=\"login\" style=\"margin: 0px;\">";
		echo $_SESSION['username_dg']." ";
		echo "<input name=\"logout\" value=\"yes\" type=\"hidden\">";
		echo " <a href=\"javascript:head_logout();\">logout</a>";
		echo "\n</form>";
		return (1);
		}
	echo "\n<form method=\"POST\" action=\"\" name=\"login\" style=\"margin: 0px;\">";
	if (isset($msg))
		echo "<span style=\"color: #ff0000;\">".$msg."</span> ";
	echo "<a href=\"javascript: formpop('signup');\">signup</a> ";

	echo "\n<input size=12               name=\"username_dg\" id=\"username_dg\" style=\"font-size: 10px; border: 1px solid;\"";
	echo " value=\"username\" onKeyPress=\"detectKeyLogin(event)\"";
	echo " onfocus=\"if(this.value == 'username') {this.value = '';}\"";
	echo " onblur=\"if(this.value == '') {this.value = 'username';}\">";

	echo "\n<input size=12 type=password name=\"password\" id=\"password\" style=\"font-size: 10px; border: 1px solid;\"";
	echo " value=\"password\" onKeyDown=\"detectKeyLogin(event)\"";
	echo " onfocus=\"if(this.value == 'password') {this.value = '';}\"";
	echo " onblur=\"if(this.value == '') {this.value = 'password';}\">";

	echo "\n</form>";
	return (0);
	}

function get_user_profiles($file, &$users) {
	//  pass in empty array
	//  build array of ...
	//  if error, ...
	//  FUTURE: build a user profile class, allow custom (what this is), SQL, Facebook/OpenID support
	$result = false;
	if ($fh = fopen($file, 'r')) {
		while (($data = fgetcsv($fh, 1000, ",")) !== FALSE) {
			if (strpos($data[0], '#') === FALSE) {  //  skip if comment, #
				$va = array('handle' => $data[1]);
				$users[$data[0]] = $va;
				}
			}
		$result = true;
		fclose($fh);
		}
	return $result;
	}
?>
