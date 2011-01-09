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
        $this->Modele_tranche->setPays(self::$pays);
        $this->Modele_tranche->setMagazine(self::$magazine);
        self::$numero=$numero;
        self::$parametrage=json_decode($parametrage);
        $parametrage=json_decode($parametrage);
        self::$etapes_actives=explode('-', $etapes_actives);
        
        $num_ordres=$this->Modele_tranche->get_ordres($pays,$magazine,$numero);
        //print_r($ordres);
        $dimensions=array();
        self::$etape_en_cours=new stdClass();
        foreach($num_ordres as $num_ordre) {
            if ($num_ordre<0 || in_array($num_ordre,self::$etapes_actives)) {
                $ordres[$num_ordre]=$this->Modele_tranche->get_fonctions($pays,$magazine,$num_ordre,$numero);
                self::$etape_en_cours->num_etape=$num_ordre;
                self::$etape_en_cours->nom_fonction=$ordres[$num_ordre][0]->Nom_fonction;
                foreach($ordres[$num_ordre] as $i=>$fonction) {
                    if (est_dans_intervalle($numero,$fonction->Numero_debut.'~'.$fonction->Numero_fin)) {
                        $options2=$this->Modele_tranche->get_options($pays,$magazine,$num_ordre,$fonction->Nom_fonction,$numero);
                        if ($num_ordre==-1)
                            $dimensions=$options2;
                        foreach(self::$parametrage as $parametres=>$options) {
                            list($num_ordre_param,$nom_fonction_param)=explode('~', $parametres);
                            if ($num_ordre_param==$num_ordre && $nom_fonction_param==$fonction->Nom_fonction) {
                                foreach($options as $option_nom__intervalle=>$option_valeur) {
                                    if(strpos($option_nom__intervalle, '.')==false) {
                                        continue;
                                    }
                                    list($option_nom,$intervalle)=explode('.',$option_nom__intervalle);
                                    if (est_dans_intervalle($numero,$intervalle)) {
                                        $options2->$option_nom
                                            =urldecode(str_replace('^','%',
                                                       str_replace('!amp!','&',
                                                       str_replace('!sharp!','#',$option_valeur))));
                                    }
                                }
                            }
                        }
                        new $ordres[$num_ordre][$i]->Nom_fonction($options2);
                    }
                }
            }
        }
        
        // Nouvelles étapes
        foreach(self::$parametrage as $parametres=>$options) {
            list($num_ordre_param_ajout,$nom_fonction_param)=explode('~', $parametres);
            if ($num_ordre_param_ajout > $num_ordre) { // Numéro d'étape supérieure à la maximale existante
                foreach($options as $option_nom__intervalle=>$option_valeur) {
                    list($option_nom,$intervalle)=explode('.',$option_nom__intervalle);
                    if (est_dans_intervalle($numero,$intervalle)) {
                        $ordres[$num_ordre_param_ajout][0]->options->$option_nom=urldecode(str_replace('^','%',
                                                   str_replace('!amp!','&',
                                                   str_replace('!sharp!','#',$option_valeur))));
                    }
                }
                if (isset($ordres[$num_ordre_param_ajout][0]->options))
                    new $nom_fonction_param($ordres[$num_ordre_param_ajout][0]->options);
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
