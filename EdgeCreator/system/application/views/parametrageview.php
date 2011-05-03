<?=$fonction->Nom_fonction?> : <br />
<?php foreach($numeros_debut_globaux as $i=>$numero_debut_global) {
    $numero_fin_global=$numeros_fin_globaux[$i];
    ?><div id="parametrage_intervalle"><table><tr><td>Num&eacute;ros <?=$numero_debut_global?> &agrave; <?=$numero_fin_global?></td>
        <td><a class="cloner" href="javascript:void(0)" onclick="cloner(this)">Cl</a></td><td>|</td>
        <td><a class="supprimer" href="javascript:void(0)" onclick="supprimer(this)">X</a></td>
    </tr></table></div><?php
}?><table rules="all" style="margin-left:5px;border:1px solid black"><?php
    foreach($options as $option_nom=>$option_valeur) {
        ?><tr><td><?=$option_nom?></td>
       <td><?=$fonction->getValeurModifiable($option_nom,$option_valeur)?></td></tr><?php
    }
?>
</table>
