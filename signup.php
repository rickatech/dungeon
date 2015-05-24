<?PHP	date_default_timezone_set('America/Los_Angeles');  // otherwise PHP warnings
	include "config.php";  ?>
<html>
<head>
<TITLE>Signup</TITLE><?PHP
        //  a_viewport = "width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui";
        $meta_viewport = "width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no";
	echo "\n<meta name=\"viewport\" content=\"".$meta_viewport."\">";  ?>
</head><?PHP

if (isset($isprod) && $isprod) {
	echo "<body>";
	}
else {
	echo "<body style=\"background: #ffdfdf;\">";
	}

echo "\n<body><b>Signup!</b>\n";

if (!isset($_POST['email'])) {
        /*  arrive before submit is clicked  */
	echo "<table style0=\"margin-bottom: 2em;\"><tr>";
	echo "\n<form method=\"post\">";
	echo "\n<td>Email:</td>";
	echo "\n<td><input name=\"email\" type=\"text\" /></td>";
	echo "\n<tr></tr>";
	echo "\n<td>Message:</td>";
	echo "\n<td><textarea name=\"message\" rows=\"4\" cols=\"40\"></textarea></td>";
	echo "\n</tr><tr>";
	echo "\n<td></td>";
	echo "\n<td><input type=\"submit\" />";
	echo "\n<p>".$signup_msg."</p>\n\n";
	echo "</td>";
	echo "\n</form></tr></table>";
	}

else {
        /*  arrive here after submit is clicked  */
	$email = $_REQUEST['email'] ;
	$message = $_REQUEST['message'] ;

	mail("Signup! <dungeon-signup@zaptech.com>", "Daytimer - Signup!", $message, "From: $email" );
//	header( "Location: http://www.papso.com/thanks/" );
	echo "<p><b>Thanks!</b></p>";
	}
?>

</body>
</html>

