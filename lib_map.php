<?PHP
function get_map($filename) {
	//  try to import dungeon array
	$row = 0;
	$user = 0;
	if ($fh = fopen($filename, 'r')) {
		while (($data = fgetcsv($fh, 1000, ",")) !== FALSE) {
			//  echo "\ndata[0]: ".$data[0]."\n";
			if ($data[0] == 'tick') {
				$new_map['tick'][0] = 'tick';
				$new_map['tick'][1] = $data[1];
				}
			else if ($data[0] == 'size') {
				$new_map['size'][0] = 'size';
				$new_map['size'][1] = $data[1];  //  x
				$new_map['size'][2] = $data[2];  //  y
				}
			else if ($data[0] == 'away') {
				$new_map['away'][0] = 'away';
				$new_map['away'][1] = $data[1];  //  uid
				$new_map['away'][2] = $data[2];  //  name
				$new_map['away'][3] = $data[3];  //  map
				}
			else if ($data[0] == 'left') {
				//  FUTURE use named attibutes above?
				$new_map['left'][$data[1]]['handle'] = $data[2];  //  name
				$new_map['left'][$data[1]]['x'] =      $data[3];  //  x
				$new_map['left'][$data[1]]['y'] =      $data[4];  //  y
				$new_map['left'][$data[1]]['yaw'] =    $data[5];  //  yaw
				if (isset($data[6]))
					$new_map['left'][$data[1]]['hit'] =    $data[6];
				//$new_map['left'][0] = 'left';
				//$new_map['left'][1] = $data[1];  //  uid
				//$new_map['left'][2] = $data[2];  //  name
				//$new_map['left'][3] = $data[3];  //  x
				//$new_map['left'][4] = $data[4];  //  y
				//$new_map['left'][5] = $data[5];  //  yaw
				}
			else if ($data[0] == 'user') {
				//  then add extra field for chat name, FUTURE use named attibutes above?
				$new_map['user'][$data[1]]['handle'] = $data[2];  //  name
				$new_map['user'][$data[1]]['x'] =      $data[3];  //  x
				$new_map['user'][$data[1]]['y'] =      $data[4];  //  y
				$new_map['user'][$data[1]]['yaw'] =    $data[5];  //  yaw
				if (isset($data[6]))
					$new_map['user'][$data[1]]['hit'] =    $data[6];
				$user++;
				}
			else
				$new_map[$row] = $data;
			$row++;
			}
		fclose($fh);
		return ($new_map);
		}
	else {
		//  echo "<p>fopen read error </p> \n\n";  //  FUTURE, make a log file entry for this?
		return (NULL);
		}
	}

function print_map(&$m) {
	$map_textpre = NULL;
	$mx = $m['size'][1];
	$my = $m['size'][2];
	for ($y = 0; $y < $my; $y++) {
		for ($x = 0; $x < $mx; $x++) {
			$o = ord($m[$x][$y]);
			if ($o == 48)
				$map_textpre .= '&nbsp;';
			else 
				$map_textpre .= $m[$x][$y];
			}
		$map_textpre .= "\n";
		}
	echo "<pre>".$map_textpre;
	//  echo print_r($m);
	echo "</pre>\n";
	}

function put_map($newfile, &$a) {
	global $debug_mask;
//	$a[ 0][ 0] = 2;
//	$a[63][ 0] = 4;
//	$a[ 0][63] = 6;
//	$a[63][63] = 8;
	//  try to output dungeon array in format that can be read back into an array later
	if ($fh = fopen($newfile, 'w')) {
		foreach ($a as $ak => $av) {
			if ($ak === 'user') {
				//  foreach ($av as $bv) {
				foreach ($av as $bk => $bv) {
					if ($debug_mask & DEBUG_FOO) echo "<br>".$bk;
					//  $user = array('user', key($av), $bv['handle'],
					$user = array('user', $bk, $bv['handle'],
					  $bv['x'], $bv['y'], $bv['yaw'], isset($bv['hit']) ? $bv['hit'] : 0);
					next($av);
					fputcsv($fh, $user);
					}
				}
			else if ($ak === 'left') {
				foreach ($av as $bv) {
					$user = array('left', key($av), $bv['handle'],
					  $bv['x'], $bv['y'], $bv['yaw'], isset($bv['hit']) ? $bv['hit'] : 0);
					next($av);
					fputcsv($fh, $user);
					}
				}
			else {
				fputcsv($fh, $av);
				}
			}
		fclose($fh);
        	return true;
		}
	return false;
	}

function gen_map(&$map) {
	//  $map  reference passed in
	$mx = 16;
	$my = 16;
	// $map  passed in by reference
	for ($y = 0; $y < $my; $y++) {
		for ($x = 0; $x < $mx; $x++) {
			//  very crude, 33% blocks for now, FUTURE: room, cavern allgythms?
			if ($r = rand(0, 2) > 1 )
				$map[$x][$y] = 1;
			else $map[$x][$y] = 0;
			}
		}
	$map['tick'][0] = 'tick';
	$map['tick'][1] = 0;
	$map['size'][0] = 'size';
	$map['size'][1] = $mx;
	$map['size'][2] = $my;
	//  user lines appended elsewhere
	//  return $map;
	}
?>
