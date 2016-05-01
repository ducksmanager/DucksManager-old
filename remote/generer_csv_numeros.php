<?php

$database=null;

include_once('auth.php');

$result = mysql_query("SELECT ID_Utilisateur, CONCAT(Pays,'/', Magazine) AS Publicationcode, Numero FROM numeros");
if ($result) {
    $num_fields = mysql_num_fields($result);
    $headers = array();
    for ($i = 0; $i < $num_fields; $i++) {
        $headers[] = mysql_field_name($result , $i);
    }
    $fp = fopen('export/numeros.csv', 'w');
    if ($fp && $result) {
        fputcsv($fp, $headers);
        while($row=mysql_fetch_array($result, MYSQL_ASSOC)) {
            fputcsv($fp, array_values($row));
        }
        exit;
    }
}