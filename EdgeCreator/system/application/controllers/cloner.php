<?php
class Cloner extends Controller {
    static $pays;
    static $magazine;
    static $etape_courante;
    static $etape;
    
    function index($pays=null,$magazine=null,$etape_courante=null,$etape=null) {
        
        if (in_array(null,array($pays,$magazine,$etape_courante,$etape))) {
            echo 'Erreur : Nombre d\'arguments insuffisant';
            exit();
        }
        self::$pays=$pays;
        self::$magazine=$magazine;
        self::$etape_courante=$etape_courante;
        self::$etape=$etape;
        
        $this->load->library('session');
        $this->load->database();
        $this->db->query('SET NAMES UTF8');
        $this->load->helper('url');
        $this->load->helper('form');
        
        $this->load->model('Modele_tranche');
        $this->Modele_tranche->cloner_etape($pays,$magazine,$etape_courante,$etape);
        
    }
}

?>
