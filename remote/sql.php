<?php
if (isset($_GET['dbg'])) {
	error_reporting(E_ALL);
}
$database= $_GET['db'] ?? 'coa';

include_once 'auth.php';

if (isset($_GET['req'])) {
	$requete=str_replace("\'","'",$_GET['req']);
	$resultats_tab= [];
	if (isset($_GET['params'])) {
	    $params = json_decode($_GET['params']);
        $statement = Database::$handle->prepare($requete);
        if ($statement !== false) {
            if (count($params) > 0) {
                $types = implode('', array_map(function($typeAndValue) {
                    return $typeAndValue->type;
                }, $params));
                $values = array_map(function($typeAndValue) {
                    return $typeAndValue->value;
                }, $params);
                $statement->bind_param($types, ...$values);
            }
            if($statement->execute()) {
                $resultats = $statement->get_result();
            }
        }
    }
	else {
        $resultats=Database::$handle->query($requete);
    }
	if (is_null($resultats)) {
        http_response_code(400);
    }
	else {
        $debut=true;
        $champs= [];
        while($resultat = $resultats->fetch_array(MYSQLI_ASSOC)) {
            if ($debut) {
                foreach(array_keys($resultat) as $cle) {
                    if (!is_int($cle)) {
                        $champs[] = $cle;
                    }
                }
                $debut=false;
            }
            $valeurs= [];
            foreach($resultat as $cle=>$valeur) {
                if (!is_int($cle)) {
                    $valeurs[$cle] = utf8_encode($valeur);
                }
            }
            $resultats_tab[]=$valeurs;
        }
        $resultats_tab= [$champs,$resultats_tab];
        if (isset($_GET['debug'])) {
            ?><pre><?php echo $requete."\n";print_r($resultats_tab);?></pre><?php
        }
        else {
            header('Content-Type: text/html; charset=utf-8');
            echo serialize($resultats_tab);
        }
    }
	mysqli_close(Database::$handle);
}
else {
    echo 'Pas de requete';
}