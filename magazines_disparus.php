<?php
require_once('DucksManager_Core.class.php');
require_once('Inducks.class.php');
require_once('Util.class.php');
$requete_tous_magazines_possedes='SELECT DISTINCT Pays,Magazine FROM numeros ORDER BY Pays, Magazine';
$resultat_tous_magazines_possedes=DM_Core::$d->requete_select($requete_tous_magazines_possedes);
$pays_tmp='';
foreach($resultat_tous_magazines_possedes as $pays_magazine) {
    $pays=$pays_magazine['Pays'];
    $magazine=$pays_magazine['Magazine'];
    if ($pays!==$pays_tmp) {
        $adresse_publications_pays='http://coa.inducks.org/country.php?xch=1&lg=4&c='.$pays;
        $buffer=Util::get_page($url);
    }
    $message_erreur='';
    $nom_complet_magazine=Inducks::get_nom_complet_magazine($pays, $magazine);
    $regex_nb_numeros='#<li><a href="publication.php\?c='.$pays.'/'.$magazine.'">[^<]+</a>&nbsp;<i>\(([^ ]+)#';
    preg_match($regex_nb_numeros,$buffer,$nb);
    if (!array_key_exists(1, $nb)) {
        echo $pays.'/'.$magazine.' n\'existe plus<br />';
        continue;
    }
    $pays_tmp=$pays;
}
?>
