<?PHP

function isprod() {
	if ($_SERVER['PHP_SELF'] == "/daytimer/index.php")
		return (1);
	return (0);
	}

function pretty($s, $v, $d) {
	printf("<input\n size=".$s." name=\"".$v."\" id=\"".$v."\" value=\"".$d."\" style=\"font-size: 10px; border: 1px solid; margin: 0px;\">");
	}

function dungeon_render() {
	/*  This is fixed!???  Weird: placing form begin/end outside of table cells </td></form> </form><td>
	    renders ok when first loaded, but subsequent ajax load is invisible in Firefox.  */
	//  http://www.quirksmode.org/js/forms.html
//	global $year;
//	global $month;
//	global $day;

	echo "\n<form name=\"form_date\" method=\"get\" style=\"margin: 0px;\"><td>";
	if (isset($_GET['year']))
		pretty(4, "year", $year);  //  never happens
	else
		pretty(4, "year", date('Y'));
	echo "-";
	if (isset($_GET['month']))
		pretty(2, "month", $month);  //  never happens
	else
		pretty(2, "month", date('m'));
	echo "-";
	if (isset($_GET['day']))
		pretty(2, "day", $month);  //  never happens
	else
		pretty(2, "day", date('d'));
	echo "x";
	if (isset($_GET['weeks']))
		pretty(2, "weeks", $weeks);  //  never happens
	else
		pretty(2, "weeks", 5);
	echo "b";
	if (isset($_GET['bill']))
		pretty(1, "bill", $weeks);  //  never happens
	else
		pretty(1, "bill", 1);
	echo "f";
	if (isset($_GET['filter']))
		pretty(1, "filter", $weeks);  //  never happens
	else
		pretty(1, "filter", 0);
	echo "</td></form>";
	printf("\n<td><input type=button value=\"set\" onclick=\"cal_set('calout')\"></td>");
	if ((isset($_SESSION['uid'])) && ($_SESSION['uid'] == "1") && !isprod()) {
		if ((isset($_SESSION['username'])) && ($_SESSION['username'] == "rickatech")) {
			printf("\n<td><input type=button value=\"newmap\" onclick=\"cal_newmap('calout')\"></td>");
			}
		printf("\n<td><input type=button value=\"list\" onclick=\"list_set('calout')\"></td>");
		printf("\n<td><input type=button value=\"to do\" onclick=\"to_do_set('calout')\"></td>");
		printf("\n<td><input type=button value=\"book\" onclick=\"book_set('calout')\"></td>");
		printf("\n<td><input type=button value=\"users\" onclick=\"users_set('calout')\"></td>");
		printf("\n<td><input type=button value=\"prev\" onclick=\"cal_prev('calout')\"></td>");
		}
	echo "\n<td><a href=\"javascript: showtest('calout');\">test</a></td>";
	}

?>
