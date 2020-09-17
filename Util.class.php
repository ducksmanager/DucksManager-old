<?php
if (!isset($no_database)) {
    require_once('Database.class.php');
}

class Util {
    public const DOMAIN='ducksmanager.net';
    static function isLocalHost() {
        return !isset($_ENV['ENV']) || $_ENV['ENV'] !== 'production';
    }

    static function magazinesSupprimesInducks() {
        $requete_magazines='SELECT Pays, Magazine FROM numeros GROUP BY Pays, Magazine ORDER BY Pays, Magazine';
        $resultat_magazines=DM_Core::$d->requete($requete_magazines);
        $pays='';
        $magazines_inducks= [];
        foreach($resultat_magazines as $pays_magazine) {
            if ($pays!==$pays_magazine['Pays']) {
                $magazines_inducks=Inducks::get_liste_magazines($pays_magazine['Pays']);
            }
            if (!array_key_exists($pays_magazine['Magazine'], $magazines_inducks)) {
                echo $pays_magazine['Pays'] . '/' . $pays_magazine['Magazine'] . ' n\'existe plus<br />';
            }
            $pays=$pays_magazine['Pays'];
        }
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

    static function exit_if_not_logged_in() {
        if (!isset($_SESSION['user'], $_SESSION['id_user'])) {
            header('Location: https://'.self::DOMAIN);
            exit(0);
        }
    }
}

if (isset($_GET['magazines_supprimes'])) {
    Util::magazinesSupprimesInducks();
}
