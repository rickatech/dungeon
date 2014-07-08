<?PHP
function get_map($filename) {
	//  try to import dungeon array
	$row = 0;
	if ($fh = fopen($filename, 'r')) {
		while (($data = fgetcsv($fh, 1000, ",")) !== FALSE) {
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
	echo "<pre>".$map_textpre."</pre>\n";
	}

function put_map($newfile, &$a) {
//	$a[ 0][ 0] = 2;
//	$a[63][ 0] = 4;
//	$a[ 0][63] = 6;
//	$a[63][63] = 8;
	//  try to output dungeon array in format that can be read back into an array later
	if ($fh = fopen($newfile, 'w')) {
		foreach ($a as $av) {
			fputcsv($fh, $av);
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
		return $map;
	}
?>
