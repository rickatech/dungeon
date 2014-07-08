<?PHP

session_start();
if (!isset($_POST['logout']) && isset($_SESSION['username'])) {
	/*  preserve session unless logout detected  */
	$mode = 1;
	}
else if (isset($_POST['username']) && isset($_POST['password']) && (!isset($_SESSION['username']))) {
	/*  login attempt detected, setup session username  */
	$mode = 2;
	$_SESSION['username'] = $_POST['username'];
	}
else {
	$mode = 0;
	}
if ($mode < 1) {
	/*  logout or inconsistent session detected, clear session username, uid  */
	if (isset($_SESSION['username']))
		unset($_SESSION['username']);
	if (isset($_SESSION['uid']))
		unset($_SESSION['uid']);
	}

function login_check($u, $p) {
	/*  Check login credentials against subscription database.  */
	/*  return 0 and set $_SESSION['uid'] if username/password accepted
	/*  otherwise return an error string  */
	/*  Future 1: stop storing password in session  */
	/*  Future 2: still using http for page display, have login  */
	/*            use AJAX https call to authentication service  */
	if (0) {  // ***
	$query = "select * from users where login=\"".$u."\"";
	if (!mysql_connect("localhost", "subscribe", "sub2008"))
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
				$_SESSION['uid'] = $row[3];
				return (0);
				}
			}
		}
	} else { // ***
		if ($u == "rickatech") {
			$_SESSION['uid'] = 1;
			return (0);
			}
		else
			return ("bad username");
	} // ***
	}

function login_state() {
	/*  Assumes a form with name=login, type=post is being used.  */
	/*  return 1  login accepted - output logout  */
	/*  return 0  invalid login - output username, password form  */
	if (isset($_POST['username']) && isset($_POST['password'])) {
		unset($_SESSION['uid']);
		/*  login_check will set $_SESSION['uid'] for valid login  */
		if ($msg = login_check($_POST['username'], $_POST['password'])) {
			echo "<span style=\"color: #ff0000;\">".$msg."</span> ";
			unset($_SESSION['username']);
			}
		}
	if (isset($_SESSION['uid'])) {
		echo $_SESSION['username']." ";
		echo "<input name=\"logout\" value=\"yes\" type=\"hidden\">";
		echo " <a href=\"javascript:document['login'].submit();\">logout</a>";
		return (1);
		}
	echo "<a href=\"javascript: formpop('signup');\">signup</a> ";
	echo "login ";
	echo "\n<input size=12 name=\"username\" style=\"font-size: 10px; border: 1px solid;\"";
	echo "  value=\"username\" onKeyPress=\"detectKey(event)\"";
	echo " onfocus=\"if(this.value == 'username') {this.value = '';}\"";
	echo " onblur=\"if(this.value == '') {this.value = 'username';}\">";
	echo "\n<input size=12 type=password name=\"password\" style=\"font-size: 10px; border: 1px solid;\"";
	echo " value=\"password\" onKeyDown=\"detectKey(event)\"";
	echo " onfocus=\"if(this.value == 'password') {this.value = '';}\"";
	echo " onblur=\"if(this.value == '') {this.value = 'password';}\">";
	return (0);
	}

?>
