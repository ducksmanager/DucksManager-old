<?php
@session_start();
if (isset($_GET['lang'])) {
	$_SESSION['lang']=$_GET['lang'];
}
require_once('Format_liste.php');
class dmspiral extends Format_liste {
    static $titre='Liste en spirale';
	function dmspiral() {
            $this->description=DMSPIRAL_DESCRIPTION;
        }
        function afficher($liste) {
            
            foreach($liste as $pays=>$numeros_pays) {
                foreach($numeros_pays as $magazine=>$numeros) {
                    $chaine='';
                    $premier=true;
                    foreach($numeros as $numero_et_etat) {
                        if (!$premier)
                            $chaine.=',';
                        if (is_string($numero_et_etat) || count($numero_et_etat) <2)
                                continue;
                        $numero=$numero_et_etat[0];
                        $etat=$numero_et_etat[1];
                        $chaine.=$magazine.'!'.
                                 $numero.'!'.
                                 $etat.'!'.
                                 '2005-00-00'.'!'.
                                 'a';
                        $premier=false;
                    }
                    ?><img alt="dmspiral" src="Listes/Liste.dmspiral.class.php?chaine=<?=$chaine?>&amp;mag=<?=$magazine?>" /><?php
                }
            }
        }
}

function marquer_numero($image,$numero,$noir) {
    $centaine=intval($numero/100);
    $diz_unites=$numero-100*$centaine;
    $pos=new stdClass();
    switch($diz_unites) {
        case 0:            
            $pos->x=GAUCHE+(0.12-$centaine/4)*EPAISSEUR;
            $pos->y=HAUT-1+HAUTEUR_CENTRALE-EPAISSEUR;
        break;
        case 1:
            $pos->x=GAUCHE+EPAISSEUR*($diz_unites-1)+EPAISSEUR/4;
            $pos->y=HAUT-($centaine+1)*EPAISSEUR/2+EPAISSEUR/3;
        break;
        case 49:                
            $pos->x=GAUCHE+EPAISSEUR*($diz_unites-1)-EPAISSEUR/4;
            $pos->y=HAUT-($centaine+1)*EPAISSEUR/2+EPAISSEUR/3;
        break;
        case 50:
            $pos->x=GAUCHE+(47.87+$centaine/4)*EPAISSEUR;
            $pos->y=HAUT-1+HAUTEUR_CENTRALE-EPAISSEUR;
        break;
        case 51:
            $pos->x=GAUCHE+EPAISSEUR*(50-($diz_unites-50)-1)-EPAISSEUR/4;
            $pos->y=HAUT+HAUTEUR_CENTRALE+($centaine+1)*EPAISSEUR/2-EPAISSEUR/3;
        break;
        case 99:
            $pos->x=GAUCHE+EPAISSEUR*(50-($diz_unites-50)-1)+EPAISSEUR/4;
            $pos->y=HAUT+HAUTEUR_CENTRALE+($centaine+1)*EPAISSEUR/2-EPAISSEUR/3;
        break;

        default:
            if ($diz_unites<50) {
                $pos->x=GAUCHE+EPAISSEUR*($diz_unites-1);
                $pos->y=HAUT-($centaine+1)*EPAISSEUR/2+EPAISSEUR/4;
            }
            else {
                $pos->x=GAUCHE+EPAISSEUR*(50-($diz_unites-50)-1);
                $pos->y=HAUT+HAUTEUR_CENTRALE+($centaine)*EPAISSEUR/2+EPAISSEUR/4;
            }
        break;
    }
    $couleur=imagecolorallocate($image, COULEUR_R, COULEUR_G, COULEUR_B);
    imagefill($image, $pos->x, $pos->y, $couleur);
}

if (isset($_GET['chaine'])) {
    if (!isset($_GET['debug']))
        header('Content-type: image/png');
    define('EPAISSEUR',30);
    define('MARGE',2);
    define('TAILLE_POLICE',EPAISSEUR/2);
    define('HAUTEUR_CENTRALE',50);
    define('NUANCE_GRIS_FOND',255);
    define('COULEUR_R',0);define('COULEUR_G',0);define('COULEUR_B',255);
    $numeros_doubles=array(1,115,300,499,150,151,198);
    
    $titre='MICKEY PARADE';
    $numero_max=601;
    define('NB_CENTAINES', intval($numero_max/100)+1);
    define('HAUT',MARGE+NB_CENTAINES*EPAISSEUR/2);
    define('GAUCHE',MARGE+NB_CENTAINES*EPAISSEUR/4);

    $image=imagecreatetruecolor((48+NB_CENTAINES/2)*EPAISSEUR-10+MARGE+10, EPAISSEUR*NB_CENTAINES+HAUTEUR_CENTRALE+2+MARGE*2);
    imageantialias($image, true);
    $blanc=  imagecolorallocate($image, 255,255,255);
    $gris_clair=imagecolorallocate($image, NUANCE_GRIS_FOND,NUANCE_GRIS_FOND,NUANCE_GRIS_FOND);
    imagefill($image, 0,0, $gris_clair);

    $noir=  imagecolorallocate($image, 0,0,0);
    $blanc=  imagecolorallocate($image, 255,255,255);

    /** CREATION DE LA GRILLE **/

    imagettftext($image, TAILLE_POLICE*0.55, 0, GAUCHE, HAUT+EPAISSEUR*0.75, $noir, 'arial.ttf', 1);
    imagettftext($image, TAILLE_POLICE*0.55, 0, GAUCHE+47.3*EPAISSEUR, HAUT+EPAISSEUR*0.75, $noir, 'arial.ttf', 49);
    imagettftext($image, TAILLE_POLICE*0.8, 0, GAUCHE+46.8*EPAISSEUR, HAUT-2+(EPAISSEUR*0.5+HAUTEUR_CENTRALE)/2, $noir, 'arial.ttf', 50);
    imagettftext($image, TAILLE_POLICE*0.55, 0, GAUCHE+47.3*EPAISSEUR, HAUT+2+HAUTEUR_CENTRALE-EPAISSEUR/2, $noir, 'arial.ttf', 51);
    imagettftext($image, TAILLE_POLICE*0.8, 0, GAUCHE+EPAISSEUR*0.5, HAUT-2+(EPAISSEUR*0.5+HAUTEUR_CENTRALE)/2, $noir, 'arial.ttf', 100);
    imagettftext($image, TAILLE_POLICE*0.55, 0, GAUCHE, HAUT+2+HAUTEUR_CENTRALE-EPAISSEUR/2, $noir, 'arial.ttf', 99);

    imagearc($image, GAUCHE+EPAISSEUR/2, HAUT-1+EPAISSEUR/2, EPAISSEUR/2, 2+EPAISSEUR, 180, 270, $noir);
    imageline($image, GAUCHE, HAUT-1+EPAISSEUR/2, GAUCHE+0.25*EPAISSEUR, HAUT-1+EPAISSEUR/2, $noir);
    imageline($image,GAUCHE+EPAISSEUR/2,HAUT-1,GAUCHE+47.5*EPAISSEUR,HAUT-1,$noir);
    for ($i=2;$i<=48;$i++) {
        $numero=$i;
        imagettftext($image, TAILLE_POLICE, 0, GAUCHE+($i-1.3)*EPAISSEUR, HAUT+1+EPAISSEUR*0.5, $noir, 'arial.ttf', $numero);
        $numero=100-$i;
        imagettftext($image, TAILLE_POLICE, 0, GAUCHE+($i-1.3)*EPAISSEUR, HAUT-2+HAUTEUR_CENTRALE, $noir, 'arial.ttf', $numero);
    }
    imagettftext($image, TAILLE_POLICE, 0, GAUCHE+23.5*EPAISSEUR, HAUT+10+HAUTEUR_CENTRALE/2, $noir, 'arial.ttf', $titre);

    imagearc($image, GAUCHE+47.5*EPAISSEUR, HAUT-2+EPAISSEUR/2, EPAISSEUR/2, EPAISSEUR, 270, 360, $noir);
    imagearc($image, GAUCHE+47.5*EPAISSEUR, HAUT-EPAISSEUR/2+HAUTEUR_CENTRALE, EPAISSEUR/2, EPAISSEUR, 0, 90, $noir);

    imageline($image,GAUCHE+47.5*EPAISSEUR,HAUT-1+HAUTEUR_CENTRALE,GAUCHE+EPAISSEUR/2,HAUT-1+HAUTEUR_CENTRALE,$noir);

    imageline($image, GAUCHE+47.75*EPAISSEUR, HAUT-1+EPAISSEUR/2, GAUCHE+47.75*EPAISSEUR, HAUT+1-EPAISSEUR/2+HAUTEUR_CENTRALE, $noir);

    imagearc($image, GAUCHE+EPAISSEUR/2, HAUT-EPAISSEUR/2+HAUTEUR_CENTRALE, EPAISSEUR, EPAISSEUR, 90, 180, $noir);
    imageline($image, GAUCHE, HAUT-1+EPAISSEUR/2, GAUCHE, HAUT+1-EPAISSEUR/2+HAUTEUR_CENTRALE, $noir);

    for ($centaine=0;$centaine<NB_CENTAINES;$centaine++) {

        imagearc($image, GAUCHE+EPAISSEUR/2, HAUT-.5, EPAISSEUR+$centaine*EPAISSEUR/2, EPAISSEUR*($centaine+1), 180, 270, $noir);    
        imageline($image, GAUCHE-$centaine*EPAISSEUR/4, HAUT-1, GAUCHE-$centaine*EPAISSEUR/4, HAUT+EPAISSEUR/2, $noir);

        imageline($image,GAUCHE+EPAISSEUR/2,HAUT-($centaine+1)*EPAISSEUR/2,GAUCHE+47.5*EPAISSEUR,HAUT-($centaine+1)*EPAISSEUR/2,$noir);

        for ($i=1;$i<49;$i++) {
            imageline($image, GAUCHE+($i-0.5)*EPAISSEUR,HAUT-($centaine+1)*EPAISSEUR/2, GAUCHE+($i-0.5)*EPAISSEUR,HAUT-$centaine*EPAISSEUR/2, $noir);
            imageline($image, GAUCHE+($i-0.5)*EPAISSEUR,HAUT+HAUTEUR_CENTRALE+($centaine+1)*EPAISSEUR/2, GAUCHE+($i-0.5)*EPAISSEUR,HAUT-1+HAUTEUR_CENTRALE+$centaine*EPAISSEUR/2, $noir);
        }

        imageline($image,GAUCHE+EPAISSEUR/2,HAUT+HAUTEUR_CENTRALE+($centaine+1)*EPAISSEUR/2,GAUCHE+47.5*EPAISSEUR,HAUT+HAUTEUR_CENTRALE+($centaine+1)*EPAISSEUR/2,$noir);

        imagearc($image, GAUCHE+47.5*EPAISSEUR, HAUT, EPAISSEUR+$centaine*EPAISSEUR/2, EPAISSEUR*($centaine+1), 270, 360, $noir);
        imagearc($image, GAUCHE+47.5*EPAISSEUR, HAUT+HAUTEUR_CENTRALE, EPAISSEUR+$centaine*EPAISSEUR/2, EPAISSEUR*($centaine+1), 0, 90, $noir);

        imageline($image, GAUCHE+(47.75+($centaine+1)/4)*EPAISSEUR, HAUT-1, GAUCHE+(47.75+($centaine+1)/4)*EPAISSEUR, HAUT+1+HAUTEUR_CENTRALE, $noir);
        imageline($image, GAUCHE+(47.75+$centaine/4)*EPAISSEUR, HAUT-1+EPAISSEUR/2, GAUCHE+(47.75+($centaine+1)/4)*EPAISSEUR, HAUT-1+EPAISSEUR/2, $noir);
        imageline($image, GAUCHE+(47.75+$centaine/4)*EPAISSEUR, HAUT+1-EPAISSEUR/2+HAUTEUR_CENTRALE, GAUCHE+(47.75+($centaine+1)/4)*EPAISSEUR, HAUT+1-EPAISSEUR/2+HAUTEUR_CENTRALE, $noir);

        imagearc($image, GAUCHE+EPAISSEUR/2, HAUT+HAUTEUR_CENTRALE, EPAISSEUR+($centaine+1)*EPAISSEUR/2, EPAISSEUR*($centaine+1), 90, 180, $noir);

        imageline($image, GAUCHE-(($centaine+1)*0.25)*EPAISSEUR, HAUT+EPAISSEUR/2, GAUCHE-(($centaine+1)*0.25)*EPAISSEUR, HAUT+1+HAUTEUR_CENTRALE, $noir);
        imageline($image, GAUCHE-(($centaine+1)*0.25)*EPAISSEUR, HAUT-1+EPAISSEUR/2, GAUCHE-($centaine*0.25)*EPAISSEUR, HAUT-1+EPAISSEUR/2, $noir);
        imageline($image, GAUCHE-(($centaine+1)*0.25)*EPAISSEUR, HAUT+1-EPAISSEUR/2+HAUTEUR_CENTRALE, GAUCHE-($centaine*0.25)*EPAISSEUR, HAUT+1-EPAISSEUR/2+HAUTEUR_CENTRALE, $noir);
    }
    foreach($numeros_doubles as $numero_double) {
        $centaine=intval($numero_double/100);
        $diz_unites=$numero_double-100*$centaine;
        switch($diz_unites) {
            case 0:
                imagefilledrectangle($image, GAUCHE+0.3*EPAISSEUR/4-($centaine+1)*EPAISSEUR/4, HAUT-1+EPAISSEUR/4, 
                                             GAUCHE+0.7*EPAISSEUR/4-($centaine+1)*EPAISSEUR/4, HAUT-1+EPAISSEUR/4+HAUTEUR_CENTRALE/2,
                                             $gris_clair);
                
            break;
            case 1:
                imagefilledrectangle($image, GAUCHE+.5*EPAISSEUR,HAUT-($centaine+0.75)*EPAISSEUR/2, 
                                             GAUCHE+EPAISSEUR,HAUT-($centaine+0.25)*EPAISSEUR/2,
                                             $gris_clair);
                
            break;
            case 48:
                imagefilledrectangle($image, GAUCHE+($diz_unites-1)*EPAISSEUR,HAUT-($centaine+0.75)*EPAISSEUR/2, 
                                             GAUCHE+($diz_unites-.5)*EPAISSEUR,HAUT-($centaine+0.25)*EPAISSEUR/2,
                                             $gris_clair);
            break;
            case 49:
                imagefilledrectangle($image, GAUCHE+(47.8+$centaine/4)*EPAISSEUR, HAUT-1+EPAISSEUR/4, 
                                             GAUCHE+(47.95+($centaine)/4)*EPAISSEUR, HAUT-1+EPAISSEUR/4+HAUTEUR_CENTRALE/2,
                                             $gris_clair);

            break;
            case 50:
                imagefilledrectangle($image, GAUCHE+(47.8+$centaine/4)*EPAISSEUR, HAUT+1+HAUTEUR_CENTRALE-EPAISSEUR, 
                                             GAUCHE+(47.95+($centaine)/4)*EPAISSEUR, HAUT+1+HAUTEUR_CENTRALE-EPAISSEUR/2+EPAISSEUR/4,
                                             $gris_clair);

            break;
            case 51:
                imagefilledrectangle($image, GAUCHE+(99-$diz_unites-1)*EPAISSEUR,HAUT+HAUTEUR_CENTRALE+($centaine+0.75)*EPAISSEUR/2, 
                                             GAUCHE+(99-$diz_unites-.5)*EPAISSEUR,HAUT+HAUTEUR_CENTRALE+($centaine+0.25)*EPAISSEUR/2,
                                             $gris_clair);
            break;
            case 98:
                imagefilledrectangle($image, GAUCHE+(99-$diz_unites-0.5)*EPAISSEUR,HAUT+HAUTEUR_CENTRALE+($centaine+0.75)*EPAISSEUR/2, 
                                             GAUCHE+(99-$diz_unites)*EPAISSEUR,HAUT+HAUTEUR_CENTRALE+($centaine+0.25)*EPAISSEUR/2,
                                             $gris_clair);
            break;
            case 99:
                imagefilledrectangle($image, GAUCHE+0.3*EPAISSEUR/4-($centaine+1)*EPAISSEUR/4,HAUT+1+HAUTEUR_CENTRALE-EPAISSEUR, 
                                             GAUCHE+0.7*EPAISSEUR/4-($centaine+1)*EPAISSEUR/4,HAUT+1+HAUTEUR_CENTRALE-EPAISSEUR/2+EPAISSEUR/4,
                                             $gris_clair);
            
            break;
            default:
                if ($diz_unites<49) {
                    imagefilledrectangle($image, GAUCHE+($diz_unites-1)*EPAISSEUR,HAUT-($centaine+0.75)*EPAISSEUR/2, 
                                                 GAUCHE+($diz_unites)*EPAISSEUR,HAUT-($centaine+0.25)*EPAISSEUR/2,
                                                 $gris_clair);
                }
                else {
                    $diz_unites=99-$diz_unites;
                    imagefilledrectangle($image, GAUCHE+($diz_unites-1)*EPAISSEUR,HAUT+HAUTEUR_CENTRALE+($centaine+0.75)*EPAISSEUR/2, 
                                                 GAUCHE+($diz_unites)*EPAISSEUR,HAUT+HAUTEUR_CENTRALE+($centaine+0.25)*EPAISSEUR/2,
                                                 $gris_clair);
                    
                }
        }
    }


    /** REMPLISSAGE **/

    marquer_numero($image,1,$noir);
    //marquer_numero($image,101,$noir);
    marquer_numero($image,155,$noir);
    marquer_numero($image,202,$noir);
    marquer_numero($image,248,$noir);
    marquer_numero($image,151,$noir);
    //marquer_numero($image,450,$noir);
    marquer_numero($image,252,$noir);
    marquer_numero($image,254,$noir);
    marquer_numero($image,300,$noir);

    imagepng($image);

    
    /** AFFICHAGE DES CENTAINES **/
    for($i=0;$i<=NB_CENTAINES;$i++) {
        $pos=new stdClass();
        $pos->x=GAUCHE+(47.87+NB_CENTAINES/4)*EPAISSEUR;
    }
}
?>