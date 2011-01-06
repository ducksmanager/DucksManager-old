<?php
include_once('util/booleen.php');
include_once('util/couleur.php');
include_once('util/image.php');
include_once('insertions/rectangle.php');
include_once('insertions/trait.php');

class ElementGraphique extends Controller {

    static $liste_elements=array('Texte','Image','Trait','Arc','Polygone','Rectangle','Ellipse');
    static $liste_images=array();
    var $sous_elements=array();
    var $type;
    var $niveau=0;
    var $image_relative;
    var $image;

    function ElementGraphique() {  }
    
    function init() {
        $this->image=new Image($this->type,imagecreatetruecolor($this->getCanevasW(),$this->getCanevasH()));
        $transparent=imagecolortransparent($this->image->contenu);
        imagefill($this->image->contenu, 0, 0, $transparent);
    }


    function getDimensionsCanevas() {
        return array($this->getCanevasW(),$this->getCanevasH());
    }

    static function getImageRelative($nom) {
        return ElementGraphique::$liste_images[$nom];
    }

    function setType($type_element) {
        if (!in_array($type_element, ElementGraphique::$liste_elements))
            fatal_error('L\'élément graphique '.$type_element.' n\'est pas supporté');
        $this->type=$type_element;
    }

    function index() {
        $image_base=new Image('vide',imagecreate(30, 250));
        $rectangle=new Rectangle();
        $rectangle->image_relative=$image_base;
        $rectangle->x1=2;
        $rectangle->y1=50;
        $rectangle->x2=60;
        $rectangle->y2=70;
        $rectangle->init();
        $rectangle->couleur=new Couleur($rectangle->image, 14, 250, 40);
        $rectangle->rempli->v=false;

        $trait=new Trait();
        $trait->x1=2;
        $trait->y1=0;
        $trait->x2=50;
        $trait->y2=90;
        $trait->init();
        $trait->couleur=new Couleur($trait->image, 145, 167, 4);

        $rectangle->ajouterElement($trait);
        
        echo $rectangle;
        $rectangle->dessiner();
        $rand=rand();
        imagepng($rectangle->image_relative->contenu,$rand.'.png');
        echo '<img src="http://localhost/DucksManager/EdgeCreator/'.$rand.'.png" />';
    }

    function dessiner() {
        foreach($this->sous_elements as $sous_element)
            $sous_element->dessiner();
        $rand=rand();
        //imagepng($this->image_relative->contenu,$rand.'.png');
        //echo '<img src="http://localhost/DucksManager/EdgeCreator/'.$rand.'.png" />';
    }

    function  __toString() {
        $retour='';
        for($i=0;$i<2*$this->niveau;$i++)
            $retour.='&nbsp;';
        $retour.= 'Element graphique de type '.$this->type.' ';

        $contenu=array();
        foreach(get_object_vars($this) as $nom=>$valeur) {
            if (!in_array($nom,array('sous_elements','type','image_relative','niveau','$image_relative')) && strpos($nom,'ci_')===false)
                $contenu[]=$nom.'='.$valeur;
        }
        $retour.= '('.implode(', ',$contenu).')';
        if (count($this->sous_elements) >0)
            $retour.=' contenant les sous-elements :';
        $retour.='<br />';
        foreach($this->sous_elements as $num=>$sous_element) {
            for($i=0;$i<2*$this->niveau+4;$i++)
                $retour.='&nbsp;';
            $retour.='['.$num.'] : '.$sous_element;
        }
        return $retour;
    }

    function getSous_elements() {
        return $this->sous_elements;
    }

    function ajouterElement(ElementGraphique $e, $position=false) {
        $e->niveau=$this->niveau+1;
        $e->image_relative->nom=$this->image->nom;

        if ($position) {
            if (array_key_exists($position, $this->sous_elements)) {
                for ($i=count($this->sous_elements)-1;$i>=$position;$i--)
                    $this->sous_elements[$i+1]=$this->sous_elements[$i];
                $this->sous_elements[$position]=$e;
            }
            else
                $this->sous_elements[]=$e;
        }
        else {
            $this->sous_elements[]=$e;
        }
    }

    function supprimerElement(ElementGraphique $e) {
        foreach($this->sous_elements as $position=>$sous_element) {
            if ($e === $sous_element) {
                unset($this->sous_elements[$position]);
                for ($i=$position;$i<count($this->sous_elements);$i++)
                    $this->sous_elements[$i]=$this->sous_elements[$i+1];
            }
        }
    }
}

function fatal_error($erreur) {
    echo $erreur;
    exit(0);
}

?>
