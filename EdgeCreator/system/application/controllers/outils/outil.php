<?php
define('NO_OBJECT',false);
define('WITH_OBJECTS',true);

class Outil extends Controller {
    var $nom_outil=null;
    var $image=null;
    static $liste_images=array();
    static $liste_couleurs=array();

    function __construct() {
        $this->nom_outil=get_class($this);
    }
    
    function action() {
        $nom_outil=$this->nom_outil;
        $parametres=$this->toParameters(WITH_OBJECTS);
        call_user_func_array($nom_outil::$fonction, $parametres);
    }

    function getIcone() {
        return get_class($this).'.png';
    }

    function toParameters($with_object=true) {
        $parametres=array();
        $nom_outil=$this->nom_outil;
        foreach($nom_outil::$arguments as $argument) {
            if ($with_object) {
                switch($argument) {
                    case 'IMAGE':
                        $parametres[]=Outil::$liste_images[$this->image];
                    break;
                    case 'COULEUR0':case 'COULEUR1':
                        $parametres[]=Outil::$liste_couleurs[$this->image];
                    break;
                    default:
                        $parametres[]=$this->$argument;
                    break;
                }
            }
            else {
                $parametre='';
                if (strtoupper($argument) === $argument)
                    $parametre='$';
                switch($argument) {
                    case 'IMAGE': case 'COULEUR0' : case 'COULEUR1':
                        $parametre.=$this->image;
                    break;
                    default:
                        $parametre.=$this->$argument;
                    break;
                }
                $parametres[]=$parametre;
            }
        }
        return $parametres;
    }

    function toCode() {
        $nom_outil=$this->nom_outil;
        $code='';
        $i=0;
        $num_couleur='couleur'.$i;
        while (isset($this->$num_couleur)) {
            $rgbalpha=imagecolorsforindex(Outil::$liste_images[$this->image], $this->$num_couleur);
            $r=$rgbalpha['red'];$g=$rgbalpha['green'];$b=$rgbalpha['blue'];
            $code.='$couleur'.$i.'=imagecolorallocate($'.$this->image.','.$r.','.$g.','.$b.');<br />';
            $i++;
            $num_couleur='couleur'.$i;
        }
        $code.=$nom_outil::$fonction.' ('
              .implode(',',$this->toParameters(NO_OBJECT))
              .');'.'<br />';
        return $code;
    }
}
?>
