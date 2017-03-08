<?php
if (!isset($no_database))
	require_once('Database.class.php');

class Util {
	static $nom_fic;
	static function get_page($url, $timeout = 0) {
		if ($timeout > 0) {
			$context = stream_context_create( [
				'http'=> [
					'timeout' => $timeout
				]
			]);
			$handle = @fopen($url, "r", null, $context);
		}
		else {
			$handle = @fopen($url, "r");
		}

		if (isset($_GET['dbg'])) {
			echo $url;
		}
		if ($handle) {
			$buffer="";
			while (!feof($handle)) {
				$buffer.= fgets($handle, 4096);
			}
			fclose($handle);
			return $buffer;
		}
		else {
			return ERREUR_CONNEXION_INDUCKS;
		}
	}
	
	static function get_secured_page(ServeurCoa $coaServer, $url, $timeout, $dbg) {
		$baseUrl = $coaServer->getUrl().'/'.$coaServer->web_root;
		$fullUrl = $baseUrl.'/'.$url.'&mdp='.sha1($coaServer->db_password);
		if ($dbg) {
			echo $fullUrl.'<br /><br />';
		}
		return Util::get_page($fullUrl, $timeout);
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

		if (preg_match("#android#i", getenv("HTTP_USER_AGENT")))
		  $navigateur = "Android";
		elseif ((preg_match("#Nav#", getenv("HTTP_USER_AGENT"))) || (preg_match("#Gold#", getenv(
		"HTTP_USER_AGENT"))) ||
		(preg_match("#X11#", getenv("HTTP_USER_AGENT"))) || (preg_match("#Mozilla#", getenv(
		"HTTP_USER_AGENT"))) ||
		(preg_match("#Netscape#", getenv("HTTP_USER_AGENT")))
		AND (!preg_match("#MSIE#", getenv("HTTP_USER_AGENT"))) AND (!preg_match("#Konqueror#", getenv(
		"HTTP_USER_AGENT"))))
		  $navigateur = "Netscape";
		elseif (preg_match("#Opera#", getenv("HTTP_USER_AGENT")))
		  $navigateur = "Opera";
		elseif (preg_match("#MSIE 9#", getenv("HTTP_USER_AGENT")))
		  $navigateur = "MSIE 9";
		elseif (preg_match("#MSIE#", getenv("HTTP_USER_AGENT")))
		  $navigateur = "MSIE<9";
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

	static function isLocalHost() {
		return !(isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'],'localhost')===false);
	}

	static function magazinesSupprimesInducks() {
		$requete_magazines='SELECT Pays, Magazine FROM numeros GROUP BY Pays, Magazine ORDER BY Pays, Magazine';
		$resultat_magazines=DM_Core::$d->requete_select($requete_magazines);
		$pays='';
		$magazines_inducks= [];
		foreach($resultat_magazines as $pays_magazine) {
			if ($pays!==$pays_magazine['Pays']) {
				$magazines_inducks=Inducks::get_liste_magazines($pays_magazine['Pays']);
			}
			if (!array_key_exists($pays_magazine['Magazine'], $magazines_inducks))
				echo $pays_magazine['Pays'].'/'.$pays_magazine['Magazine'].' n\'existe plus<br />';
			$pays=$pays_magazine['Pays'];
		}
	}
	
	static function supprimerAccents($str) {
		return( strtr( $str,"�����������������������������������������������������",
							"AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn" ) );
	}
	
	static function remplacerNiemeCaractere($n, $caractere, $remplacement, $chaine) {
		$result = preg_split('#('.$caractere.')#',$chaine,$n,PREG_SPLIT_DELIM_CAPTURE);
		array_push($result,preg_replace('#'.$caractere.'#',$remplacement,array_pop($result),1));
		return implode($result);
	}
	
	static function lire_depuis_fichier($nom_fichier) {
		$inF = fopen($nom_fichier,"r");
		$str='';
		if ($inF === false) {
			echo 'Le fichier '.$nom_fichier.' n\'existe pas';
		}
		else {
			while (!feof($inF)) {
				$str.=fgets($inF, 4096);
			} 
		}
		return $str;
	}
	
	static function ecrire_dans_fichier($nom_fichier,$str,$a_la_suite=false) {
		$inF = fopen($nom_fichier,$a_la_suite ? 'a+' : 'w');
		fputs($inF,$str); 
		fclose($inF);
	}
	
	static function exit_if_not_logged_in() {
		if (!isset($_SESSION['user'])) {
			header('Location: http://www.ducksmanager.net');
			exit(0);
		}
	}

    static function get_random_string($length = 16) {
        $validCharacters = "abcdefghijklmnopqrstuxyvwzABCDEFGHIJKLMNOPQRSTUXYVWZ";
        $validCharNumber = strlen($validCharacters);
        $result = "";
        for ($i = 0; $i < $length; $i++) {
            $result.=$validCharacters[mt_rand(0, $validCharNumber - 1)];
        }

        return $result;
    }

	/**
	 * @param $destination string
	 * @param $sourceObject stdClass
	 * @return stdClass
     */
	static function cast($destination, $sourceObject)
	{
		if (is_string($destination)) {
			$destination = new $destination();
		}
		$sourceReflection = new ReflectionObject($sourceObject);
		$destinationReflection = new ReflectionObject($destination);
		$sourceProperties = $sourceReflection->getProperties();
		foreach ($sourceProperties as $sourceProperty) {
			$sourceProperty->setAccessible(true);
			$name = $sourceProperty->getName();
			$value = $sourceProperty->getValue($sourceObject);
			if ($destinationReflection->hasProperty($name)) {
				$propDest = $destinationReflection->getProperty($name);
				$propDest->setAccessible(true);
				$propDest->setValue($destination,$value);
			} else {
				$destination->$name = $value;
			}
		}
		return $destination;
	}

    static function get_query_results_from_remote(ServeurCoa $coaServer, $query, $db) {
	    $parameters = [
            'query' => $query,
            'db' => $db
        ];
        return self::get_service_results($coaServer, 'POST', '/rawsql', 'rawsql', $parameters);
    }

    /**
     * @param ServeurCoa $coaServer
     * @param $method
     * @param $path
     * @param string $role
     * @param array $parameters
     * @return mixed|null
     */
    public static function get_service_results(ServeurCoa $coaServer, $method, $path, $role, $parameters = [])
    {
        $ch = curl_init();
        $url = $coaServer->getUrl() . '/' . $coaServer->web_root . $path;

        switch($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
            break;
            case 'GET':
                if (count($parameters) > 0) {
                    $url .= '/' . implode('/', $parameters);
                }
            break;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $method === 'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $headers = [
            'Authorization: Basic ' . base64_encode(implode(':', [$role, $coaServer->role_passwords[$role]])),
            'Content-Type: application/x-www-form-urlencoded',
            'Cache-Control: no-cache',
            'x-dm-version: 1.0',
        ];
        if (isset($_COOKIE['user'])) {
            $headers[] = 'x-dm-user: ' . $_COOKIE['user'];
            $headers[] = 'x-dm-pass: ' . $_COOKIE['pass'];
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $buffer = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!empty($buffer) && $responseCode >= 200 && $responseCode < 300) {
            $results = json_decode($buffer, true);
            if (is_array($results)) {
                return $results;
            }
        }
        return [];
    }

}

if (isset($_GET['magazines_supprimes'])) {
	Util::magazinesSupprimesInducks();
}