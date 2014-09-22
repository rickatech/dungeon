<?PHP	date_default_timezone_set('America/Los_Angeles');  // otherwise PHP warnings
	include "session.php";  // identical session code used for daytimer?

/*  This 'plug-in' generates output for calout div from AJAX call.

/*  FUTURE
  - use case: user logged in and active, steps away from computer, back after an hour, clicks a command
    javascript timer to idle screen, reset session?  server side, invalid session after x minutes
    should refresh display, ignore command, indicate your next command will be ohonored if you don't step away again  */

/*  Invalid session okay here since login prompt if no user is detected  */
if (isset($_GET['ajax']))
	/*  0 render full cal div, 1 render just the calout div, unset ajax disabled  */
	$ajax = $_GET['ajax'];
else {
	echo "[ ajax disabled ]";
	return;
	}

/*  display: none, block  visibility: hidden, visible  */
//if ($ajax == 0) {
//	echo "<a href=\"javascript: formpop('signup');\">sign p</a> ";
//	echo "\n<form method=\"POST\" action=\"\" name=\"login\" style=\"margin: 0px;\">";
        login_state();
//	echo "\n</form>";
//	return;
//	}

//login_state();
