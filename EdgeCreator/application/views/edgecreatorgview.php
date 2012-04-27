<div id="barre"></div>
<div id="entete_page">
	<?php
	if ($privilege!='Affichage') {?>
		<div id="zoom" class="cache">
			Zoom : <span id="zoom_value">1.5</span>
			<div id="zoom_slider"></div>
		</div>
		<?php 
		if ($mode_expert==true) {?>
			<div style="position:fixed;left:210px">
				<div style="float:left">
					<select style="font-size:11px" id="liste_pays"></select>
					&nbsp;&nbsp;
					<select style="font-size:11px" id="liste_magazines"></select><br />
					<div id="filtre_numeros">
						Num&eacute;ros&nbsp;
						<select id="filtre_debut"></select>&nbsp;&agrave;&nbsp;
						<select id="filtre_fin"></select>
						<button>OK</button>
					</div>
				</div>
			</div>
		<?php }
	} ?>
	<div id="action_bar" class="cache">Mod&eacute;lisation de la tranche du num&eacute;ro <span id="nom_complet_tranche_en_cours"></span><br />
		<img class="action tip" name="home" 
			 title="Revenir &agrave; l'&eacute;cran d'accueil de EdgeCreator" />
		<img class="action tip" name="photo" style="margin-left: 10px"
			 title="Placer la photo de la tranche &agrave; c&ocirc;t&eacute; du mod&egrave;le de tranche" />
	</div>
	<div id="status_user">
		<?php
		if ($privilege=='Affichage') {
			?>Non connect&eacute;(e)<?php
		}
		else {
			?>Connect&eacute;(e) en tant que <?=$user?><?php
		}
		?><br /><?php
		if ($user=='demo') {
			?><button class="small" id="connexion" onclick="location.reload()">Connexion</button><?php
		}
		else {
			?><button class="small" id="deconnexion" onclick="logout()">D&eacute;connexion</button><?php					
		}
		?>
	</div>
</div>
<?php
if ($mode_expert==true) {?>
	<div id="viewer">
		<div id="viewer_inner">
			<div id="tabs">
				<ul>
					<li><a href="#contenu_builder">Builder</a></li>
					<li><a href="#contenu_previews">Previews</a></li>
				</ul>
			
				<div id="contenu_builder">
					<div id="numero_preview">Cliquez sur le lien <img src="<?=base_url()?>images/view.png" /> d'un num&eacute;ro pour le pr&eacute;visaliser.</div>
					<?php switch($privilege) {
						case 'Admin' :
						?>
						<a style="display:none" class="save" href="javascript:void(0)">Enregistrer comme image PNG</a>
					<?php 
						break;
						case 'Edition' :
						?>
						<a style="display:none" class="save" href="javascript:void(0)">Proposer le mod&egrave;le de tranche</a>
					<?php
						break;
					} ?>
					<div class="previews"></div>
				</div>
				<div id="contenu_previews">
					<span class="options" style="display:none">
						<input type="checkbox" checked="checked" id="option_details" />D&eacute;tails<br />
						<input type="checkbox" checked="checked" id="option_pretes_seulement" />Pr&ecirc;tes seulement<br />
					</span>
	
					<?php switch($privilege) {
						case 'Admin' :
						?>
						<a style="display:none;" class="save" href="javascript:void(0)">Enregistrer comme images PNG</a>
					<?php 
						break;
						case 'Edition' :
						?>
						<a style="display:none" class="save" href="javascript:void(0)">Proposer les mod&egrave;les de tranches</a>
					<?php
						break;
					} ?>
					<div id="numero_preview_debut" style="display:inline">
						Cliquez sur le lien <img src="<?=base_url()?>images/view.png" /> d'un num&eacute;ro 
						pour le s&eacute;lectionner comme premier num&eacute;ro &agrave; pr&eacute;visualiser.
					</div>
					- 
					<div id="numero_preview_fin" style="display:inline"></div>
					<div id="montrer_details">
					</div>
					<div class="previews"></div>
				</div>
			</div>
		</div>
	</div>
	<div id="corps">
		<br />
	</div>
	<?php if ($privilege !='Affichage') { ?>
		<div id="infos" class="cache">
			<div id="helpers"></div>
		</div>
		<div id="upload_fichier">
		</div>
	<?php } ?>
	<div id="chargement">
	</div>
	<div id="erreurs" ></div>
	<?php if ($privilege !='Affichage') { ?>
		<a id="toggle_helpers" href="javascript:void(0)" class="cache">Cacher l'assistant</a>
	<?php }
} ?>