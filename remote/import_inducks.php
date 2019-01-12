<?php

function flattenpublicationcode($element) {
    return $element['publicationcode'];
}

$action = $argv[1];

switch($action) {
    case 'check_removed_publications':
        @include_once '../Inducks.class.php';

        $requete_liste_magazines_utilisateurs = 'SELECT DISTINCT CONCAT(Pays,"/",Magazine) as publicationcode, COUNT(*) AS cpt FROM numeros GROUP BY CONCAT(Pays,"/",Magazine)';
        $resultats=DM_Core::$d->requete_select($requete_liste_magazines_utilisateurs);

        $publication_codes = array_map('flattenpublicationcode', $resultats);
        $issues_cpt = [];
        foreach($resultats as $resultat) {
            $issues_cpt[$resultat['publicationcode']] = $resultat['cpt'];
        }

        $requete = 'SELECT publicationcode FROM inducks_publication WHERE publicationcode IN (\''.implode('\',\'', $publication_codes).'\') ';
        $resultats = DM_Core::$d->requete_select($requete, [], 'db_coa');

        $existing_publication_codes = array_map('flattenpublicationcode', $resultats);
        $missing_publication_codes = array_diff($publication_codes, $existing_publication_codes);

        $issues_cpt = array_intersect_key($issues_cpt, array_flip($missing_publication_codes));

        if (count($issues_cpt) > 0) {
            echo print_r($issues_cpt, true);
        }
    break;
}
