<?header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
include_once('../Database.class.php');
include_once('../authentification.php');

if (isset($_POST['ID'])) {
    $requete_maj_bouquinerie =
        'UPDATE bouquineries
        SET CoordX='.$_POST['CoordX'].',
            CoordY='.$_POST['CoordY'].',
            Actif=1
        WHERE ID='.$_POST['ID'];

    DM_Core::$d->requete_distante($requete_maj_bouquinerie);
}

$requete = 'SELECT * from bouquineries WHERE Actif=0';

$resultats = DM_Core::$d->requete_select_distante($requete);

if (count($resultats) > 0) {
    $champs = array_keys($resultats[0]);
    $champs_utilises = [];
    ?><table border="1">
        <tr><form method="post">
            <?php foreach($champs as $champ) {
                if (!is_int($champ)) {
                    $champs_utilises[] = $champ;
                    ?><th><?=$champ?></th><?php
                }
            }?>
            <th>Validation</th>
        </tr>
        <?php foreach($resultats as $resultat) {
            ?><tr>
                <?php foreach($champs_utilises as $champ) {
                    ?><td>
                        <?php
                            if (strpos($champ,'Coord') !== false) {
                                ?><input type="text" size="10" name="<?=$champ?>" value="<?=$resultat[$champ]?>" /><?php
                            }
                            elseif ($champ === 'ID') {
                                ?><input type="text" readonly size="10" name="<?=$champ?>" value="<?=$resultat[$champ]?>" /><?php
                            }
                            else {
                                echo $resultat[$champ];
                            }
                        ?>
                    </td><?php
                }?>
                <td><input type="submit" /></td>
            </form></tr><?php
        }?>

    </table><?php
}
else {
    echo 'Aucune bouquinerie en attente de validation';
}

?>