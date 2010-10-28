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
    var $ne_parait_plus=null;
    
    function Magazine($pays_abrege=null,$nom_abrege=null,$nom_complet=null,$ne_parait_plus=null) {
        parent::__construct();
    }

    function get_cle() {
        return array($this->pays_abrege,$this->nom_abrege);
    }

    static function updateList() {
        global $isv_publication;
        global $isv_publicationcategory;

        $f_o=new Magazine();
        $liste_magazines_publicationcode=DM_Core::multipleConstructFromIsv('Magazine',$isv_publication);
        $liste_magazines_category=DM::multipleConstructFromIsv('Magazine',$isv_publicationcategory);
        foreach($liste_magazines_publicationcode as $cle_magazine=>&$magazine) {
            if (array_key_exists($cle_magazine, $liste_magazines_category))
                $magazine->ne_parait_plus=$liste_magazines_category[$cle_magazine]->ne_parait_plus;
            else
                $magazine->ne_parait_plus=0;
            $magazine->toDB();
        }
        
    }

    function inducksToDM($nom_champ_inducks,$valeur) {
        switch($nom_champ_inducks) {
            case 'publicationcode':
                $regex_pays_magazine='#([a-z]+)/([A-Z0-9]+)#is';
                preg_match($regex_pays_magazine,$valeur,$pays_magazine);
                if (!array_key_exists(1,$pays_magazine))
                    xdebug_break();
                $this->pays_abrege=$pays_magazine[1];
                $this->nom_abrege=$pays_magazine[2];
            break;

            case 'category':
                $regex_ne_parait_plus='#ne paraissant plus#isU';
                $this->ne_parait_plus=preg_match($regex_ne_parait_plus, $valeur)>0 ? 1 : 0;
            break;
        }
    }

    function toDB() {
        $requete='INSERT INTO magazines(PaysAbrege,NomAbrege,NomComplet,NeParaitPlus) '
                .'VALUES(\''.$this->pays_abrege.'\',\''.$this->nom_abrege.'\',\''.$this->nom_complet.'\','.$this->ne_parait_plus.')';
        self::$d->requete($requete);
        echo $requete;
    }
    static function viderDB() {
        self::$d->requete('TRUNCATE magazines');
    }
}
?>
