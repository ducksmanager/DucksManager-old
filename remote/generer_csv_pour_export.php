<?php

$database=null;

include_once('auth.php');

function exporter($requete, $nomCsv)
{
    $result = mysql_query($requete);
    if ($result) {
        $num_fields = mysql_num_fields($result);
        $headers = array();
        for ($i = 0; $i < $num_fields; $i++) {
            $headers[] = mysql_field_name($result, $i);
        }
        $fp = fopen('export/'.$nomCsv, 'w');
        if ($fp && $result) {
            fputcsv($fp, $headers);
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                fputcsv($fp, array_values($row));
            }
        }
    }
}

exporter("
      SELECT ID_Utilisateur, CONCAT(Pays,'/', Magazine) AS Publicationcode, Numero
      FROM numeros
  ",
  'numeros.csv'
);

exporter("
      SELECT ID_user, NomAuteurAbrege
      FROM auteurs_pseudos
      WHERE DateStat IS NULL AND NomAuteurAbrege <> ''
  ",
  'auteurs_pseudos.csv'
);