<?php
class ParametrageG extends Controller {
    static $pays;
    static $magazine;
    static $etape;
    
    function index($pays=null,$magazine=null,$etape=null,$nom_fonction='null') {
        
        if (in_array(null,array($pays,$magazine))) {
            echo 'Erreur : Nombre d\'arguments insuffisant';
            exit();
        }
        self::$pays=$pays;
        self::$magazine=$magazine;
        self::$etape=$etape=='null'?null:$etape;
        $nom_fonction=$nom_fonction=='null'?null:$nom_fonction;
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
        if (is_null(self::$etape)) { // Liste des étapes
            $etapes=$this->Modele_tranche->get_etapes_simple(self::$pays,self::$magazine);
            if (count($etapes) == 0) {
                $fonction_dimension=new stdClass();
                $fonction_dimension->Ordre=-1;
                $fonction_dimension->Numero_debut=$fonction_dimension->Numero_fin=-1;
                $fonction_dimension->Nom_fonction='Dimensions';
                $etapes[]=$fonction_dimension;
            }
            $data=array('etapes'=>$etapes);
        }
        else {
            if (!is_null($nom_fonction)) {// Etape temporaire
                $options=$this->Modele_tranche->get_options(self::$pays,self::$magazine,self::$etape,$nom_fonction, null, false, true, true);
            }
            else {
                $fonction=$this->Modele_tranche->get_fonction(self::$pays,self::$magazine,self::$etape);
                if (is_null($fonction) && self::$etape == -1) {
                    $fonction=new stdClass();
                    $fonction->Nom_fonction='Dimensions';
                }
                if ($this->Modele_tranche->has_no_option(self::$pays,self::$magazine,self::$etape)) {
                    $options=$this->Modele_tranche->get_noms_champs($fonction->Nom_fonction);
                }
                else
                    $options=$this->Modele_tranche->get_options(self::$pays,self::$magazine,self::$etape,$fonction->Nom_fonction, null, false, true);

            }
            
            $data = array(
                'options'=>$options
            );
        }
        $this->load->view('parametragegview',$data);
    }
}

?>
