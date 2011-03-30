<?php
class ModifierG extends Controller {
    static $pays;
    static $magazine;
    static $etape;
    static $numeros;
    static $nom_option;
    static $nouvelle_valeur;
    
    function index($pays=null,$magazine=null,$etape=null,$numeros='',$nom_option='',$nouvelle_valeur='', $debut_plage='null',$fin_plage='null', $nom_nouvelle_fonction=null) {
        $nouvelle_valeur=$nouvelle_valeur=='null' ? null : $nouvelle_valeur;
        if (in_array(null,array($pays,$magazine,$etape))) {
            echo 'Erreur : Nombre d\'arguments insuffisant';
            exit();
        }
        self::$etape=$etape;
        $est_etape_temporaire="".intval(self::$etape) != self::$etape;
        self::$pays=$pays;
        self::$magazine=$magazine;
        self::$numeros=$numeros=explode('~',$numeros);
        self::$nom_option=$nom_option;
        self::$nouvelle_valeur=is_null($nouvelle_valeur) ? null : urldecode(str_replace('!','%',$nouvelle_valeur));
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

        if ($est_etape_temporaire) {
            $this->Modele_tranche->decaler_etapes_a_partir_de(self::$pays,self::$magazine,intval(self::$etape)+1);
            self::$etape=intval(self::$etape);
            $this->Modele_tranche->insert_ordre(self::$pays,self::$magazine,self::$etape,self::$numeros[0],self::$numeros[count(self::$numeros)-1],$nom_nouvelle_fonction,array());
        }
        $fonctions=$this->Modele_tranche->get_fonctions(self::$pays,self::$magazine,self::$etape);
        $options=$this->Modele_tranche->get_options(self::$pays,self::$magazine,self::$etape,$fonctions[0]->Nom_fonction);


        $valeurs=array();
        if ($nom_option == 'Actif') {
            $intervalles=array();
            $numeros_debut=explode(';',$fonctions[0]->Numero_debut);
            $numeros_fin=explode(';',$fonctions[0]->Numero_fin);
            foreach($numeros_debut as $i=>$numero_debut)
                $intervalles[]=$numero_debut.'~'.$numeros_fin[$i];
            $intervalles=implode(';',$intervalles);
            $valeurs_preexistantes=array($intervalles=>'on');
        }
        else
            $valeurs_preexistantes=$options->$nom_option;
        foreach($valeurs_preexistantes as $intervalles=>$valeur) {
            $liste_intervalles=explode(';',$intervalles);
            foreach($liste_intervalles as $i=>$intervalle) {
                list($numero_debut,$numero_fin)=strpos($intervalle, '~') == false ? array($intervalle,$intervalle) : explode('~',$intervalle);
                if ($numero_debut === $numero_fin)
                    $valeurs[$numero_debut]=$valeur;

                $numero_debut_trouve=false;
                foreach($numeros_dispos as $numero_dispo) {
                    if ($numero_dispo==$numero_debut)
                        $numero_debut_trouve=true;
                    if ($numero_debut_trouve) {
                        $valeurs[$numero_dispo]=$valeur;
                    }
                    if ($numero_dispo==$numero_fin)
                        continue 2;
                }
            }
        }
        foreach($numeros as $numero) {
            if ($nom_option == 'Actif' && (is_null(self::$nouvelle_valeur) || empty(self::$nouvelle_valeur))) {
                if (array_key_exists($numero, $valeurs))
                    unset ($valeurs[$numero]);
            }
            else
                $valeurs[$numero]=self::$nouvelle_valeur;
        }
        foreach($valeurs as $numero=>$valeur) {

        }
        $valeurs_distinctes=array_unique($valeurs);
        $valeurs_distinctes_numeros_groupes=array();
        foreach($valeurs_distinctes as $valeur_distincte) {
            $numeros_associes=array_value_list($valeur_distincte, $valeurs);
            sort($numeros_associes);
            $valeurs_distinctes_numeros_groupes[$valeur_distincte]=array();
            $numero_debut=null;
            $i=0;
            foreach($numeros_dispos as $numero_dispo) {
                if (is_null($numero_debut)) {
                    if ($numero_dispo != $numeros_associes[$i])
                        continue;
                    $numero_debut=$numero_fin=$numeros_associes[$i];
                }
                else {
                    if ($numero_dispo != $numeros_associes[$i]) {
                        $valeurs_distinctes_numeros_groupes[$valeur_distincte][]=$numero_debut.'~'.$numero_fin;
                        $numero_debut=null;
                        continue;
                    }
                    else
                        $numero_fin=$numeros_associes[$i];
                }
                $i++;
            }
            if (!is_null($numero_debut))
                $valeurs_distinctes_numeros_groupes[$valeur_distincte][]=$numero_debut.'~'.$numero_fin;
        }

        if (!$est_temporaire)
            $this->Modele_tranche->delete_option(self::$pays,self::$magazine,self::$etape,self::$nom_option);

        foreach($valeurs_distinctes_numeros_groupes as $valeur=>$intervalles) {
            $numeros_debut=array();
            $numeros_fin=array();
            foreach($intervalles as $intervalle) {
                list($numero_debut,$numero_fin)=explode('~',$intervalle);
                $numeros_debut[]=$numero_debut;
                $numeros_fin[]=$numero_fin;
            }
            $this->Modele_tranche->insert_valeur_option(self::$pays,self::$magazine,self::$etape,$fonctions[0]->Nom_fonction,self::$nom_option,
                                                        $valeur,implode(';',$numeros_debut),implode(';',$numeros_fin));
        }
        $this->load->view('parametragegview',$data);
    }
}

function array_value_list ($match, $array)
{
    $occurences = array();
    foreach ($array as $key => $value) {
        if ($value == $match)
            $occurences[]=$key;
    }

    return $occurences;
}
?>
