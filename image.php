<?
global $largeur;global $hauteur;global $petit_rayon;global $centre_spirale_x;global $centre_spirale_y;
$largeur=290;
$hauteur=290;
$epaisseur=16;
$petit_rayon=81;

$possessions_init=explode(',',$_GET['chaine']);
$possessions=array();
foreach($possessions_init as $indice=>$possession) {
	list($id,$numero,$etat,$date,$couleur)=$array_temp=explode('!',$possessions_init[$indice]);
        $possessions[]=array('ID'=>$id,
                             'Numero'=>$numero,
                             'Etat'=>$etat,
                             'Date d\'acquisition'=>$date,
                             'Couleur'=>$couleur); // Les indices du tableau correspondent aux num�ros
}
$max_centaines=1;
foreach($possessions as $numero) {
	$numero=$numero['Numero'];
	if (intval($numero/100)>$max_centaines)
		$max_centaines=intval($numero/100);
}
$max_centaines++;
$largeur+=30*$max_centaines;
$hauteur+=30*$max_centaines;

$centre_spirale_x = $largeur/2;
$centre_spirale_y = $hauteur/2;

if (!isset($_GET['dbg']))
	header ("Content-type: image/jpeg");
$image = imagecreate($largeur,$hauteur);
$blanc = imagecolorallocate($image, 255, 255, 255);
$noir = imagecolorallocate($image, 0, 0, 0);
$bleu = imagecolorallocate($image, 0, 0, 255);
$cyan = imagecolorallocate($image, 0, 255, 255);
$magenta = imagecolorallocate($image, 255, 0, 255);

//$max_indices_num=($max<100? $max : 100);
$max_indices_num=100;
for ($i=1;$i<$max_indices_num;$i+=3) {
	$p_texte=num_vers_Point($i,0,$epaisseur);
	$p_ligne1_1=num_vers_Point($i,0,$epaisseur);
	$p_ligne1_2=num_vers_Point($i+1,0,$epaisseur);
	$p_ligne2_1=num_vers_Point($i,0,$epaisseur);
	$p_ligne2_2=num_vers_Point($i+1,0,$epaisseur);
	ImageString($image,2,$p_texte['x'],$p_texte['y'],$i,$noir);
	ImageLine($image,$p_ligne1_1['x'], $p_ligne1_1['y'], $p_ligne1_2['x'], $p_ligne1_2['y'],$magenta);
	ImageLine($image,$p_ligne2_1['x'], $p_ligne2_1['y'], $p_ligne2_2['x'], $p_ligne2_2['y'],$magenta);
}
foreach($possessions as $numero) {
	$etat=$numero['Etat'];
	$numero=$numero['Numero'];
	/*if (isset($_GET['dbg'])) {

		if (array_key_exists($numero,$possessions))
			echo $numero.' existe';
		else
			echo $numero.' n\'existe pas';

	}*/
	$cpt=0;
	if ($numero%100==0) { // Affiche une ligne �paissie � la ligne des multiples de 500
		$cpt++;
		$p=num_vers_Point($numero,+0.1,$epaisseur);
		$p2=num_vers_Point($numero+1,0.1,$epaisseur);
		ImageLine ($image, $p['x'], $p['y'], $p2['x'], $p2['y'], $noir);
	}

	$p=num_vers_Point($numero,0,$epaisseur);
	$p2=num_vers_Point($numero+1,0,$epaisseur);
	$p3=num_vers_Point($numero+100,0,$epaisseur);
	$p4=num_vers_Point($numero+101,0,$epaisseur);
	$x=array($p['x'],$p['y'],$p2['x'],$p2['y'],$p4['x'],$p4['y'],$p3['x'],$p3['y']);

	$coul=etat_to_color($etat,$image);

	imagefilledpolygon($image, $x, 4, $coul);

	imagepolygon($image, $x, 4, $noir);
}
ImageJpeg($image);

function num_vers_Point ($num,$modif_ecart,$epaisseur) {
	global $petit_rayon;global $centre_spirale_x;global $centre_spirale_y;

	$num_centaines=0; $num_restant=$num;

	while ($num_restant>100) {
		$num_centaines++;
		$num_restant-=100;
	}
	$angle = 2*3.14159*(100-$num_restant)/100;

	$rayon=$petit_rayon+($modif_ecart*$epaisseur)+$epaisseur*$num_centaines+(0.01*$num_restant)*$epaisseur;

	$point=array();
	$point['x']=intval($centre_spirale_x-$rayon*sin($angle));
	$point['y']=intval($centre_spirale_y-$rayon*cos($angle));
	return $point;
}
function etat_to_color($etat,$image) {
	if ("Mauvais"==$etat)
		return imagecolorallocate($image, 202, 202, 255);
	if ("Moyen"==$etat)
		return imagecolorallocate($image, 136, 136, 255);
	if ("Correct"==$etat)
		return imagecolorallocate($image, 83, 83, 255);
	if ("Excellent"==$etat)
		return imagecolorallocate($image, 0, 0, 255);
	return imagecolorallocate($image, 128, 128, 0);
}