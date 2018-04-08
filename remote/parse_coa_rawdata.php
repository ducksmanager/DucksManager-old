<?php
ob_start('ob_gzhandler');
$database='coa';

include_once '../ServeurDb.class.php';
include_once '../Util.class.php';
ServeurDb::connect($database);
if (isset($_GET['dbg'])) {
    echo 'Serveur : ' . ServeurDb::getProfilCourant()->server
        . ', User : ' . ServeurDb::getProfilCourant()->user
        . ', BD : ' . $database . "\n";
}

if (!isset($_GET['mdp']) || !ServeurDb::verifPassword($_GET['mdp'])) {
	echo 'Erreur d\'authentification';
	exit();
}

Database::$handle->query('SET NAMES UTF8');
if (isset($_GET['rawData_file'])) {
	$rawdatafile=$_GET['rawData_file'];
    $contenu=Util::get_page('https://www.ducksmanager.net/_tmp/rawdata_'.$rawdatafile.'.txt');
	if (isset($_GET['dbg'])) {
        echo 'Contenu en entree : ' . $contenu . '<br /><br />';
    }
    $lignes=explode("\n",$contenu);
	$collection= []; 
	foreach($lignes as $i=>$ligne) {
		$ligne=str_replace("\r", '', $ligne);
		if ($i==0) {
            continue;
        }
		$infos_ligne=explode('^',$ligne);
		if (count($infos_ligne)>=3) {
			$pays=$infos_ligne[0];
			$regex='#^([^ ]*)[ ]+(.*)$#';
			$magazine_numero=$infos_ligne[1];
			$requete='SELECT publicationcode, issuenumber FROM inducks_issue WHERE issuecode=\''.$pays.'/'.$magazine_numero.'\'';
			if (isset($_GET['dbg'])) {
                echo $requete;
            }
			$resultats=Inducks::requete_select($requete);
			if (count($resultats) == 0 && isset($_GET['dbg'])) {
				echo 'Pas de correspondance trouvee pour '.$pays.'/'.$magazine_numero.'<br />';
			}
			else {
				if (isset($_GET['dbg']) && !array_key_exists(0, $resultats)) {
					echo 'L\'index 0 n\'existe pas pour '.print_r($resultats, true).' : '.$requete;
					break;
				}

                $pays_magazine=explode('/',$resultats[0]['publicationcode']);
                $magazine=$pays_magazine[1];
                $numero=$resultats[0]['issuenumber'];
                if (!array_key_exists($pays,$collection)) {
                    $arr_temp= [$magazine=> [0=>$numero]];
                    $collection[$pays]=$arr_temp;
                }
                else {
                    if (!array_key_exists($magazine,$collection[$pays])) {
                        $collection[$pays][$magazine]= [$numero];
                    }
                    else {
                        if (!array_push($collection[$pays][$magazine],$numero))  {
                            echo '<b>'.$magazine.$numero.'</b>';
                        }
                    }
                }
            }
		}
	}
	if (isset($_GET['dbg'])) {
		echo '<pre>';print_r($collection);echo '</pre>';
	}
	echo serialize($collection);
}