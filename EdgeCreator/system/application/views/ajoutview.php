<?=$fonction->Nom_fonction?> : <br />
<?php foreach($numeros_debut_globaux as $i=>$numero_debut_global) {
    $numero_fin_global=$numeros_fin_globaux[$i];
    ?>Num&eacute;ros <?=$numero_debut_global?> &agrave; <?=$numero_fin_global?><br /><?php
}?>
<table rules="all" style="margin-left:5px;border:1px solid black"><?php
    foreach($options as $option_nom=>$option_valeur) {
        ?><tr><td><?=$option_nom?></td>
       <td><?=$fonction->getValeurModifiable($option_nom,array($intervalle=>$option_valeur),false)?></td></tr><?php
    }
?>
</table><br />
Modifiez les param&egrave;tres de la nouvelle fonction puis, lorsque la pr&eacute;visualisation vous satisfait,<br />cliquez sur "Appliquer" pour l'int&eacute;grer dans le mod&egrave;le de tranche.
