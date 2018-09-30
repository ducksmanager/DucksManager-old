<?php

include_once 'auth.php';
require_once '../DucksManager_Core.class.php';

/**
 * @param string $requete
 * @param string $cheminCsv
 * @return bool
 */
function exporter($requete, $cheminCsv)
{
    $results = DM_Core::$d->requete_select($requete);
    if (count($results) > 0) {
        $fp = fopen($cheminCsv, 'w');
        if ($fp) {
            fputcsv($fp, array_keys($results[0]));
            foreach($results as $result) {
                fputcsv($fp, array_values($result));
            }
            return true;
        }
    }
    return false;
}

if (isset($_GET['csv'])) {
    $csv = $_GET['csv'];
    switch($csv) {
        case 'numeros':
            $requete = "
                SELECT ID_Utilisateur, CONCAT(Pays,'/', Magazine) AS Publicationcode, Numero
                FROM numeros";
        break;
        case 'auteurs_pseudos':
            $requete = "
                SELECT ID_user, NomAuteurAbrege, Notation
                FROM auteurs_pseudos
                WHERE DateStat IS NULL AND NomAuteurAbrege <> ''";
        break;
    }

    if (isset($requete)) {
        $cheminCsv = "export/$csv.csv";
        if (exporter($requete, $cheminCsv)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename("$csv.csv").'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($cheminCsv));
            readfile($cheminCsv);
        }
    }
}