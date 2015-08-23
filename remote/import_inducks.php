<?php

function flattenpublicationcode($element) {
    return $element['publicationcode'];
}

include_once('../Util.class.php');

$action = $argv[1];

$properties = parse_ini_file('/home/ducksmanager/ducksmanager.properties');

switch($action) {
    case 'clean':
        $no_database = true;
        $sql = Util::lire_depuis_fichier($properties['isv_path'] . '/../createtables.sql');
        $sql = preg_replace('#DROP TABLE IF EXISTS induckspriv[^;]+;#is', '', $sql);
        $sql = preg_replace('#RENAME TABLE induckspriv[^;]+;#is', '', $sql);
        $sql = preg_replace('#CREATE TABLE IF NOT EXISTS induckspriv[^;]+;#is', '', $sql);
        $sql = preg_replace('#LOAD DATA LOCAL INFILE "\./isv/induckspriv[^;]+;#is', '', $sql);
        $sql = preg_replace('#CREATE TABLE induckspriv_[^;]+;#is', '', $sql);
        $sql = preg_replace('#\# SQL for re-creating and filling table induckspriv_[a-z]*#is', '', $sql);
        $sql = preg_replace('#ALTER TABLE ([^)]+) ADD FULLTEXT#i', "ALTER TABLE $1 ENGINE = MYISAM;\n$0", $sql);
        $sql = preg_replace('#(\# End of file)\n*$#i',
                            "\nALTER TABLE inducks_entry ADD FULLTEXT INDEX entryTitleFullText(title);\n\n$1\n", $sql);
        Util::ecrire_dans_fichier($properties['isv_path'] . '/../createtables_clean.sql', $sql, false);

    break;
    case 'check_removed_publications':
        @include_once('../Inducks.class.php');

        $requete_liste_magazines_utilisateurs = 'SELECT DISTINCT CONCAT(Pays,"/",Magazine) as publicationcode, COUNT(*) AS cpt FROM numeros GROUP BY CONCAT(Pays,"/",Magazine)';
        $resultats=Inducks::requete_select($requete_liste_magazines_utilisateurs,'db301759616','ducksmanager.net');

        $publication_codes = array_map('flattenpublicationcode', $resultats);
        $issues_cpt = array();
        foreach($resultats as $resultat) {
            $issues_cpt[$resultat['publicationcode']] = $resultat['cpt'];
        }

        DatabasePriv::connect('coa');
        $requete = 'SELECT publicationcode FROM inducks_publication WHERE publicationcode IN (\''.implode('\',\'', $publication_codes).'\') ';
        $resultats = DM_Core::$d->requete_select($requete);

        $existing_publication_codes = array_map('flattenpublicationcode', $resultats);
        $missing_publication_codes = array_diff($publication_codes, $existing_publication_codes);

        $issues_cpt = array_intersect_key($issues_cpt, array_flip($missing_publication_codes));

        if (count($issues_cpt) > 0) {
            echo print_r($issues_cpt, true);
        }
    break;
}
