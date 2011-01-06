<h1>Mod&egrave;le de tranche</h1>
<?=$pays?> &gt; <?=$magazine?>
<br />
<table border="1">
    <tr>
        <td></td>
        <?php
        foreach($num_ordres as $num_ordre) {
            if ($num_ordre<0)
                continue;
            ?><td>Etape <?=$num_ordre?></td><?php
        }
        ?>
    </tr>
    <?php foreach ($ordres[-1] as $dimensions) {
        $debut=true;
        foreach($numeros_dispos as $numero_dispo) {
            if ($debut) {
                if ($numero_dispo!=$dimensions->Numero_debut)
                    continue;
                else
                    $debut=false;
            }
            ?><tr>
                <td>
                    <?=$numero_dispo?>
                </td>
                <?php
                    foreach($num_ordres as $num_ordre) { 
                        foreach($ordres[$num_ordre] as $fonction) {
                            if ($num_ordre<0)
                                continue;
                            ?><td><?php
                                $numeros_debut=explode(';',$fonction->Numero_debut);
                                $numeros_fin=explode(';',$fonction->Numero_fin);
                                foreach($numeros_debut as $i=>$numero_debut) {
                                    $numero_fin=$numeros_fin[$i];
                                    if (est_dans_intervalle($numero_dispo,$numero_debut.'~'.$numero_fin)) {
                                        echo $fonction->Nom_fonction;
                                    }
                                }
                            ?></td><?php
                        }
                    }
                ?>
            </tr>
            <?php
            if ($numero_dispo==$dimensions->Numero_fin)
                break;
        }
    }
?>
</table>
<?php exit(0);?>
    <?=$texte?>
    <ul style="list-style: none outside none; padding-left: 15px;">
    <?php foreach($ordres as $ordre=>$fonctions) {
        ?><li>
            <hr />
            <?php if ($ordre > 0) { ?>
                <div style="float:right">Etape <?=$ordre?></div>
            <?php } ?>
        <?php if ($ordre<0) {
            ?><b>Param&egrave;tres</b><br /><?php
        }
        else {
            //echo 'Etape '.$ordre;
            
        }
        foreach($fonctions as $fonction) {
            if ($ordre>0) { 
                ?><input type="checkbox" checked="checked" name="etape_active" value="<?=$ordre?>"/>&nbsp;Montrer<br /><?php
            }?>
            <a href="javascript:void(0)" onclick="parametrage_etape(<?=$ordre?>,'<?=$fonction->Nom_fonction?>','<?=$fonction->Numero_debut?>','<?=$fonction->Numero_fin?>')">Modifier</a>&nbsp;|&nbsp;
            <a href="javascript:void(0)" onclick="supprimer_etape(<?=$ordre?>,'<?=$fonction->Nom_fonction?>','<?=$fonction->Numero_debut?>','<?=$fonction->Numero_fin?>')">Supprimer</a>
            <br />
            <div id="<?=$ordre?>-<?=$fonction->Nom_fonction?>">
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
    $ordre=(isset($ordre))?($ordre==-1?1:($ordre+1)):-1;
    ?></select> 
    pour les num&eacute;ros entre 
    <span class="cache" id="numero_ajout_select"><?=$numeros_visualisables1_select?>&nbsp;et&nbsp;<?=$numeros_visualisables2_select?></span>
    <span class="cache" id="numero_ajout_input"><?=$numeros_visualisables1_input?>&nbsp;et&nbsp;<?=$numeros_visualisables2_input?></span>
    &nbsp;<a href="javascript:void(0)" onclick="ajouter_etape(<?=$ordre?>)">OK</a>
</div>
<div style="float: left;margin:10px">
    <div id="parametrage">
    </div>
</div>