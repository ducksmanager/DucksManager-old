<?php
header("Access-Control-Allow-Origin: *");

error_reporting(E_ALL);

include_once 'dm_client.php';
DmClient::init();

$serverIniAuth = true;
include_once 'auth.php';

$version= $_GET['version'] ?? '1.0';
$language = isset($_GET['language']) ? ($_GET['language'] === 'fr' ? 'fr' : 'en') : 'en';

$retour=new stdClass();
$retour->static=new stdClass();

if (isset($_GET['pseudo_user'], $_GET['mdp_user'])) {

    DmClient::setUserdata(['user' => $_GET['pseudo_user'], 'pass' => $_GET['mdp_user']]);
	if (isset($_GET['coa'])) {

		if (isset($_GET['liste_pays'])) {
            $retour->static->pays = DmClient::get_service_results_for_wtd('GET', "/coa/list/countries/$language");
		}
		else if (isset($_GET['liste_magazines'], $_GET['pays'])) {
			$retour->static->magazines=DmClient::get_service_results_for_wtd('GET', "/coa/list/publications", [$_GET['pays']]);
		}
		else if (isset($_GET['liste_numeros'], $_GET['magazine'])) {
            $retour->static->numeros=DmClient::get_service_results_for_wtd('GET', "/coa/list/issues", [$_GET['magazine']]);
		}
		echo json_encode($retour);
	}
	else {
		$pseudo=$_GET['pseudo_user'];
		$mdp=$_GET['mdp_user'];

		$requete="
		    SELECT ID
		    FROM users
		    WHERE username='$pseudo' AND password='$mdp'";
        $resultats = DmClient::get_query_results_from_dm_site($requete);

		if (isset($_GET['debug'])) {
            echo $requete . '<br />';
        }

		$action= $_GET['action'] ?? '';
        if ($action === 'signup') {
            $user = $_GET['pseudo_user'];
            $pass = $_GET['mdp_user'];
            $pass2 = $_GET['mdp_user2'];
            $email = $_GET['email'];

            @include_once 'Affichage.class.php';
            @include_once '../Affichage.class.php';

            $erreur = Affichage::valider_formulaire_inscription($user, $pass, $pass2);

            if (is_null($erreur)) {
                $requete = "
					  INSERT INTO users(username,password,Email,DateInscription)
					  VALUES('$user','$pass','$email','" . date('Y-m-d') . "')";
                $resultats = DmClient::get_query_results_from_dm_site($requete);
                if ($resultats === []) {
                    echo 'OK';
                }
            }
            else {
                echo utf8_encode(html_entity_decode($erreur));
            }
        }
        else {
            if (count($resultats) > 0) {
                $id_utilisateur = $resultats[0]['ID'];
                if (isset($_GET['ajouter_numero'])) {
                    list($pays, $magazine) = explode('/', $_GET['pays_magazine']);
                    $numero = $_GET['numero'];
                    $etat = $_GET['etat'];

                    if (isset($_GET['id_acquisition'])) {
                        $id_acquisition = $_GET['id_acquisition'];
                        $requete_date_achat = "
                            SELECT 1
                            FROM achats
                            WHERE ID_Acquisition=$id_acquisition AND ID_User=$id_utilisateur";
                        $resultats_date_achat = DmClient::get_query_results_from_dm_site($requete_date_achat);
                        if (count($resultats_date_achat) !== 1) {
                            echo 'Invalid purchase ID';
                        }
                    } else {
                        $id_acquisition = -2;
                    }

                    $requete = "
                      INSERT INTO numeros(Pays,Magazine,Numero, Etat, ID_Acquisition, ID_Utilisateur)
                      VALUES('$pays', '$magazine', '$numero', '$etat', $id_acquisition, $id_utilisateur)";
                    $resultats = DmClient::get_query_results_from_dm_site($requete);

                    if (isset($_GET['debug'])) {
                        echo $requete . '<br />';
                    }
                    if ($resultats === []) {
                        echo 'OK';
                    } else {
                        print_r($resultats);
                    }
                } else if (isset($_GET['ajouter_achat'])) {
                    $date_achat = str_replace("'", "", $_GET['date_achat']);
                    $description_achat = str_replace("'", "", $_GET['description_achat']);
                    $requete_ajout_achat = "
						  INSERT INTO achats(ID_User,Date,Description)
						  VALUES ($id_utilisateur, '$date_achat', '$description_achat')";

                    $resultats_achats = DmClient::get_query_results_from_dm_site($requete_ajout_achat);

                    if (isset($_GET['debug'])) {
                        echo $requete_ajout_achat . '<br />';
                    }
                    if (count($resultats_achats) === 0) {
                        echo 'OK';
                    }
                } else if (isset($_GET['get_achats'])) {
                    $requete_achats = "SELECT ID_Acquisition, Date,Description FROM achats WHERE ID_User=$id_utilisateur ORDER BY Date DESC LIMIT 15";
                    $resultats_achats = DmClient::get_query_results_from_dm_site($requete_achats);
                    $retour->achats = $resultats_achats;
                    echo json_encode($retour);
                } else {
                    $numeros = [];
                    $pays = [];
                    $magazines = [];
                    $requete_numeros = "
						  SELECT Pays, Magazine, Numero, Etat, achats.ID_Acquisition AS ID_Acquisition, achats.Date AS Date_Acquisition, achats.Description AS Description_Acquisition
						  FROM numeros
						  LEFT JOIN achats ON numeros.ID_Acquisition=achats.ID_Acquisition
						  WHERE ID_Utilisateur=$id_utilisateur
						  ORDER BY Pays, Magazine, Numero";
                    $resultats_numeros = DmClient::get_query_results_from_dm_site($requete_numeros);
                    foreach ($resultats_numeros as $resultat_numero) {
                        $pays_magazine = $resultat_numero['Pays'] . '/' . $resultat_numero['Magazine'];
                        if (!array_key_exists($pays_magazine, $numeros)) {
                            $numeros[$pays_magazine] = [];
                            $magazines[$pays_magazine] = $pays_magazine;
                        }
                        $details_numero = new stdClass();
                        $details_numero->Numero = $resultat_numero['Numero'];
                        $details_numero->Etat = $resultat_numero['Etat'];

                        if (is_null($resultat_numero['ID_Acquisition'])) {
                            $acquisition = null;
                        } else {
                            $acquisition = new stdClass();
                            $acquisition->ID_Acquisition = $resultat_numero['ID_Acquisition'];
                            $acquisition->Date_Acquisition = $resultat_numero['Date_Acquisition'];
                            $acquisition->Description_Acquisition = $resultat_numero['Description_Acquisition'];
                        }
                        $details_numero->Acquisition = $acquisition;

                        $numeros[$pays_magazine][] = $details_numero;
                    }

                    $retour->static->pays = DmClient::get_service_results_for_wtd('GET', "/coa/list/countries/$language");
                    $retour->static->magazines=DmClient::get_service_results_for_wtd('GET', "/coa/list/publications", [implode(',', array_keys($magazines))]);
                    $retour->numeros = $numeros;

                    echo json_encode($retour);

                }
            } else {
                echo '0';
            }
        }
	}
}
