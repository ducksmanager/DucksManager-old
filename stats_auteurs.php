<?php
require_once('Database.class.php');
require_once('Liste.class.php'); 
$debut=microtime(true);
$pays='fr';
$d=new Database();
if (!$d) {
	echo 'Probl&egrave;me avec la base de donn&eacute;es !';
	exit(-1);
}
$requete_avancement_stats='SELECT Auteur,Page FROM traitement_stats';
$requete_avancement_stats_resultat=$d->requete_select($requete_avancement_stats);
$auteur_en_cours=$requete_avancement_stats_resultat[0]['Auteur'];
$page_en_cours=$requete_avancement_stats_resultat[0]['Page'];
echo $auteur_en_cours.' '.$page_en_cours; 
/*$requete='SELECT MAX(ID) FROM users';
$max_resultat=$d->requete_select($requete);
$max=$max_resultat[0][0];
for($i=1;$i<$max;$i++) {
	$l=new Liste();
	$d->toList($i);
}*/
$resultats=$d->requete_select('SELECT NomAuteur,ID_user FROM auteurs_pseudos');
$auteurs_demandes=array();
foreach($resultats as $auteur) {
	if (array_key_exists($auteur['NomAuteur'],$auteurs_demandes)) {
		array_push($auteurs_demandes[$auteur['NomAuteur']],array('user'=>$auteur['ID_user'],'cpt'=>0));
		continue;
	}
	else {
		$auteurs_demandes[$auteur['NomAuteur']]=array(0=>array('user'=>$auteur['ID_user'],'cpt'=>0));
	}
}
echo '<pre>';print_r($auteurs_demandes);echo '</pre>';
//$users_demandeurs=array();
//$requete_users_demandeurs='SELECT DISTINCT ID_user FROM auteurs_pseudos';
//$resultat_requete_users_demandeurs=mysql_query($requete_users_demandeurs);
//foreach($resultat_requete_users_demandeurs as $user) {
//	array_push($user);
//}
foreach($auteurs_demandes as $auteur=>$users) {
	if ($auteur_en_cours!='Aucun') {
		echo 'Reprise des stats pour l\'auteur '.$auteur_en_cours.'<br />';
	}
	$requete_nb_histoires='SELECT NbHistoires_tmp FROM traitement_stats WHERE Auteur LIKE \''.$auteur.'\'';
	$resultat_requete_nb_histoires=$d->requete_select($requete_nb_histoires);
	$total_histoires=$resultat_requete_nb_histoires[0]['NbHistoiresTmp'];
	echo $total_histoires;
	$nom_auteur=$auteur;
	echo '<u>'.$nom_auteur.'</u><br />';
	$adresse_auteur='http://coa.inducks.org/comp2.php?code=&keyw=&keywt=i&exactpg=&pg1=&pg2=&bro2=&bro3=&kind=0&rowsperpage=0&columnsperpage=0&hero=&xapp=&univ=&xa2=&creat='.$auteur.'&creat2=&plot=&plot2=&writ=&writ2=&art=&art2=&ink=&ink2=&pub1=&pub2=&part=&ser=&xref=&mref=&xrefd=&repabb=&repabbc=al&imgmode=0&vdesc2=on&vdesc=en&vfr=on&sort1=auto';
	$regex_code_histoire='#<A HREF="story.php\?c=[^"]+"><font courier>([^<]+)</font></A>#'; 
	$regex_histoire_code_personnages='#<tr[^>]+>.<td[^>]+><[^>]+><[^>]+><br>.<A[^>]+><[^>]+>([^<]+)</font></A> </td>.<td>[ ]*(?:<[^>]+>)?(<A [^<]+</A>[, ]*)*[^<]*(?:</small>)?(?:(?:(?:<i>)?[^<]*(?:<span[^<]*</span>[ ]*)*[^<]*</i>)?<br>)?.(?:.<i>(?:[^<]*)</i>)?(?:<br>.)?</td>.<td>(?:[^<]*<br>.)?<small>(?:[^<]*<br>)*[^<]*</small></td>.<td>(?:[^<]*<A [^>]+>(?:(?:<span [^>]+>)?[^<]*(?:</span>[ ]*)?)*</A>[()?*, ]*)+(?:<font [^<]+</font>)?[^<]*(?:<br>.)?(?:<font[^<]*</font><br>.)?</td>.<td>(([^<]*(<A [^<]*</A>[, ]*)*(?:<br>.?)?[^<]*)*)</td><td>(?:(?:[^<]*(?:<(?:A|i)[^<]*</(?:A|i)>)+[.()0-9a-zA-Z, ]*)*<br>.?)*#is';
	$regex_numero='#<A HREF="issue.php\?c=[^"]*">([^<]*)</A>#';
	
	list($nb_codes,$nb,$buffer,$codes,$histoires)=liste_histoires($adresse_auteur,$regex_code_histoire,$regex_histoire_code_personnages);
   	echo '<br /><u>'.$auteur.'</u> : <br />'; 
   	echo 'Page 1 : '.$nb.'/'.$nb_codes.' total<br />';
   	$requete_maj_avancement='UPDATE traitement_stats SET Auteur=\''.$auteur.'\',Page=0';
   	$d->requete($requete_maj_avancement);
   	if ($page_en_cours==-1||$page_en_cours==0||$auteur_en_cours!=$auteur)
   		$page=1;
   	else {
   		$page=$page_en_cours+1;
   		echo 'Reprise à la page '.($page_en_cours+1).'<br />';
   	}
   	$trouve=true;
   	while ($trouve) {
	   	if ($page_en_cours!=-1)//&&$page_en_cours==$page)
   			$regex_requete='#input type=hidden name=queryDirect value="([^"]*)"#is';
	   	$trouve=(preg_match($regex_requete,$buffer,$req)!=0); 
	   	$adresse_auteur2='http://coa.inducks.org/comp2.php?imgmode=0&owned=&noowned=&pageDirecte='.$page.'&c2Direct=en&c3Direct=fr&queryDirect='
	   					.urlencode(preg_replace($regex_requete,'$1',$req[0]));
	   	//echo $adresse_auteur2;
	   	list($nb_codes,$nb,$buffer,$codes,$histoires)=liste_histoires($adresse_auteur2,$regex_code_histoire,$regex_histoire_code_personnages);
	   	echo 'Page '.($page+1).' : '.$nb.'/'.$nb_codes.' total<br />';
	   	if ($page!=100) {
		   	//echo '<table border="1">';
		   	foreach($codes as $i=>$code) {
		   		$possession=array();
		   		foreach($users as $info_user)
		   			$possession[$infos_user['user']]=false;
		   		
		   		//echo '<tr><td>';
		   		$date_et_publications=preg_replace($regex_histoire_code_personnages,'$3',$histoires[0][$i]);
		   		//echo $date_et_publications;//echo preg_replace($regex_histoire_code_personnages,'<span style="background-color:#444499;">$1, $2, $3, $4, $5, $6, $7, $8, $9, $10</span>',$histoires[0][$i]);
		   		$nb_publications=preg_match_all($regex_numero,$date_et_publications,$publications);
		   		$liste_publications_texte='';
		   		foreach($publications[0] as $publication) {
		   			$magazine_numero=explode(' ',preg_replace($regex_numero,'$1',$publication));
		   			$magazine=$magazine_numero[0];
		   			$numero=$magazine_numero[1].$magazine_numero[2];
		   			foreach($users as $id=>$user) {
		   				$cpt_user=$user['cpt'];
		   				$id_user=$user['user'];
		   				$l=$d->toList($id_user);
			   			//$l->afficher('debug'); 
			   			if ($l->est_possede($pays,$magazine,$numero)) {
			   				$possession[$id_user]=true;
			   			}
		   			}
		   			$liste_publications_texte.= $magazine.' '.$numero.'<br />';
		   		}
		   		/*echo $liste_publications_texte;
				echo '<pre>';print_r($possession);echo '</pre>';
				echo '</td>';
		   		echo '<td>';
		   		echo preg_replace($regex_code_histoire,'<span style="background-color:#444499;">$1</span>',$code);
		   		echo '</td></tr>';*/
		   		
		   		foreach($possession as $id_user=>$possede)
		   			if ($possede)
		   				$auteurs_demandes[$auteur][$id_user]['cpt']++;
		   		$total_histoires++;
		   	}
		   //	echo '</table>';
	   	}
	   	
		$fin=microtime(true);
		echo (($fin-$debut)).' secondes écoulées !<br />';
		if ($fin-$debut>=25) {
			$requete_maj_avancement='UPDATE traitement_stats SET Auteur=\''.$auteur.'\',Page='.$page;
   			$d->requete($requete_maj_avancement); 
   			$requete_maj_nb_histoires='UPDATE traitement_stats SET NbHistoires_tmp='.$total_histoires;
   			$d->requete($requete_maj_nb_histoires);
   			echo 'stop !!'.(($fin-$debut)).' secondes écoulées !<br />';
   			echo 'Auteur en cours : '.$auteur.', page en cours : '.$page;
			exit(0);
			
		}
	   	$page++;
   	}
    echo '<u>'.$auteur.' : </u>'.$total_histoires.'<br />';
	echo '<pre>';print_r($auteurs_demandes);echo '</pre>'; 	
	
}
$requete_maj_avancement='UPDATE traitement_stats SET Auteur=\'Aucun\',Page=-1';
$d->requete($requete_maj_avancement);

function liste_histoires($adresse_auteur,$regex_code_histoire,$regex_histoire_code_personnages) {
	$nb_codes=$nb=0;$buffer="";$codes=array();$histoires=array();
	$handle = @fopen($adresse_auteur, "r");
	if ($handle) {
		$buffer="";
	   	while (!feof($handle)) {
	     	$buffer.= fgets($handle, 4096);
	   	}
	   	fclose($handle);
	   	$nb_codes=preg_match_all($regex_code_histoire,$buffer,$codes);
	   	$nb=preg_match_all($regex_histoire_code_personnages,$buffer,$histoires,PREG_PATTERN_ORDER);
	}
	else {
		echo 'Erreur de connexion &agrave; Inducks!';
		echo $adresse_auteur;
	}
	echo '<pre>';print_r($codes);echo '</pre>';
	return array($nb_codes,$nb,$buffer,$codes,$histoires);
}
?>