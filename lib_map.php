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
				$new_map['size'][1] = $data[1];
				$new_map['size'][2] = $data[2];
				}
			else if ($data[0] == 'user') {
				//  then add extra field for chat name
				$new_map['user'][$data[1]]['handle'] = $data[2];
				$new_map['user'][$data[1]]['x'] =      $data[3];
				$new_map['user'][$data[1]]['y'] =      $data[4];
				$new_map['user'][$data[1]]['yaw'] =    $data[5];
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
		echo "<p>fopen read error </p> \n\n";
		return (NULL);
		}
	}

function print_map($m) {
	$map_textpre = NULL;
	for ($y = 0; $y < 64; $y++) {
		for ($x = 0; $x < 64; $x++) {
			$map_textpre .= $m[$x][$y];
			}
		$map_textpre .= "\n";
		}
	echo "<pre>".$map_textpre;
	//  echo print_r($m);
	echo "</pre>\n";
	}

function put_map($newfile, &$a) {
//	$a[ 0][ 0] = 2;
//	$a[63][ 0] = 4;
//	$a[ 0][63] = 6;
//	$a[63][63] = 8;
	//  try to output dungeon array in format that can be read back into an array later
	if ($fh = fopen($newfile, 'w')) {
		foreach ($a as $ak => $av) {
			if ($ak === 'user') {
				foreach ($av as $bv) {
					$user = array('user', key($av), $bv['handle'],
					  $bv['x'], $bv['y'], $bv['yaw']);
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
	// $map  passed in by reference
	for ($y = 0; $y < 64; $y++) {
		for ($x = 0; $x < 64; $x++) {
			$map[$x][$y] = rand(0, 1);
			}
		}
	$map['tick'][0] = 'tick';
	$map['tick'][1] = 0;
	$map['size'][0] = 'size';
	$map['size'][1] = 64;
	$map['size'][2] = 64;
	//  user lines appended elsewhere
	return $map;
	}
?>
