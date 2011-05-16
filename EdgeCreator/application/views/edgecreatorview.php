<div style="float:left;height:100%;overflow-y:auto;margin:10px;width:40%">
	<h1>Mod&egrave;le de tranche</h1>
	<?=$pays?> &gt; <?=$magazine?>
	<br />
	<?=$texte?>
	<ul style="list-style: none outside none; padding-left: 15px;">
	<?php foreach($etapes as $etape=>$fonctions) {
		?><li>
			<hr />
			<?php if ($etape > 0) { ?>
				<div style="float:right">Etape <?=$etape?></div>
			<?php } ?>
		<?php 
		if ($etape<0) {
			?><b>Param&egrave;tres</b><br /><?php
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
	<span>
		<input type="radio" checked="checked" name="action" id="ajouter_etape"/>
		Ajouter&nbsp;<?=$liste_fonctions?>
		<ul style="margin-left:30px" id="intervalles_ajout">
			<li class="intervalle_ajout">Pour les num&eacute;ros entre 
				<span class="numero_ajout debut"><?=$numeros_visualisables1_select?>&nbsp;et&nbsp;<?=$numeros_visualisables2_select?></span>
				&nbsp;
				<a href="javascript:void(0)" onclick="ajouter_intervalle(this)">Cl</a>|<a href="javascript:void(0)" onclick="supprimer_intervalle(this)">X</a>&nbsp;
			</li>
			<li><a href="javascript:void(0)" onclick="ajouter_expression(this)">Ajouter une expression</a></li>
		</ul>
		<?php 
		if (count($etapes) > 1) { ?>
			<input type="radio" id="cloner_etape" name="action"/>Cloner l'&eacute;tape <?=$etapes_clonables?>
			<br />
		<?php } ?>
			<input type="radio" id="etendre_numero" name="action"/>Etendre les propri&eacute;t&eacute;s du num&eacute;ro <?=$numeros_extension1_select?> au num&eacute;ro <?=$numeros_extension2_select?>
			<br />
	</span>
	<?php $etape=(isset($etape))?($etape==-1?1:($etape+1)):-1;?>
	<a href="javascript:void(0)" onclick="executer_action(<?=$etape?>)">OK</a>
</div>
<div style="float: left;margin:10px;height:100%;overflow-y:auto">
	<div id="parametrage">
	</div>
</div>