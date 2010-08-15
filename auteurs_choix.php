<?php
if (isset($_POST['value'])) {
	require_once('Util.class.php');
	$valeur=$_POST['value'];
	$premiere_lettre=substr($valeur,0,1);
	if ($premiere_lettre>='a' && $premiere_lettre <='z')
		$premiere_lettre=strtoupper($premiere_lettre);
	if ($premiere_lettre>='A' && $premiere_lettre <='Z')
		$url='http://coa.inducks.org/legend-creator.php?start='.$premiere_lettre.'&sortby=&filter=';
	else
		$url='http://coa.inducks.org/legend-creator.php?start=1&sortby=&filter=';
	$regex_auteur='#<a href="creator\.php\?c=([^"]+)">([^<]+)</a><td>[^<]*<td>[^<]*<td>[^<]*<td>([^<]*)<tr>#is';
	$page=Util::get_page($url);
	preg_match_all($regex_auteur,$page,$auteurs);   
	$i=0;  
	echo '<ul class="contacts">';
	foreach($auteurs[0] as $auteur) {
		$activites_auteur=preg_replace($regex_auteur,'$3',$auteur);
		$liste_activites_auteur=explode(', ',$activites_auteur);
		$nom_auteur=preg_replace($regex_auteur,'$2',$auteur);
		$id_auteur=preg_replace($regex_auteur,'$1',$auteur);
		if (strtolower(substr($nom_auteur,0,strlen($valeur)))==strtolower($valeur)) {
			echo '<li class="contact">'//<div class="image"><img width="32" src="images/'.$nom_auteur.'-mini.jpg"/></div>'
				.'<div class="nom"><span>'.$nom_auteur.'</span><span style="display:none" title="'.$id_auteur.'"</div>
				</li>';
			if (++$i >= 10)
				die('<li>...</li></ul>');
		}		
	}
	echo '</ul>';
	die();
}