<?PHP

session_start();
if (!isset($_POST['logout']) && !isset($_GET['logout']) && isset($_SESSION['username'])) {
//if (!isset($_POST['logout']) && isset($_SESSION['username'])) {
	/*  preserve session unless logout detected  */
	$mode = 1;
	}
else if (isset($_POST['username']) && isset($_POST['password']) && (!isset($_SESSION['username']))) {
	/*  login attempt detected, setup session username  */
	$mode = 2;
	$_SESSION['username'] = $_POST['username'];
	}
else if (isset($_GET['username']) && isset($_GET['password']) && (!isset($_SESSION['username']))) {
	/*  login attempt detected, setup session username  */
	$mode = 2;
	$_SESSION['username'] = $_GET['username'];
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
		else if ($u == "henauker") {
			$_SESSION['uid'] = 2;
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
	if (isset($_POST['username']) && isset($_POST['password'])) {  // clasic POST form
		unset($_SESSION['uid']);
		/*  login_check will set $_SESSION['uid'] for valid login  */
		if ($msg = login_check($_POST['username'], $_POST['password'])) {
			//  echo "<span style=\"color: #ff0000;\">".$msg."</span> ";
			unset($_SESSION['username']);
			}
		}
	if (isset($_GET['username']) && isset($_GET['password'])) {  // new AJAX form
//	if (isset($username)) {  //  new AJAX form
		unset($_SESSION['uid']);
		/*  login_check will set $_SESSION['uid'] for valid login  */
		if ($msg = login_check($_GET['username'], $_GET['password'])) {
			//  echo "<span style=\"color: #ff0000;\">".$msg."</span> ";
			unset($_SESSION['username']);
			}
		}
	if (isset($_SESSION['uid'])) {
		echo "\n<form method=\"POST\" action=\"\" name=\"login\" style=\"margin: 0px;\">";
		echo $_SESSION['username']." ";
		echo "<input name=\"logout\" value=\"yes\" type=\"hidden\">";
		echo " <a href=\"javascript:head_logout();\">logout</a>";
		echo "\n</form>";
		return (1);
		}
//	echo "<form style=\"margin: 0px;\">[ loading ... ]";
//	echo "</form>";
//	document.getElementById("newTotal").value = your_new_total;
//http://stackoverflow.com/questions/3134819/how-do-i-call-a-js-function-on-pressing-enter-key
//onkeypress="return handleEnter(event, update_field(this));
//function handleEnter(e, func){
//    if (e.keyCode == 13 || e.which == 13)
//        //Enter was pressed, handle it here
//}
	echo "\n<form method=\"POST\" action=\"\" name=\"login\" style=\"margin: 0px;\">";
	if (isset($msg))
		echo "<span style=\"color: #ff0000;\">".$msg."</span> ";
	echo "<a href=\"javascript: formpop('signup');\">signup</a> ";

	echo "\n<input size=12               name=\"username\" id=\"username\" style=\"font-size: 10px; border: 1px solid;\"";
	echo "  value=\"username\" onKeyPress=\"detectKeyLogin(event)\"";
	echo " onfocus=\"if(this.value == 'username') {this.value = '';}\"";
	echo " onblur=\"if(this.value == '') {this.value = 'username';}\">";

	echo "\n<input size=12 type=password name=\"password\" id=\"password\" style=\"font-size: 10px; border: 1px solid;\"";
	echo " value=\"password\" onKeyDown=\"detectKeyLogin(event)\"";
	echo " onfocus=\"if(this.value == 'password') {this.value = '';}\"";
	echo " onblur=\"if(this.value == '') {this.value = 'password';}\">";

	//  echo "\n<a href=\"javascript:head_login('rickatech');\">login</a>";
	echo "\n</form>";
	return (0);
	}
?>
