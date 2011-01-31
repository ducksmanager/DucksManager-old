<?php
require_once ('Database.class.php');

class Util {
    static $nom_fic;
    static function get_page($url,$essai=0) {
        /*if (extension_loaded('curl')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_NOBODY, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_ENCODING, "gzip");
            $page = curl_exec($ch);
            curl_close($ch);
            return $page;
        }
        else */{
            $handle = @fopen($url, "r");
            if ($handle) {
                $buffer="";
                while (!feof($handle)) {
                    $buffer.= fgets($handle, 4096);
                }
                fclose($handle);
                return $buffer;
            }
            else return ERREUR_CONNEXION_INDUCKS;
        }
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

    static function isLocalHost() {
        return !(isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'],'localhost')===false);
    }

    static function magazinesSupprimesInducks() {
        $requete_magazines='SELECT Pays, Magazine FROM numeros GROUP BY Pays, Magazine ORDER BY Pays, Magazine';
        $resultat_magazines=DM_Core::$d->requete_select($requete_magazines);
        $pays='';
        $magazines_inducks=array();
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
        return( strtr( $str,"ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ",
                            "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn" ) );
    }
    
    static function lire_depuis_fichier($nom_fichier) {
        $inF = fopen($nom_fichier,"r");
        $str='';
        while (!feof($inF)) {
            $str.=fgets($inF, 4096);
        } 
        return $str;
    }
    
    static function ecrire_dans_fichier($nom_fichier,$str) {
        $inF = fopen($nom_fichier,"w");
        fputs($inF,$str); 
        fclose($inF);
    }
}

if (isset($_GET['magazines_supprimes'])) {
    Util::magazinesSupprimesInducks();
}