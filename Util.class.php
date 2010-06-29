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

        if ((preg_match("#Nav#", getenv("HTTP_USER_AGENT"))) || (preg_match("#Gold#", getenv(
        "HTTP_USER_AGENT"))) ||
        (preg_match("#X11#", getenv("HTTP_USER_AGENT"))) || (preg_match("#Mozilla#", getenv(
        "HTTP_USER_AGENT"))) ||
        (preg_match("#Netscape#", getenv("HTTP_USER_AGENT")))
        AND (!preg_match("#MSIE#", getenv("HTTP_USER_AGENT"))) AND (!preg_match("#Konqueror#", getenv(
        "HTTP_USER_AGENT"))))
          $navigateur = "Netscape";
        elseif (preg_match("#Opera#", getenv("HTTP_USER_AGENT")))
          $navigateur = "Opera";
        elseif (preg_match("#MSIE#", getenv("HTTP_USER_AGENT")))
          $navigateur = "MSIE";
        elseif (preg_match("#Lynx#", getenv("HTTP_USER_AGENT")))
          $navigateur = "Lynx";
        elseif (preg_match("#WebTV#", getenv("HTTP_USER_AGENT")))
          $navigateur = "WebTV";
        elseif (preg_match("#Konqueror#", getenv("HTTP_USER_AGENT")))
          $navigateur = "Konqueror";
        elseif ((preg_match("#bot#", getenv("HTTP_USER_AGENT"))) || (preg_match("#Google#", getenv(
        "HTTP_USER_AGENT"))) ||
        (preg_match("#Slurp#", getenv("HTTP_USER_AGENT"))) || (preg_match("#Scooter#", getenv(
        "HTTP_USER_AGENT"))) ||
        (preg_match("#Spider#", getenv("HTTP_USER_AGENT"))) || (preg_match("#Infoseek#", getenv(
        "HTTP_USER_AGENT"))))
          $navigateur = "Bot";
        else
          $navigateur = "Autre";
        return $navigateur;

    }
}