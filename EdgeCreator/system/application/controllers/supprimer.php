<?php
class Supprimer extends Controller {
    static $pays;
    static $magazine;
    static $ordre;
    static $numero_debut;
    static $numero_fin;
    static $nom_fonction;
    
    function index($pays=null,$magazine=null,$ordre=null,$numero_debut=1,$numero_fin=1,$nom_fonction=null,$parametrage='',$appliquer=false) {
        
        if (in_array(null,array($pays,$magazine,$ordre,$nom_fonction))) {
            echo 'Erreur : Nombre d\'arguments insuffisant';
            exit();
        }
        self::$pays=$pays;
        self::$magazine=$magazine;
        self::$ordre=$ordre;
        self::$numero_debut=$numero_debut;
        self::$numero_fin=$numero_fin;
        self::$nom_fonction=$nom_fonction;
        
        $this->load->library('session');
        $this->load->database();
        $this->db->query('SET NAMES UTF8');
        $this->load->helper('url');
        
        $this->load->model('Modele_tranche');
        $this->Modele_tranche->delete_ordre($pays,$magazine,$ordre,$numero_debut,$numero_fin,$nom_fonction);
    }
}

?>
