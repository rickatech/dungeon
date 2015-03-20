<?PHP	date_default_timezone_set('America/Los_Angeles');  // otherwise PHP warnings
	include "session.php";  // identical session code used for daytimer?

/*  This 'plug-in' generates output for calnav div from AJAX call.

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
if ($ajax == 0) {
//	echo "\n\n<!--  calnav  --><div id=\"calnav\">";
//	echo "[ calnav ]</div><!--  calnav  -->";
//	return;
	}

echo "<table border=0\n  cellspacing=0 cellpadding=0 style=\"margin-left: auto; margin-right: auto;\";><tr>";
//  dungeon_render();

//echo "\n<td> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </td>";  //  white space here to balance reset below
if (isset($_GET['nav'])) {  //  nav=2 only for now, yes?
	if (isset($_SESSION['uid_dg'])) {
		//  http://www.elated.com/articles/html-checkboxes-radio-buttons-select-form-fields/
		//  show nearby objects radio buttons, followed by actions drop down
		//  Actions
		echo "<td><select id=\"toy\" name=\"toy\" style=\"display0: inline;\">";
		echo "  <optgroup label=\"Actions\">";
		echo "  <option>tag</option>";
		echo "  <option disabled=disabled>dungeon</option>";
		echo "  <option disabled=disabled>give up</option>";
		echo "  <option disabled=disabled>new map</option>";
		echo "  <option disabled=disabled>pass</option>";
		echo "  </optgroup>";
		echo "</select>";
		//  Targets, not all action need targets
echo "\n<br><input type=\"radio\" name=\"favourite_colour\" value=\"red\" checked> henauker
<br><input type=\"radio\" name=\"favourite_colour\" value=\"orange\"> Orange
<br><input type=\"radio\" name=\"favourite_colour\" value=\"pink\"> Pink ";
//	echo "\n<br><input type=button value=\"tag henauker\" onclick=\"showactive('rentab'); cal_set('calout');\">".rand(0, 4);."</td>";
	echo "\n<br><input type=button value=\"action\" onclick=\"showactive('rentab'); cal_set('calout');\">".$_GET['nav'].mt_rand()."</td>";
		}
	}
	
else {  //  @@@@
	printf("\n<td><input type=button value=\"refresh\" onclick=\"showactive('rentab'); cal_set('calout');\"></td>");
//	printf("\n<td><input type=button value=\"gong\" onclick=\"snd.play();\"></td>");
	if (isset($_SESSION['uid_dg'])) {
		printf("\n<td><input type=button value=\".o&nbsp;\"      onclick=\"showactive('rentab'); nav_turnleft('calout');\">");
		printf("\n<br><input type=button value=\"<-\"            onclick=\"showactive('rentab'); nav_stepleft('calout');\"></td>");
		printf("\n<td><input type=button value=\"&nbsp;^&nbsp;\" onclick=\"showactive('rentab'); nav_stepforw('calout');\">");
		printf("\n<br><input type=button value=\"&nbsp;v&nbsp;\" onclick=\"showactive('rentab'); nav_stepback('calout');\"></td>");
		printf("\n<td><input type=button value=\"&nbsp;o.\"      onclick=\"showactive('rentab'); nav_turnrght('calout');\">");
		printf("\n<br><input type=button value=\"->\"            onclick=\"showactive('rentab'); nav_steprght('calout');\"></td>");
		echo "\n  <td><input id=\"newmap\"  type=button value=\"newmap\"";
		echo "\n    disabled=false onclick=\"showactive('rentab'); cal_newmap('calout');\"  style=\"display: none;\"></td>";
		echo "\n  <td><input id=\"dungeon\" type=button value=\"dungeon\"";
		echo "\n    disabled=false onclick=\"showactive('rentab'); cal_dungeon('calout');\" style=\"display: none;\"></td>";
		echo "\n  <td><input id=\"give_up\" type=button value=\"give_up\"";
		echo "\n    disabled=false onclick=\"showactive('rentab'); cal_giveup('calout');\"  style=\"display: none;\"></td>";
		//  if ($_SESSION['uid_dg'] == "1") {
		//	if ((isset($_SESSION['username_dg'])) && ($_SESSION['username'] == "rickatech"))
		//	}
		/*  FUTURE: need hint so high scores ajax knows what dungeon to report for  */
		/*          when player moves from one map to another, perhaps stash dungeon name in DOM element?  */
		echo "\n<td>&nbsp;<a href=\"javascript: showactive('rentab');  showtest('calout', 'dungeon');\">reset</a></td>";
		}
	else {
		//  FUTURE: &nbsp;<a href=\"javascript: showactive('rentab');  showtest('calout', 'user00000001');\">reset</a></td>";
		echo "\n<td>&nbsp;<a href=\"javascript: showactive('rentab');  showtest('calout');\">reset</a></td>";
		}

	}  //  @@@@
echo "</tr></table>\n";


if (($debug_mask & DEBUG_ADM) && isset($_SESSION['uid_dg']) && $_SESSION['uid_dg'] == 1) {  // admin/rickatech check
       if (!isset($msg)) $msg ='';
       $msg .= "\n<span style=\"font-size: smaller; color: #ff0000;\">".$_SERVER['REQUEST_URI']."</span>";
       echo "\n".$msg."\n";
	}
?>
