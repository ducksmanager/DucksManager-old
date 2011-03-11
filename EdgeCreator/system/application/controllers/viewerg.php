<?php
class Viewer extends Controller {
    static $image;
    static $largeur;
    static $hauteur;
    static $pays;
    static $magazine;
    static $numero;
    static $parametrage;
    static $zoom;
    static $etapes_actives=array();
    
    static $etape_en_cours;
    
    function index($pays=null,$magazine=null,$numero=null,$zoom=1,$etapes_actives='1',$parametrage='',$save='false',$random=null,$debug=false) {
        self::$zoom=$zoom;
        $this->load->library('session');
        $session_id = $this->session->userdata('session_id');
        $this->load->database();
        
        $this->load->model('Modele_tranche');
        
        if (is_null($pays) || is_null($magazine)) {
            echo 'Erreur : Nombre d\'arguments insuffisant';
            exit();
        }
        else {
             if (is_null($numero)) {
                 header('Content-type: image/png');
                 self::$image=imagecreatetruecolor(1, 1);
                 imagepng(self::$image);
                 exit();
             }
             elseif($numero=='Aucun') {
                 $largeur=20;
                 $hauteur=250;
                 self::$image=imagecreatetruecolor(z($largeur), z($hauteur));
                 $blanc=imagecolorallocate(self::$image, 255,255,255);
                 imagefill(self::$image,0,0,$blanc);
                 $noir=imagecolorallocate(self::$image, 0,0,0);
                 imagettftext(self::$image,z(10),-90,
                              z(5),z(5),
                              $noir,BASEPATH.'fonts/Arial.TTF','Aucun numero selectionne');
                 $dimensions=new stdClass();
                 $dimensions->Dimension_x=$largeur;
                 $dimensions->Dimension_y=$hauteur;
                 new Dessiner_contour($dimensions);
                 
                 header('Content-type: image/png');
                 imagepng(self::$image);
                 exit();
             }
        }
        self::$pays=$pays;
        self::$magazine=$magazine;
        $this->Modele_tranche->setSessionID($session_id);
        $this->Modele_tranche->setPays(self::$pays);
        $this->Modele_tranche->setMagazine(self::$magazine);
        self::$numero=$numero;
        self::$parametrage=json_decode($parametrage);
        $parametrage=json_decode($parametrage);
        self::$etapes_actives=explode('-', $etapes_actives);
        
        $num_etapes=$this->Modele_tranche->get_ordres($pays,$magazine,$numero);
        //print_r($ordres);
        $dimensions=array();
        self::$etape_en_cours=new stdClass();
        $num_etape=-2;
        foreach($num_etapes as $num_etape) {
            if ($num_etape<0 || in_array($num_etape,self::$etapes_actives)) {
                /*if ($num_etape>=0 && file_exists('../edges/tmp_previews/'.$session_id.'/'.$pays.'_'.$magazine.'_'.$numero.'_'.$num_etape.'_'.self::$zoom.'.png')) {
                    header('Content-type: image/png');
                    imagepng(imagecreatefrompng('../edges/tmp_previews/'.$session_id.'/'.$pays.'_'.$magazine.'_'.$numero.'_'.$num_etape.'_'.self::$zoom.'.png'));
                    exit(0);
                }*/
                $etapes[$num_etape]=$this->Modele_tranche->get_fonctions($pays,$magazine,$num_etape,$numero);
                self::$etape_en_cours->num_etape=$num_etape;
                self::$etape_en_cours->nom_fonction=$etapes[$num_etape][0]->Nom_fonction;
                $fonction=$etapes[$num_etape][0];
                $a=est_dans_intervalle($numero,$fonction->Numero_debut.'~'.$fonction->Numero_fin);
                if (est_dans_intervalle($numero,$fonction->Numero_debut.'~'.$fonction->Numero_fin)) {
                    $options2=$this->Modele_tranche->get_options($pays,$magazine,$num_etape,$fonction->Nom_fonction,$numero);
                    if ($num_etape==-1)
                        $dimensions=$options2;
                    foreach(self::$parametrage as $parametres=>$options) {
                        list($num_etape_param,$nom_fonction_param)=explode('~', $parametres);
                        if ($num_etape_param==$num_etape && $nom_fonction_param==$fonction->Nom_fonction) {
                            foreach($options as $option_nom__intervalle=>$option_valeur) {
                                if(strpos($option_nom__intervalle, '.')==false) {
                                    continue;
                                }
                                list($option_nom,$intervalle)=explode('.',$option_nom__intervalle);
                                if (est_dans_intervalle($numero,$intervalle)) {
                                    $options2->$option_nom
                                        =urldecode(str_replace('^','%',
                                                   str_replace('!amp!','&',
                                                   str_replace('!slash!','/',
                                                   str_replace('!sharp!','#',$option_valeur)))));
                                }
                            }
                        }
                    }
                    $id_preview=-1;
                    if ($num_etape == -1) {
                        $o_dimensions=new $etapes[$num_etape][0]->Nom_fonction($options2);
                        $o_options_dimensions=$o_dimensions->options;
                        if (self::$etapes_actives == array(1)) {
                            $id_preview=$this->Modele_tranche->ajouter_preview(json_encode($o_options_dimensions));
                            
                        }
                            
                    }
                    if ($num_etape >= 0 || self::$etapes_actives==array(-1)) {
                        $nom_fonction=$etapes[$num_etape][0]->Nom_fonction;
                        $o_etape=new $nom_fonction($options2);
                        $options=new stdClass();
                        $options->dimensions=$o_options_dimensions;
                        $options->$nom_fonction=$o_etape->options;
                        $id_preview=$this->Modele_tranche->ajouter_preview(json_encode($options));
                    }
                    if ($id_preview !=-1) {
                        imagepng(Viewer::$image,'../edges/tmp_previews/'.$session_id.'/'.$id_preview.'.png');
                            
                    }
                }
            }
        }
        
        // Nouvelles étapes
        foreach(self::$parametrage as $parametres=>$options) {
            list($num_etape_param_ajout,$nom_fonction_param)=explode('~', $parametres);
            self::$etape_en_cours->num_etape=$num_etape_param_ajout;
            self::$etape_en_cours->nom_fonction=$nom_fonction_param;
            if ($num_etape_param_ajout > $num_etape) { // Numéro d'étape supérieure à la maximale existante
                foreach($options as $option_nom__intervalle=>$option_valeur) {
                    list($option_nom,$intervalle)=explode('.',$option_nom__intervalle);
                    if (est_dans_intervalle($numero,$intervalle)) {
                        $etapes[$num_etape_param_ajout][0]->options->$option_nom=urldecode(str_replace('^','%',
                                                   str_replace('!amp!','&',
                                                   str_replace('!slash!','/',
                                                   str_replace('!sharp!','#',$option_valeur)))));
                    }
                }
                if (isset($etapes[$num_etape_param_ajout][0]->options))
                    new $nom_fonction_param($etapes[$num_etape_param_ajout][0]->options);
            }
        }
        
        new Dessiner_contour($dimensions);
        
        if ($debug===false)
            header('Content-type: image/png');
        if ($save=='true' && $zoom==1.5) {
            @mkdir('system/application/views/gen/'.$pays);
            imagepng(Viewer::$image,'system/application/views/gen/'.$pays.'/'.$magazine.'.'.$numero.'.png');
            imagepng(Viewer::$image,'../edges/'.$pays.'/gen/'.$magazine.'.'.$numero.'.png');
        }
        imagepng(Viewer::$image);
        
        exit();
    }
}

?>
