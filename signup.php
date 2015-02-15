<html>
<head>
<TITLE>Signup</TITLE>
</head>
<body><b>Signup!</b>

<?PHP

if (!isset($_POST['email'])) {
        /*  arrive before submit is clicked  */
	echo "<table style=\"margin-bottom: 2em;\"><tr>";
	echo "\n<form method=\"post\">";
	echo "\n<td>Email:</td>";
	echo "\n<td><input name=\"email\" type=\"text\" /></td>";
	echo "\n<tr></tr>";
	echo "\n<td>Message:</td>";
	echo "\n<td><textarea name=\"message\" rows=\"4\" cols=\"40\"></textarea></td>";
	echo "\n</tr><tr>";
	echo "\n<td></td>";
	echo "\n<td><input type=\"submit\" /></td>";
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

