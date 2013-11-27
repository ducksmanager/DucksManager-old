<?php
/**
 * Description of Magazine
 *
 * @author Bruno
 */
include_once('DucksManager_Core.class.php');
include_once('Util.class.php');

class Magazine extends DM_Core{
    var $pays_abrege;
    var $nom_abrege;
    var $nom_complet;
    var $redirige_depuis=null; // Facultatif
    var $ne_parait_plus=null;
    
    function Magazine() {
        parent::__construct();
    }

    function get_cle() {
        return array($this->pays_abrege,$this->nom_abrege,$this->redirige_depuis);
    }

    static function updateList() {
        global $isv_publication;
        global $isv_publicationcategory;
        global $isv_redirection_magazine;

        $liste_magazines_publicationcode=DM_Core::multipleConstructFromIsv('Magazine',$isv_publication);
        $liste_magazines_category=DM_Core::multipleConstructFromIsv('Magazine',$isv_publicationcategory);
        foreach($liste_magazines_publicationcode as $cle_magazine=>&$magazine) {
            if (array_key_exists($cle_magazine, $liste_magazines_category))
                $magazine->ne_parait_plus=$liste_magazines_category[$cle_magazine]->ne_parait_plus;
            else
                $magazine->ne_parait_plus=0;
            $magazine->toDB();
        }
        $liste_magazines_rediriges=DM_Core::multipleConstructFromIsv('Magazine', $isv_redirection_magazine);
        foreach($liste_magazines_rediriges as $cle_magazine=>&$magazine) {
            $magazine->toDB();
        }
        
    }

    function inducksToDM($nom_champ_inducks,$valeur) {
        switch($nom_champ_inducks) {
            case 'publicationcode': case 'issuerangecode':
                $regex_pays_magazine='#([a-z]+)/([A-Z0-9 ]+)#is';
                preg_match($regex_pays_magazine,$valeur,$pays_magazine);
                if (!array_key_exists(1,$pays_magazine))
                    xdebug_break();
                switch ($nom_champ_inducks) {
                    case 'publicationcode':  
                        $this->pays_abrege=$pays_magazine[1];
                        $this->nom_abrege=$pays_magazine[2];
                    break;
                    case 'issuerangecode':
                        $this->redirige_depuis=$pays_magazine[2];
                }
            break;

            case 'category':
                $regex_ne_parait_plus='#ne paraissant plus#isU';
                $this->ne_parait_plus=preg_match($regex_ne_parait_plus, $valeur)>0 ? 1 : 0;
            break;
        }
    }

    function toDB() {
        if (is_null($this->redirige_depuis)) {
            $requete='INSERT INTO magazines(PaysAbrege,NomAbrege,NomComplet,NeParaitPlus) '
                    .'VALUES(\''.$this->pays_abrege.'\',\''.$this->nom_abrege.'\',\''.str_replace("'", "\'", $this->nom_complet).'\','.$this->ne_parait_plus.')';
            DM_Core::$d->requete($requete);
        }
        else {
            $requete_get_nom_complet='SELECT NomComplet FROM magazines WHERE PaysAbrege = \''.$this->pays_abrege.'\' AND NomAbrege = \''.$this->nom_abrege.'\'';
            $resultat_get_nom_complet=DM_Core::$d->requete_select($requete_get_nom_complet);
            $nom_complet=$resultat_get_nom_complet[0]['NomComplet'];
            $requete='INSERT INTO magazines(PaysAbrege,NomAbrege,NomComplet,RedirigeDepuis) '
                    .'VALUES(\''.$this->pays_abrege.'\',\''.$this->nom_abrege.'\',\''.$nom_complet.'\',\''.$this->redirige_depuis.'\')';
            DM_Core::$d->requete($requete);
        }
        echo $requete.'<br />';
    }
    static function viderDB() {
        DM_Core::$d->requete('TRUNCATE magazines');
    }
}
?>
