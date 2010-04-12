<?php
class Util {
	static $nom_fic;
	static function get_page($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_NOBODY, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$page = curl_exec($ch);
		curl_close($ch);
		return $page;
	}
	
	static function init_pct($event) {
		require_once ('Database.class.php');
		$d=new Database();
		$requete='INSERT INTO events (ID_Event,pct) VALUES('.$event.',0)';
		$d->requete($requete);
	}
	
	static function update_pct($event,$pct) {
		require_once ('Database.class.php');
		$d=new Database();
		$requete='UPDATE events SET pct='.$pct.' WHERE ID_Event='.$event;
		$d->requete($requete);
	}
	
	static function start_log($nom) {
		
		ob_start();
		self::$nom_fic=$nom.'.txt';
	}
	
	static function stop_log() {
		$handle = fopen(self::$nom_fic, 'a');
		$tab_debug=ob_get_contents();
		ob_end_clean();
		fwrite($handle, $tab_debug);
		fclose($handle);
	}
}