<?php
class Numerosdispos extends Controller {
    
    function index($pays=null,$magazine=null) {
        
        if (in_array(null,array($pays,$magazine))) {
            echo 'Erreur : Nombre d\'arguments insuffisant';
            exit();
        }
        $this->load->database();
        $this->load->model('Modele_tranche');
        $numeros_dispos=$this->Modele_tranche->get_numeros_disponibles($pays,$magazine);
        $nb_etapes=$this->Modele_tranche->get_nb_etapes($pays,$magazine);

        $requete_nom_magazine='SELECT NomComplet FROM magazines WHERE PaysAbrege LIKE \''.$pays.'\' AND (NomAbrege LIKE \''.$magazine.'\' OR RedirigeDepuis LIKE \''.$magazine.'\')';
        $resultat_nom_magazine=$this->Modele_tranche->db->query($requete_nom_magazine)->result();
        $requete_nom_pays='SELECT NomComplet FROM pays WHERE NomAbrege LIKE \''.$pays.'\' AND L10n LIKE \''.$_SESSION['lang'].'\'';
        $resultat_nom_pays=$this->Modele_tranche->db->query($requete_nom_pays)->result();

        
        $data = array('numeros_dispos'=>$numeros_dispos,
                      'nb_etapes'=>$nb_etapes,
                      'nom_magazine'=>$resultat_nom_magazine[0]->NomComplet.' ('.$resultat_nom_pays[0]->NomComplet.')');

        $this->load->view('numerosdisposview',$data);
    }
}

?>
