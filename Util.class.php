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

    static function getBrowser() {

        if ((ereg("Nav", getenv("HTTP_USER_AGENT"))) || (ereg("Gold", getenv(
        "HTTP_USER_AGENT"))) ||
        (ereg("X11", getenv("HTTP_USER_AGENT"))) || (ereg("Mozilla", getenv(
        "HTTP_USER_AGENT"))) ||
        (ereg("Netscape", getenv("HTTP_USER_AGENT")))
        AND (!ereg("MSIE", getenv("HTTP_USER_AGENT"))) AND (!ereg("Konqueror", getenv(
        "HTTP_USER_AGENT"))))
          $navigateur = "Netscape";
        elseif (ereg("Opera", getenv("HTTP_USER_AGENT")))
          $navigateur = "Opera";
        elseif (ereg("MSIE", getenv("HTTP_USER_AGENT")))
          $navigateur = "MSIE";
        elseif (ereg("Lynx", getenv("HTTP_USER_AGENT")))
          $navigateur = "Lynx";
        elseif (ereg("WebTV", getenv("HTTP_USER_AGENT")))
          $navigateur = "WebTV";
        elseif (ereg("Konqueror", getenv("HTTP_USER_AGENT")))
          $navigateur = "Konqueror";
        elseif ((eregi("bot", getenv("HTTP_USER_AGENT"))) || (ereg("Google", getenv(
        "HTTP_USER_AGENT"))) ||
        (ereg("Slurp", getenv("HTTP_USER_AGENT"))) || (ereg("Scooter", getenv(
        "HTTP_USER_AGENT"))) ||
        (eregi("Spider", getenv("HTTP_USER_AGENT"))) || (eregi("Infoseek", getenv(
        "HTTP_USER_AGENT"))))
          $navigateur = "Bot";
        else
          $navigateur = "Autre";
        return $navigateur;

    }
}