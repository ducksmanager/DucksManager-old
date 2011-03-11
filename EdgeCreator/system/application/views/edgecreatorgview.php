<div id="corps">
    <h1>Mod&egrave;le de tranche</h1>
    <?=$pays?> &gt; <?=$magazine?>
    <br />
    <table border="1" id="table_parametres">
        <tr class="ligne_entete" id="ligne_etapes">
            <td></td>
            <?php
            foreach($num_etapes as $num_etape) {
                $nom_fonction=$etapes[$num_etape][0]->Nom_fonction;
                $nb_attributs=0;
                foreach($options[$num_etape] as $option)
                    $nb_attributs++;
                ?><td colspan="<?=/*$nb_attributs+*/1?>" id="entete_etape_<?=$num_etape?>" class="lien_etape cellule_nom_fonction">
                    <span style="white-space: nowrap">Etape <?=$num_etape?></span>
                    <br />
                    <img width="18" title="<?=$nom_fonction?>" alt="<?=$nom_fonction?>" src="<?=base_url()?>../images/fonctions/<?=$nom_fonction?>.png" /></td><?php
            }
            ?>
        </tr>
        <tr class="ligne_entete" id="ligne_noms_options">
            <td></td>
            <?php
            foreach($etapes as $num_etape=>$etape) {
                ?><td class="centre"></td><?php
            }
            ?>
        </tr><?php
        foreach($numeros_dispos as $numero_dispo) {
            if ($numero_dispo=='Aucun')
                continue;
        ?>
        <tr class="ligne_dispo" id="ligne_<?=$numero_dispo?>">
            <td class="intitule_numero"><?=$numero_dispo?>&nbsp;<span class="preview">Preview</span></td><?php

            foreach($num_etapes as $num_etape) { 
                foreach($etapes[$num_etape] as $fonction) {
                    /*foreach($options[$num_etape] as $option_nom=>$option) {
                        ?><td></td><?php
                    }*/
                    $intervalle=$fonction->Numero_debut.'~'.$fonction->Numero_fin;
                    ?><td name="etape_<?=$num_etape?>"<?php
                    if (est_dans_intervalle($numero_dispo, $intervalle)) {
                        ?> class="num_checked"<?php
                    }
                    ?>>&nbsp;</td><?php
                }
            }
            ?>
        </tr>
            <?php
        }
    ?>
        </table>
    <?php exit(0);?>
        <?=$texte?>
        <ul style="list-style: none outside none; padding-left: 15px;">
        <?php foreach($etapes as $etape=>$fonctions) {
            ?><li>
                <hr />
                <?php if ($etape > 0) { ?>
                    <div style="float:right">Etape <?=$etape?></div>
                <?php } ?>
            <?php if ($etape<0) {
                ?><b>Param&egrave;tres</b><br /><?php
            }
            else {
                //echo 'Etape '.$etape;

            }
            foreach($fonctions as $fonction) {
                if ($etape>0) { 
                    ?><input type="checkbox" checked="checked" name="etape_active" value="<?=$etape?>"/>&nbsp;Montrer<br /><?php
                }?>
                <a href="javascript:void(0)" onclick="parametrage_etape(<?=$etape?>,'<?=$fonction->Nom_fonction?>','<?=$fonction->Numero_debut?>','<?=$fonction->Numero_fin?>')">Modifier</a>&nbsp;|&nbsp;
                <a href="javascript:void(0)" onclick="supprimer_etape(<?=$etape?>,'<?=$fonction->Nom_fonction?>','<?=$fonction->Numero_debut?>','<?=$fonction->Numero_fin?>')">Supprimer</a>
                <br />
                <div id="<?=$etape?>-<?=$fonction->Nom_fonction?>">
                    <?=$fonction?>
                    <table rules="all" style="margin-left:5px;border:1px solid black"><?php
                    $modele_options=(array)($fonction->options);
                    ksort($modele_options); // Tri par nom d'option
                    $fonction->options=(object)$modele_options;
                    foreach($fonction->options as $option_nom=>$option_valeur) {
                       ?><tr><td><?=$option_nom?></td>
                           <td><?=$fonction->getValeur($option_nom,$option_valeur)?></td></tr><?php
                    }
                    ?></table>
                 </div><?php
            }
            ?></li><?php
        }?>
        </ul>

        Ajouter&nbsp;<select id="nouvelle_fonction" >
        <?php foreach($liste_fonctions as $fonction) {
            ?><option value="<?=$fonction?>"><?=$fonction?></option><?php
        }
        $etape=(isset($etape))?($etape==-1?1:($etape+1)):-1;
        ?></select> 
        pour les num&eacute;ros entre 
        <span class="cache" id="numero_ajout_select"><?=$numeros_visualisables1_select?>&nbsp;et&nbsp;<?=$numeros_visualisables2_select?></span>
        <span class="cache" id="numero_ajout_input"><?=$numeros_visualisables1_input?>&nbsp;et&nbsp;<?=$numeros_visualisables2_input?></span>
        &nbsp;<a href="javascript:void(0)" onclick="ajouter_etape(<?=$etape?>)">OK</a>
</div>

<!--
<div style="float: left;margin:10px">
    <div id="parametrage">
    </div>
</div>!-->