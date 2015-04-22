<?PHP 

function render($v, $message = NULL, $b = 0, $or = 0, $near = array()) {
	/*  this may be client side javascript at some point?  */
	global $w;
	global $z;
	global $bonk;
	//  adjust border color to hint at presence of nearby walls
	$bs  = $b & 1 ? 'border-top: solid 4px;' :       'border-top: solid 4px; border-top-color: #9FFF9F;';
	$bs .= $b & 2 ? 'border-left: solid 4px;' :     'border-left: solid 4px; border-left-color: #9FFF9F;';
	$bs .= $b & 4 ? 'border-right: solid 4px;' :   'border-right: solid 4px; border-right-color: #9FFF9F;';
	$bs .= $b & 8 ? 'border-bottom: solid 4px;' : 'border-bottom: solid 4px; border-bottom-color: #9FFF9F;';
	$r0 = $w[$v][0];
	$r1 = $w[$v][1];
	$r2 = $w[$v][2];
	if (!$bonk) {
		//  show other player!!!
		if (isset($near['ooo'])) {
			$r1[2] = $near['ooo'];  }
		if (isset($near['oo'])) {
			$r1[3] = $near['oo'];  }
		if (isset($near['n'])) {
			$r1[0] = $near['n']; $r1[1] = $near['n'];
			$r2[0] = $near['n']; $r2[1] = $near['n']; }
		if (isset($near['o'])) {
			$r1[2] = $near['o']; $r1[3] = $near['o'];
			$r2[2] = $near['o']; $r2[3] = $near['o']; }
		if (isset($near['p'])) {
			$r1[4] = $near['p']; $r1[5] = $near['p'];
			$r2[4] = $near['p']; $r2[5] = $near['p']; }
		}
	//  change background based on player orientation, FUTURE: 3D background map
	if ($or < 90)
		$bg_tc = '#ffffff';  //  'south', bright
	else if ($or < 180)
		$bg_tc = '#ffefff';  //  'west', purple
	else if ($or < 270)
		$bg_tc = '#dfdfff';  //  'north', bluish/periwinkle/cyan
	else
		$bg_tc = '#efffff';  //  'east', green/mint
	echo "<center><table style=\"margin: auto;  background: ".$bg_tc."\"><tr>\n<td id=\"rentab\" style=\"".$bs."\">\n";
	//  echo "<center><table style=\"margin: auto;  background: #ffffff;\"><tr>\n<td id=\"rentab\" style=\"".$bs."\">\n";
	printf("<pre style=\"font-size: 72px; margin-bottom: 0px;\">%s\n%s\n%s</pre>",
	    $r0, $r1, $r2);
	echo "</td>\n</tr></table>\n";
	if ($message)
		echo "\n".$message."\n";
	echo "</center>";
	}

function stepwrap($v, $m, $s) {
	// torus 'wrap around' world baby!
	// v current value
	// m max, wrap around value
	// s step amount, assumed less than m
	if      ($v + $s < 0)        $r = $v + $s + $m;
	else if ($v + $s > ($m - 1)) $r = $v - $m + $s;
	else 	                     $r = $v + $s;
	return ($r);
	//  what if s > m?
	}

?>
