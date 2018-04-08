<?php
if (isset($_POST['value'])) {
	require_once 'Util.class.php';
	require_once 'Inducks.class.php';
	$valeurs=$_POST['value'];
	$liste_auteurs= [];
	foreach(explode(' ',$valeurs) as $mot) {
		$requete_auteur='SELECT personcode, fullname FROM inducks_person '
					   .'WHERE LOWER(fullname) LIKE \'%'.$mot.'%\' ';
		$resultats_auteur=Inducks::requete_select($requete_auteur);
		$liste_auteurs=array_merge($liste_auteurs,$resultats_auteur);
	}
	usort($liste_auteurs,function($auteur1,$auteur2) {
		if ($auteur1['fullname'] < $auteur2['fullname']) {
            return 0;
        }
		return  $auteur1['fullname'] < $auteur2['fullname'] ? -1 : 1;
	});
	?><ul class="contacts"><?php
	foreach($liste_auteurs as $auteur) {
		?>
        <li class="contact">
			<div class="nom">
				<span><?=$auteur['fullname']?></span> 
				<span style="display: none" name="nom_auteur" title="<?=$auteur['personcode']?>"></span>
			</div>
		</li><?php
	}
	?></ul><?php
}