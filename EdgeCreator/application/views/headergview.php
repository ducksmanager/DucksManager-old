<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/prototip/prototip.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/csstabs.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/edgecreator.css" />
	
	<script type="text/javascript" src="<?=base_url()?>js/prototype.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/scriptaculous/src/scriptaculous.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/json2.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/jscolor.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/edgecreatorlib.js" ></script>

	<script type="text/javascript">
		var pays='<?=$pays?>';
		var magazine='<?=$magazine?>';
		var etape_ouverture='<?=is_null($etape_ouverture) || empty($etape_ouverture) ? '_' : $etape_ouverture?>';
		var privilege='<?=$privilege?>';
		var username = '<?=$user?>';
		
		var plage=<?php
		if (is_null($numero_debut_filtre)) {
			?>new Array('null','null');<?php
		}
		else {
			?>new Array('<?=$numero_debut_filtre?>','<?=$numero_fin_filtre?>');<?php
		}?>
		
		var numero_fin_filtre='<?=$numero_fin_filtre?>';
		var base_url='<?=base_url()?>';

		var urls=new Array();
		urls['edgecreatorg']='<?=site_url('edgecreatorg')?>/';
		urls['numerosdispos']='<?=site_url('numerosdispos')?>/';
		urls['parametrageg']='<?=site_url('parametrageg')?>/';
		urls['modifierg']='<?=site_url('modifierg')?>/';
		urls['supprimerg']='<?=site_url('supprimerg')?>/';
		urls['listerg']='<?=site_url('listerg')?>/';
		urls['etendre']='<?=site_url('etendre')?>/';
		urls['viewer']='<?=site_url('viewer/index/'.$pays.'/'.$magazine)?>/';
	</script>
	<script type="text/javascript" src="<?=base_url()?>js/edgecreator.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/prototip/prototip.js" ></script>
	
	<title><?=$title?></title>
</head>
<body id="body" style="margin:0;padding:0">
	<?php 
	if (!empty($erreur)) {
		echo $erreur;
		?><br /><?php
	}
	if ($privilege==null) {?>
		Identifiez-vous<br /><br />
		<form method="post" action="<?=site_url('edgecreatorg/index/'.$pays.'/'.$magazine)?>">
			<table border="0">
				<tr><td>Nom d'utilisateur :</td><td><input type="text" name="user" /></td></tr>
				<tr><td>Mot de passe :</td><td><input type="password" name="pass" /></td></tr>
				<tr><td align="center" colspan="2"><input type="submit" value="Connexion"/></td></tr>
			</table>
		</form>
		</body></html><?php
		exit(0);
	}?>
	<div id="viewer">
		<div id="viewer_inner">
		
			<div class="entete_page">
				<a title="Deconnexion" href="<?=site_url('edgecreatorg')?>/logout">X</a>
				&nbsp;<?=$user?> - <?=$privilege?>
				<table>
				<tr>
				<td>
					<div id="zoom_slider" class="slider">
						<div class="handle"></div>
					</div>
				</td>
				<td style="font-size: 11px">
					Zoom<span style="display:none"> : <span id="zoom_value"></span></span>
				</td>
				</tr>
				</table>
			</div>
			<?php

			include_once(BASEPATH.('/../../Affichage.class.php'));
			$onglets=array(
				'Builder'=>array('builder','Builder'),
				'Previews'=>array('previews','Previews'));
			Affichage::onglets('parametres',$onglets,'previews','.');
			?>
			<div id="contenu_builder">
				<h1>Builder</h1>
				<div id="numero_preview">Cliquez sur le lien "Preview" d'un num&eacute;ro pour le pr&eacute;visaliser.</div>
				<?php switch($privilege) {
					case 'Admin' :
					?>
					<a style="display:none" id="save_png" href="javascript:void(0)">Enregistrer comme image PNG</a>
				<?php 
					break;
					case 'Edition' :
					?>
					<a style="display:none" id="save_png" href="javascript:void(0)">Proposer le mod&egrave;le de tranche</a>
				<?php
					break;
				} ?>
				<div class="previews"></div>
			</div>
			<div id="contenu_previews">
				<table style="margin-bottom: 15px">
					<tr>
						<td>
							<h2 style="-webkit-margin-before: 0px;-webkit-margin-after: 0px;">Previews</h2>
						</td>
						<td class="option_previews">
							<input type="checkbox" checked="checked" id="option_details" />D&eacute;tails<br />
							<input type="checkbox" checked="checked" id="option_pretes_seulement" />Pr&ecirc;tes seulement
							
						</td>
					</tr>
				</table>
				<?php switch($privilege) {
					case 'Admin' :
					?>
					<a style="display:none" id="save_pngs" href="javascript:void(0)">Enregistrer comme images PNG</a>
				<?php 
					break;
					case 'Edition' :
					?>
					<a style="display:none" id="save_pngs" href="javascript:void(0)">Proposer les mod&egrave;les de tranches</a>
				<?php
					break;
				} ?>
				<div id="numero_preview_debut" style="display:inline">
					Cliquez sur le lien "Preview" d'un num&eacute;ro 
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
	<div id="corps">
		<div class="entete_page">
			<table>
				<tr>
					<td>
						<h2>Mod&egrave;le de tranche</h2>
					</td>
					<td>
						<select style="font-size:11px" id="liste_pays"></select>
						&nbsp;&nbsp;
						<select style="font-size:11px" id="liste_magazines"></select><br />
						<div id="filtre_numeros">
							<img title="Cliquez pour changer l'intervalle des num&eacute;ros affich&eacute;s" src="<?=base_url()?>images/funnel.png" />
							Num&eacute;ros&nbsp;
							<select id="filtre_debut"></select>&nbsp;&agrave;&nbsp;
							<select id="filtre_fin"></select>
						</div>
						
					</td>
				</tr>
			</table>
		</div>
		<br />
	</div>
	<?php if ($privilege !='Affichage') { ?>
		<div id="infos">
			<div id="helpers"></div>
		</div>
		<div id="upload_fichier">
		</div>
	<?php } ?>
	<div id="chargement">
	</div>
	<div id="erreurs"></div>
	<?php if ($privilege !='Affichage') { ?>
		<a id="toggle_helpers" href="javascript:void(0)">Cacher l'assistant</a>
	<?php } ?>