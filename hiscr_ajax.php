<?PHP	include "session.php";  // identical session code used for daytimer?

/*  This 'plug-in' generates output for dv_hiscore div from AJAX call.

/*  Invalid session okay here since login prompt if no user is detected  */
if (isset($_GET['ajax']))
	/*  0 render full cal div, 1 render just the calout div, unset ajax disabled  */
	$ajax = $_GET['ajax'];  //  this isn't confirmation of valid login, see elsewhere for that
else {
	echo "[ ajax disabled ]";
	return;
	}

echo "\n<p style=\"margin-top: 0px;\">high score test pattern</p> \n";
?>
