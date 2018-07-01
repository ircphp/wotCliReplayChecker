<?php
require_once ('wotReaplayReader.php');
define('TM_SLP', 3);

if (isset($argv[1])) {
	$i = 0;
	$fcount = 0;
	$replays = array();
	$dir = dirname(str_replace('\\', '/', $argv[1]));
	while (true) {
		usleep(TM_SLP * 1000000);

		$files = scandir($dir);
		$cnt = count($files);
		$time = time();
		if ($fcount > $cnt) {
			$fcount = $cnt;
			$i--;
		} elseif ($fcount < $cnt) foreach ($files as $fl) if ($fl != '.' && $fl != '..' && $fl != 'temp.wotreplay' && !isset($replays[$fl])) {


			$fl_full = $dir.'/'.$fl;
			$mtime = filemtime($fl_full);

			if ($time - $mtime > TM_SLP) {
				$fcount = $cnt;
				$replays[$fl] = $mtime;

				$i++;
				echo "\n".str_pad($i, 3, ' ', STR_PAD_LEFT).' '.str_pad('', 20, '_').' '
					.str_pad($fl.' ', 80, '_')."\n";
				echo check_pepl_file($fl_full) ? '' : "\tERROR!\n";
			}

		}

	}
}

function check_pepl_file($file) {
	if ($wot_rd = new \wotReaplayReader($file)) {
		if ($data_str = $wot_rd->read()) {
			//$GLOBALS['wrl_data_obj'] = json_decode($data_str, true);
			if ($data_str = $wot_rd->read()) {
				unset($wot_rd);

				$GLOBALS['wrl_data_obj'] = json_decode($data_str, true);

				if ($GLOBALS['wrl_data_obj'][0]['common']['winnerTeam'] == 2)
					echo "\t\t";

				echo "\t\t\t\t\t  [ W I N ]\n";

				$teams = array();
				foreach ($GLOBALS['wrl_data_obj'][1] as $pl_id => $pl_data)
					$teams[$pl_data['team']][] = $pl_id;

				$lvl = 0;
				while (!empty($teams[1][$lvl]) || !empty($teams[2][$lvl])) {
					$full = false;

					if (empty($teams[1][$lvl])) {
						echo "\t\t\t\t\t\t\t";
					}
					else {
						$full = true;
						echo_data($teams[1][$lvl]);
					}

					echo '    |';

					if (empty($teams[2][$lvl])) {

					}
					else {
						$full = true;
						echo_data($teams[2][$lvl]);
					}

					echo "\n";
					$lvl++;
				}
				return $full;
			}
		}
	}
	return false;
}

function echo_data($pl_id) {
	$u = &$GLOBALS['wrl_data_obj'][1][$pl_id];
	echo
		($u['isTeamKiller'] ? '* ' : '    ').
		($u['isAlive'] ? '+ ' : '- ').
		str_pad(substr($u['name'], 0, 15), 15, ' ').' '.
		str_pad(substr(str_replace('_', ' ', substr($u['vehicleType'], strpos($u['vehicleType'], '_')+1)), 0, 20), 20, ' ', STR_PAD_LEFT).
		str_pad($GLOBALS['wrl_data_obj'][0]['vehicles'][$pl_id][0]['kills'], 3, ' ', STR_PAD_LEFT).
		str_pad($GLOBALS['wrl_data_obj'][0]['vehicles'][$pl_id][0]['damageDealt'], 5, ' ', STR_PAD_LEFT);
}