<?php
include_once('Texte.class.php');
include_once('IntervalleValidite.class.php');
class Edge {
    var $pays;
    var $magazine;
    var $numero;
    var $textes=array();
    var $largeur=20;
    var $hauteur=200;
    var $image;
    var $o;
    var $est_visible=true;
    var $intervalles_validite=array();
    var $en_cours=array();
    static $grossissement=10;
    static $grossissement_affichage=1.5;
    static $largeur_numeros_precedents=0;
    
	function Edge($pays=null,$magazine=null,$numero=null) {
        if (is_null($pays))
            return;
        $this->pays=$pays;$this->magazine=$magazine;$this->numero=$numero;
        if (file_exists('edges/'.$this->pays.'/'.$this->magazine.'.edge.class.php')) {
            require_once('edges/'.$this->pays.'/'.$this->magazine.'.edge.class.php');
            $this->o=new $this->magazine($this->numero);
            $intervalle_validite=new IntervalleValidite($this->o->intervalles_validite);
            if (!$intervalle_validite->estValide($this->numero))
                $this->est_visible=false;
        }
        else {
            $this->o=clone $this;
            $this->est_visible=false;
            $this->largeur*=Edge::$grossissement;
            $this->hauteur*=Edge::$grossissement;
        }
	}

    static function getEtagereHTML($br=true) {
        $code= '<div class="etagere" style="width:'.Etagere::$largeur.';'
                                          .'background-image: url(\'edges/textures/'.Etagere::$texture2.'/'.Etagere::$sous_texture2.'.jpg\')">&nbsp;</div>';
        if ($br===true)
            $code.= '<br />';
        return $code;
    }
    function getImgHTML($regen=false) {
        $code='';
        if (Edge::$largeur_numeros_precedents + $this->o->largeur > Etagere::$largeur) {
            $code.=Edge::getEtagereHTML();
            Edge::$largeur_numeros_precedents=0;
        }
        if ($this->o->hauteur > Etagere::$hauteur_max_etage)
            Etagere::$hauteur_max_etage = $this->o->hauteur ;
        $code.= '<img class="tranche" ';
        $fichier_existe=file_exists('edges/'.$this->pays.'/gen/'.$this->magazine.'.'.$this->numero.'.png');
        if (!file_exists('edges/'.$this->pays.'/gen/'.$this->magazine.'.'.$this->numero.'.png') || $regen) {
            $code.='name="Edge.class.php?pays='.$this->pays.'&amp;magazine='.$this->magazine.'&amp;numero='.$this->numero.'&amp;grossissement='.Edge::$grossissement.'" ';
        }
        else {
            $code.='name="edges/'.$this->pays.'/gen/'.$this->magazine.'.'.$this->numero.'.png" ';
        }
        $code.='width="'.$this->o->largeur.'" height="'.$this->o->hauteur.'" />';
        Edge::$largeur_numeros_precedents+=$this->o->largeur;
        return $code;
    }

    function dessiner_tranche($regen=false) {
        $intervalle_validite=new IntervalleValidite($this->intervalles_validite);
        if ($intervalle_validite->estValide($this->numero))
            $this->image=$this->dessiner();
        else
            $this->image=$this->dessiner_defaut();
        $this->dessiner_contour();
        foreach($this->textes as $texte) {
            imagettftext($this->image,$texte->taille,$texte->angle,$texte->pos_x,$texte->pos_y,$texte->couleur,$texte->police,$texte->texte);
        }
        imageantialias($this->image, true);
        if (!is_dir('edges/'.$this->pays)) {
            mkdir('edges/'.$this->pays);
            mkdir('edges/'.$this->pays.'/gen');
        }
        $reduction=Edge::$grossissement/Edge::$grossissement_affichage;
        $largeur_image_finale=intval($this->largeur/$reduction);
        $hauteur_image_finale=intval($this->hauteur/$reduction);
        $image2=imagecreatetruecolor($largeur_image_finale, $hauteur_image_finale);
        imagecopyresampled($image2, $this->image, 0, 0, 0, 0, $largeur_image_finale, $hauteur_image_finale, $this->largeur, $this->hauteur);
        imagepng($image2,'edges/'.$this->pays.'/gen/'.$this->magazine.'.'.str_replace(' ','',$this->numero).'.png');
        imagepng($this->image);
    }

	function dessiner_defaut() {
        $this->image=imagecreatetruecolor($this->largeur,$this->hauteur);
        $blanc=imagecolorallocate($this->image,255,255,255);
        $noir = imagecolorallocate($this->image, 0, 0, 0);
        imagefilledrectangle($this->image, 0, 0, $this->largeur-2, $this->hauteur-2, $blanc);
        imagettftext($this->image,$this->largeur/3,90,$this->largeur*7/10,$this->hauteur-$this->largeur*4/5,
         $noir,'edges/Verdana.ttf','['.$this->pays.' / '.$this->magazine.' / '.$this->numero.']');
        imageantialias($this->image, true);
        return $this->image;
	}

    function getColorsFromDB($default_color=array(255,255,255),$parametre_autre=null) {
        include_once('Database.class.php');
        $d=new Database();
        $requete_couleurs='SELECT CouleurR, CouleurG, CouleurB FROM bibliotheque_options WHERE Pays LIKE \''.$this->pays.'\' AND Magazine LIKE \''.$this->magazine.'\' AND Numéro LIKE \''.$this->numero.'\'';
        if (!is_null($parametre_autre))
            $requete_couleurs.=' AND Autre LIKE \''.$parametre_autre.'\'';
        else
            $requete_couleurs.=' AND (Autre IS NULL || Autre LIKE \'\')';
        $resultat=$d->requete_select($requete_couleurs);
        if (count($resultat)==0)
            return $default_color;
        return array($resultat[0]['CouleurR'], $resultat[0]['CouleurG'], $resultat[0]['CouleurB']);
    }

    function getDataFromDB($default_text='') {
        include_once('Database.class.php');
        $d=new Database();
        $requete_couleurs='SELECT Autre FROM bibliotheque_options WHERE Pays LIKE \''.$this->pays.'\' AND Magazine LIKE \''.$this->magazine.'\' AND Numéro LIKE \''.$this->numero.'\'';
        $resultat=$d->requete_select($requete_couleurs);
        if (count($resultat)==0)
            return $default_text;
        return $resultat[0]['Autre'];
    }

    function agrafer() {
        $noir=imagecolorallocate($this->image, 0, 0, 0);
        for ($i=-.25*Edge::$grossissement;$i<.25*Edge::$grossissement;$i++) {
            imageline($this->image, $this->largeur/2 - $i, $this->hauteur/5, $this->largeur/2 - $i, $this->hauteur/4, $noir);
            imageline($this->image, $this->largeur/2 - $i, $this->hauteur*4/5, $this->largeur/2 - $i, $this->hauteur*4/5 - ($this->hauteur/4 - $this->hauteur/5), $noir);
        }
    }

    function placer_image($sous_image, $position='haut', $decalage=array(0,0), $compression_largeur=1, $compression_hauteur=1) {
        if (is_string($sous_image)) {
            $extension_image=strtolower(substr($sous_image, strrpos($sous_image, '.')+1,strlen($sous_image)-strrpos($sous_image, '.')-1));
            $fonction_creation_image='imagecreatefrom'.$extension_image.'_getimagesize';
            list($sous_image,$width,$height)=call_user_func($fonction_creation_image,$this->getChemin().'/'.$sous_image);
        }
        else {
            $width=imagesx($sous_image);
            $height=imagesy($sous_image);
        }
        $hauteur_sous_image=$this->largeur*($height/$width);
        if ($position=='bas') {
            $decalage[1]=$this->hauteur-$hauteur_sous_image-$decalage[1];
        }
        imagecopyresampled ($this->image, $sous_image, $decalage[0], $decalage[1], 0, 0, $this->largeur*$compression_largeur, $hauteur_sous_image*$compression_hauteur, $width, $height);
        return $sous_image;
    }

    function dessiner_contour() {
        $noir=imagecolorallocate($this->image, 0, 0, 0);
        for ($i=0;$i<.15*Edge::$grossissement;$i++)
            imagerectangle($this->image, $i, $i, $this->largeur-1-$i, $this->hauteur-1-$i, $noir);
    }

    static function getPourcentageVisible($get_html=false, $regen=false, $user_unique=true) {
        include_once('Database.class.php');
        @session_start();
        $d=new Database();
        if ($user_unique===true)
            $ids_users=array($d->user_to_id($_SESSION['user']));
        else {
            $pourcentages_visible=array();
            $requete_users='SELECT ID, username FROM users';
            $resultat_users=$d->requete_select($requete_users);
            foreach($resultat_users as $user)
                $ids_users[$user['username']]=$user['ID'];
        }
        foreach($ids_users as $username=>$id_user) {
            $l=$d->toList($id_user);
            $texte_final='';
            $total_numeros=0;
            $total_numeros_visibles=0;
            foreach($l->collection as $pays=>$magazines) {
                foreach($magazines as $magazine=>$numeros) {
                    if ($get_html === true)
                        sort($numeros);
                    $total_numeros+=count($numeros);
                    foreach($numeros as $numero) {
                        if ($get_html) {
                            list($texte,$est_visible)=getEstVisible($pays, $magazine, $numero[0],true, $regen);
                            $texte_final.=$texte;
                        }
                        else
                            $est_visible=getEstVisible($pays, $magazine, $numero[0]);
                        if ($est_visible===true)
                            $total_numeros_visibles++;
                    }
                }
            }
            $pourcentage_visible=$total_numeros==0 ? 0 : intval(100*$total_numeros_visibles/$total_numeros);
            if ($user_unique===true) {
                if ($get_html)
                    return array($texte_final, $pourcentage_visible);
                else
                    return $pourcentage_visible;
            }
            elseif ($total_numeros>0)
                $pourcentages_visible[' '.$username]=$pourcentage_visible;
        }
        return $pourcentages_visible;
    }

    function getChemin() {
        return 'edges/'.$this->pays.'/elements';
    }

}
if (isset($_POST['get_visible'])) {
    include_once('Database.class.php');
    include_once ('locales/lang.php');
    $d=new Database();
    if (!$d) {
        echo PROBLEME_BD;
        exit(-1);
    }
    list($nom_complet_pays,$nom_complet_magazine)=$nom_complet_magazine=$d->get_nom_complet_magazine($_POST['pays'], $_POST['magazine']);
    ?>
    <div class="titre_magazine"><?=utf8_encode($nom_complet_magazine)?></div><br />
    <div class="numero_magazine">n&deg;<?=$_POST['numero']?></div><br />
    <?php
    if (!getEstVisible($_POST['pays'], strtoupper($_POST['magazine']), $_POST['numero'])) {
        ?>
        <?=TRANCHE_NON_DISPONIBLE1?><br /><?=TRANCHE_NON_DISPONIBLE2?><a class="lien_participer" target="_blank" href="?action=bibliotheque&onglet=participer"><?=ICI?></a><?=TRANCHE_NON_DISPONIBLE3?>
        <?php
    }
}
elseif (isset($_GET['pays']) && isset($_GET['magazine']) && isset($_GET['numero'])) {
    if (isset($_GET['grossissement']))
        Edge::$grossissement=$_GET['grossissement'];
    if (!isset($_GET['debug']))
        header('Content-type: image/png');
    $e=new Edge($_GET['pays'],$_GET['magazine'],$_GET['numero']);
    $o=$e->o;
    $regen=isset($_GET['regen']);
    $o->dessiner_tranche($regen);
}
/*
 * Table bibliotheque_options
*/
elseif (isset($_POST['get_texture'])) {
    include_once('Database.class.php');
    $d=new Database();
    if (!$d) {
        echo PROBLEME_BD;
        exit(-1);
    }
    $id_user=$d->user_to_id($_SESSION['user']);
    $requete_texture='SELECT Bibliotheque_Texture'.$_POST['n'].' FROM users WHERE ID LIKE \''.$id_user.'\'';
    $resultat_texture=$d->requete_select($requete_texture);
	$rep = "edges/textures";
    $dir = opendir($rep);
    while ($f = readdir($dir)) {
        if( $f!=='.' && $f!=='..') {
            ?>
            <option 
            <?php
            if ($f==$resultat_texture[0]['Bibliotheque_Texture'.$_POST['n']])
                echo 'selected="selected" ';?>
            value="<?=$f?>"
            ><?=constant('TEXTURE__'.strtoupper($f))?></option>
            <?php
        }
    }
}

elseif (isset($_POST['get_sous_texture'])) {
    include_once('Database.class.php');
    $d=new Database();
    if (!$d) {
        echo PROBLEME_BD;
        exit(-1);
    }
    $id_user=$d->user_to_id($_SESSION['user']);
    $requete_texture='SELECT Bibliotheque_Sous_Texture'.$_POST['n'].' FROM users WHERE ID LIKE \''.$id_user.'\'';
    $resultat_texture=$d->requete_select($requete_texture);

	$rep = 'edges/textures/'.$_POST['texture'].'/miniatures';
    $dir = opendir($rep);
    while ($f = readdir($dir)) {
        if( $f!=='.' && $f!=='..') {
            $nom_sous_texture=substr($f,0, strrpos($f, '.'));
            ?>
            <option <?php
            if ($nom_sous_texture==$resultat_texture[0]['Bibliotheque_Sous_Texture'.$_POST['n']])
                echo 'selected="selected" ';?>
            style="background:url('edges/textures/<?=$_POST['texture']?>/miniatures/<?=$f?>') no-repeat scroll center right transparent">
                <?=$nom_sous_texture?>
            </option><?php
        }
    }
}
elseif (isset($_GET['regen'])) {
    ?>
            <html><head><style type="text/css">img {margin-left:-4px;}</style></head><body>
    <?php
    $pays=$_GET['pays'];
    $magazine=$_GET['magazine'];
    if (isset($_GET['debut'])) {
        $numeros=array('debut'=>$_GET['debut'], 'fin'=>$_GET['fin']);
    }
    include_once('edges/'.$pays.'/'.$magazine.'.edge.class.php');
    $o=new $magazine(0);
    $iv=new IntervalleValidite($o->intervalles_validite);
    if (isset($_GET['debut'])) {
        $liste_numeros=array();
        for ($i=$_GET['debut'];$i<=$_GET['fin'];$i++)
            //if ($iv->estValide($i))
                $liste_numeros[]=$i;
    }
    else
        $liste_numeros=$iv->getListeNumeros();
    foreach($liste_numeros as $numero) {?>
        <img src="Edge.class.php?grossissement=1.5&regen=true&pays=<?=$pays?>&magazine=<?=$magazine?>&numero=<?=$numero?>" />
    <?php }
    ?></body></html><?php
}
elseif (isset($_GET['dispo_tranches'])) {
    $data=Edge::getPourcentageVisible(false, false, false);
    asort($data);
    $usernames=array_keys($data);
    sort($data);
    $somme=0;
    foreach($data as $pct)
        $somme+=$pct;
    $moyenne=$somme/count($data);
    $data_moyenne=array();
    for($i=0;$i<count($data);$i++)
        $data_moyenne[]=$moyenne;

    include 'OpenFlashChart/php-ofc-library/open-flash-chart.php';

    $chart = new open_flash_chart();
    $chart->set_title( new title( 'Disponibilite des tranches' ) );

    //
    // Make our area chart:
    //
    $area = new area();
    // set the circle line width:
    $area->set_width( 2 );
    $area->set_default_dot_style( new hollow_dot() );
    $area->set_colour( '#838A96' );
    $area->set_fill_colour( '#E01B49' );
    $area->set_fill_alpha( 0.4 );
    $area->set_values( $data );
    $t=new tooltip( "Utilisateur #x_label<br>#val#" );

    // add the area object to the chart:
    $chart->add_element( $area );
    
    $line_dot = new line();
    $line_dot->set_values($data_moyenne);
    $line_dot->set_tooltip( $t );
    $chart->add_element( $line_dot );

    $y_axis = new y_axis();
    $y_axis->set_range( 0, 100, 10 );
    $y_axis->labels = null;
    $y_axis->set_offset( false );

    $chart->add_y_axis( $y_axis );

    $x_labels = new x_axis_labels();
    $x_labels->set_vertical();
    $x_labels->set_colour( '#A2ACBA' );
    $x_labels->set_labels($usernames);
    
    $x = new x_axis();
    $x->set_colour( '#A2ACBA' );
    $x->set_grid_colour( '#D7E4A3' );
    $x->set_offset( false );
    $x->set_labels( $x_labels );
    
    $chart->set_x_axis( $x );

    ?>
        <html>
            <head>
            <link rel="stylesheet" type="text/css" href="style.css">
            <!--[if IE]>
                    <style type="text/css" media="all">@import "fix-ie.css";</style>
            <![endif]-->
            <script type="text/javascript" src="js/json/json2.js"></script>
            <script type="text/javascript" src="js/swfobject.js"></script>
            <script type="text/javascript">
            swfobject.embedSWF("open-flash-chart.swf", "my_chart", "<?=(25*count($usernames))?>", "380", "9.0.0");
            </script>

            <script type="text/javascript">

            function open_flash_chart_data()
            {
                return JSON.stringify(data);
            }

            function findSWF(movieName) {
              if (navigator.appName.indexOf("Microsoft")!= -1) {
                return window[movieName];
              } else {
                return document[movieName];
              }
            }

            var data = <?php echo $chart->toPrettyString(); ?>;

            </script>
            </head>
            <body>
                <div id="my_chart"></div>
            </body>
        </html>
        <?php
}

function getEstVisible($pays,$magazine,$numero, $get_html=false, $regen=false) {
    $e=new Edge($pays, $magazine, $numero);
    if ($get_html)
        return array($e->getImgHTML($regen),$e->est_visible);
    else
        return $e->est_visible;
}

function imagecreatefrompng_getimagesize($chemin) {
    $image=imagecreatefrompng($chemin);
    return array($image,imagesx($image),imagesy($image));
}

function imagecreatefromgif_getimagesize($chemin) {
    $image=imagecreatefromgif($chemin);
    return array($image,imagesx($image),imagesy($image));
}

function imagepalettetotruecolor(&$img) {
    if (!imageistruecolor($img))
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $img1 = imagecreatetruecolor($w,$h);
        imagecopy($img1,$img,0,0,0,0,$w,$h);
        $img = $img1;
    }
}

function rgb2hex($r,$g,$b) {
    $hex = "";
    $rgb=array($r,$g,$b);
    for ($i = 0; $i < 3; $i++) {
        if (($rgb[$i] > 255) || ($rgb[$i] < 0)) {
            echo "Error : input must be between 0 and 255";
            return 0;
        }
        $tmp = dechex($rgb[$i]);
        if (strlen($tmp) < 2)
            $hex .= "0" . $tmp;
        else
            $hex .= $tmp;
    }
    return $hex;
}
?>