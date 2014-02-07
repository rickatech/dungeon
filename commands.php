<?PHP
if ((isset($_GET["authcode"])) && ($_GET["authcode"] == '1248')) {
	$authcode = $_GET["authcode"];
	}
echo "cmd: ".$_GET["cmd"]."\n<br>";
echo "map: ".$_GET["map"]."\n<br>";

if ((isset($_GET["cmd"])) && ($_GET["cmd"] == 'map new')) {
	$map_gen = 1;
	}
else
	$map_gen = 0;

$map_textpre = NULL;
if ($map_gen == 1) {
	for ($y = 1; $y < 64; $y++) {
		for ($x = 1; $x < 64; $x++) {
			$map[$x][$y] = rand(0, 1);
			$map_textpre .= $map[$x][$y];
			}
		$map_textpre .= "\n";
		}
	}

?>
