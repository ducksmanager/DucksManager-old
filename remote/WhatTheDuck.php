<?php
header("Access-Control-Allow-Origin: *");

error_reporting(E_ALL);

include_once 'dm_client.php';
DmClient::init();
include_once 'auth.php';

$SUPPORTED_LANGUAGES = ['fr', 'en'];

$version= $_GET['version'] ?? '1.0';
$language = $_GET['language'] ?? 'en';
if (!in_array($language, $SUPPORTED_LANGUAGES, true)) {
    $language = 'en';
}

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

        $resultats = DmClient::get_query_results_from_dm_server('
            SELECT ID
            FROM users
            WHERE username=? AND password=?',
            'db_dm',
            [$pseudo, $mdp]
        );

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
                $resultats = DmClient::get_query_results_from_dm_server('
                    INSERT INTO users(username,password,Email,DateInscription)
                    VALUES(?,?,?,?)',
                    'db_dm',
                    [$user,$pass,$email, date('Y-m-d')]);
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
                $id_utilisateur = $resultats[0]->ID;
                if (isset($_GET['ajouter_numero'])) {
                    [$pays, $magazine] = explode('/', $_GET['pays_magazine']);
                    $numero = $_GET['numero'];
                    $etat = $_GET['etat'];

                    if (isset($_GET['id_acquisition'])) {
                        $resultats_date_achat = DmClient::get_query_results_from_dm_server('
                            SELECT 1
                            FROM achats
                            WHERE ID_Acquisition=? AND ID_User=?',
                            'db_dm',
                            [$_GET['id_acquisition'], $id_utilisateur]
                        );
                        if (count($resultats_date_achat) !== 1) {
                            echo 'Invalid purchase ID';
                        }
                        else {
                            $id_acquisition = $_GET['id_acquisition'];
                        }
                    }

                    if (!isset($id_acquisition)) {
                        $id_acquisition = -2;
                    }

                    $resultats = DmClient::get_query_results_from_dm_server('
                        INSERT INTO numeros(Pays,Magazine,Numero, Etat, ID_Acquisition, ID_Utilisateur, AV)
                        VALUES(?, ?, ?, ?, ?, ?, ?)',
                        'db_dm',
                        [$pays,$magazine,$numero,$etat,$id_acquisition,$id_utilisateur,0]
                    );

                    if ($resultats === []) {
                        echo 'OK';
                    }
                } else if (isset($_GET['ajouter_achat'])) {
                    $date_achat = str_replace("'", "", $_GET['date_achat']);
                    $description_achat = str_replace("'", "", $_GET['description_achat']);

                    $resultats_achats = DmClient::get_query_results_from_dm_server('
                        INSERT INTO achats(ID_User,Date,Description)
                        VALUES (?, ?, ?)',
                        'db_dm',
                        [$id_utilisateur, $date_achat, $description_achat]
                    );

                    if (count($resultats_achats) === 0) {
                        echo 'OK';
                    }
                } else if (isset($_GET['get_achats'])) {
                    $resultats_achats = DmClient::get_query_results_from_dm_server('
                        SELECT ID_Acquisition, Date,Description
                        FROM achats
                        WHERE ID_User=?
                        ORDER BY Date DESC
                        LIMIT 15',
                        'db_dm',
                        [$id_utilisateur]
                    );
                    $retour->achats = $resultats_achats;
                    echo json_encode($retour);
                } else {
                    $resultats_numeros = DmClient::get_query_results_from_dm_server('
                        SELECT Pays, Magazine, Numero, Etat, achats.ID_Acquisition AS ID_Acquisition, achats.Date AS Date_Acquisition, achats.Description AS Description_Acquisition
                        FROM numeros
                        LEFT JOIN achats ON numeros.ID_Acquisition=achats.ID_Acquisition
                        WHERE ID_Utilisateur=?
                        ORDER BY Pays, Magazine, Numero',
                        'db_dm',
                        [$id_utilisateur]);

                    $numeros = [];
                    $pays = [];
                    $magazines = [];
                    foreach ($resultats_numeros as $resultat_numero) {
                        $pays_magazine = $resultat_numero->Pays . '/' . $resultat_numero->Magazine;
                        if (!array_key_exists($pays_magazine, $numeros)) {
                            $numeros[$pays_magazine] = [];
                            $magazines[$pays_magazine] = $pays_magazine;
                        }
                        $details_numero = new stdClass();
                        $details_numero->Numero = $resultat_numero->Numero;
                        $details_numero->Etat = $resultat_numero->Etat;

                        if (empty($resultat_numero->ID_Acquisition)) {
                            $acquisition = null;
                        } else {
                            $acquisition = new stdClass();
                            $acquisition->ID_Acquisition = (int)$resultat_numero->ID_Acquisition;
                            $acquisition->Date_Acquisition = $resultat_numero->Date_Acquisition;
                            $acquisition->Description_Acquisition = $resultat_numero->Description_Acquisition;
                        }
                        $details_numero->Acquisition = $acquisition;

                        $numeros[$pays_magazine][] = $details_numero;
                    }

                    $retour->static->pays = DmClient::get_service_results_for_wtd('GET', "/coa/list/countries/$language");
                    if (count($magazines) > 0) {
                        $retour->static->magazines=DmClient::get_service_results_for_wtd('GET', "/coa/list/publications", [implode(',', array_keys($magazines))]);
                    }
                    else {
                        $retour->static->magazines = [];
                    }
                    $retour->numeros = $numeros;

                    echo json_encode($retour);

                }
            } else {
                echo '0';
            }
        }
	}
}
