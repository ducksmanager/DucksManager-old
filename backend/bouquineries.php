<?header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
include_once '../Database.class.php';
include_once '../authentification.php';

if (isset($_POST['ID'])) {
    date_default_timezone_set('Europe/Paris');
    $date = date("Y-m-d H:i:s");
    $requete_maj_bouquinerie =
        "UPDATE bouquineries
        SET CoordX='{$_POST['CoordX']}',
            CoordY='{$_POST['CoordY']}',
            Actif=1,
            DateAjout='$date'
        WHERE ID={$_POST['ID']}";

    DM_Core::$d->requete($requete_maj_bouquinerie);
}

$requete = 'SELECT ID, Nom, AdresseComplete, Pays, Commentaire, ID_Utilisateur, DateAjout, CONCAT(CoordX, ",", CoordY) As Coord from bouquineries WHERE Actif=0';

$resultats = DM_Core::$d->requete_select($requete);

if (count($resultats) > 0) {
    $champs = array_keys($resultats[0]);
    $champs_utilises = [];
    ?><table border="1">
        <tr>
            <?php foreach($champs as $champ) {
                if (!is_int($champ)) {
                    $champs_utilises[] = $champ;
                    ?><th><?=$champ?></th><?php
                }
            }?>
        </tr>
        <?php foreach($resultats as $resultat) {
            ?><tr>
                <?php foreach($champs_utilises as $champ) {
                    ?><td>
                        <?php
                            if ($champ === 'Coord') {
                                ?><form method="post">
                                    <input type="hidden" name="ID" value="<?=$resultat['ID']?>" />
                                    <?php
                                    array_walk(explode(',', $resultat[$champ]), function($coord, $i) {
                                        $champ = $i === 0 ? 'CoordX': 'CoordY';
                                        ?><input class="text_input short" type="text" size="10" name="<?=$champ?>" value="<?=$coord?>" /><?php
                                    });
                                    ?>
                                    <br />
                                    <input type="submit" />
                                </form><?php
                            }
                            else {
                                echo $resultat[$champ];
                            }
                        ?>
                    </td><?php
                }?>
            </tr><?php
        }?>

    </table><?php
}
else {
    echo 'Aucune bouquinerie en attente de validation';
}

?>