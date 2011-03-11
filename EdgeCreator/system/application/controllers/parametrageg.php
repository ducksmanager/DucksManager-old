<?php
class ParametrageG extends Controller {
    static $pays;
    static $magazine;
    static $etape;
    
    function index($pays=null,$magazine=null,$etape=null) {
        
        if (in_array(null,array($pays,$magazine,$etape))) {
            echo 'Erreur : Nombre d\'arguments insuffisant';
            exit();
        }
        self::$etape=$etape;
        self::$pays=$pays;
        self::$magazine=$magazine;
        
        $this->load->library('session');
        $this->load->database();
        $this->db->query('SET NAMES UTF8');
        $this->load->helper('url');
        $this->load->helper('form');
        
        $this->load->model('Modele_tranche');
        $numeros_dispos=$this->Modele_tranche->get_numeros_disponibles(self::$pays,self::$magazine);
        $this->Modele_tranche->setNumerosDisponibles($numeros_dispos);
        $this->Modele_tranche->setPays(self::$pays);
        $this->Modele_tranche->setMagazine(self::$magazine);
        
        $fonctions=$this->Modele_tranche->get_fonctions(self::$pays,self::$magazine,self::$etape);
        $options=$this->Modele_tranche->get_options(self::$pays,self::$magazine,self::$etape,$fonctions[0]->Nom_fonction);
        
        $data = array(
                'fonctions'=>$fonctions,
                'options'=>$options
        );

        $this->load->view('parametragegview',$data);
    }
}

?>
