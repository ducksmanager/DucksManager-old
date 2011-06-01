<html>
<head>
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>../csstabs.css" />
	<style type="text/css">
	html, body, #chargement, #chargement span, #filtre_numeros {
		height:100%;
		padding:0;
		margin:0;
		font-size:11px;
		line-height:25px;
	}

	html, body, span, td, th {
		font-family: Charcoal,arial,sans-serif;
	}

	span, td, th {
		font-size:16px;
	}
	#corps h2 {
		margin-bottom: 0;
	}
	
	.entete_page {
		height:60px;
		width:100%;
		border-bottom:1px solid gray;
	}
	
	#filtre_numeros {
		background:transparent url('<?=base_url()?>images/funnel.png') no-repeat center left;
		padding-left: 20px;
	}

	#viewer h2 {
		margin-top:-10px;
	}

	a {
		color:blue;
		cursor:pointer;
		text-decoration:none;
	}

	.cache {
		display:none;
	}
	
	#infos {
		position:fixed;
		right:0;
		top:0;
		background-color:white;
		z-index:500;
		width:375px;
		overflow-y: auto;
		height: 100%;
	}
	
	#toggle_helpers{
		position:fixed;
		right:0;
		top:0;
		z-index:1000;
		background-color:white;
	}

	#chargement {
		position:fixed;
		top:0px;
		right:0px;
		margin-top:20px;
		z-index:1000;
		background-color:white;
		height: 100px;
	}
	
	#erreurs {
		position:fixed;
		right:0;
		bottom:0;
		color:red;
	}
	
	#viewer {
		position:fixed;
		left:0px;
		width:200px;
		height:100%;
		border-right:1px solid black;
		background-color: white;
		z-index: 500;
		padding-right: 5px;
	}

	#viewer_inner {
		height:100%;
		overflow-x:auto;
		overflow-y:auto;
		width:100%;
	}
	
	.tabnav {
		margin-top: 10px;
	}
	
	#corps {
		position:absolute;
		left:225px;
		padding-left:10px;
		width: 100%;
		padding-right: 450px;
	}

	table.bordered {
		border-color: black;
		border-collapse: collapse;
		border-spacing: 0px;
		overflow-x:auto;
	}
	
	.intitule_numero {
		white-space:nowrap;
	}

	td .preview {
		cursor:pointer;
		vertical-align:middle;
	}
	 
	.valeur_reelle {
		display:none;
	}

	#modifier_valeur {
		margin-top:5px;
		border-top : 1px solid black;
	}

	.cloner {
		cursor:pointer;
	}
	
	.non_concerne {
		background-color:gray;
	}
	
	.erreur_valeurs_multiples {
		border:1px solid red;
	}
		
	.num_checked {
			background:url('<?=base_url()?>images/checkedbox.png') no-repeat center center;
	}
	.centre {
		text-align:center;
	}
	.lien_etape,.num_etape_preview {
		text-align:center;
	}
	
	.lien_etape>span, .lien_etape>.logo_option, .lien_option>span, .num_etape_preview, .ajouter_etape, .supprimer_etape {
		cursor:pointer;
	}
	
	.numero_etape {
		white-space:nowrap;
	}
	
	.option_etape a{
		color:black;
		cursor:default;
	}
	
	.option_etape a:hover{
		background-color:white;
	}
	
	.option_etape a span {
		display:none; 
		padding:2px 3px; 
		margin-left:10px; 
		width:300px;
	}
	
	.option_etape a:hover span{
		display:inline; 
		position:absolute; 
		border:1px solid #cccccc; 
		background:#ffffff; 
		color:#C88964;
	}
	
	.previews td {
		border-spacing: 0;
		padding: 0;
		border-collapse: collapse;
	}

	.ligne_previews .image_etape,.fond_noir_active,.fond_noir_inactive {
		text-align:center;
	}

	.ligne_previews td {
		vertical-align: top;
	}
	
	.cellule_nom_fonction {
		text-align:center;
	}
	
	.pointer {
		cursor:pointer;
	}

	tr.tranche_prete {
		background-color: #98EBB2;
	}

	#table_numeros tr:not(.ligne_entete)>td:not(.intitule_numero):not(.cloner) {
		white-space: nowrap;
		min-width:25px;
		max-width: 200px;
		overflow-x: auto;
		cursor:default;
	}

	td.selected {
		border-style:dotted;
	}

	td.reload {
		text-align:center;
	}

	.ajouter_etape {
		margin-right:-10px;
		float:right;
	}

	.supprimer_etape {
		margin-top: -9px;
		position: absolute;
		margin-left: -3px;
	}

	.fond_noir_active {
		border-bottom:1px solid blue;
	}

	.regle {
		z-index: 500;
		float: right;
		cursor:e-resize;
	}

	.zone_regle {
		overflow:hidden;
		position:absolute;
	}
	
	.repere {
		z-index:600;
	}
	
	#montrer_details {
		display:none;
	}
	
	.view_preview {
		float:right;
	}
	
	.option_previews {
		font-size:11px;
		white-space: nowrap;
	}

	div.slider { width:150px; margin:10px 0; background-color:#ccc; height:10px; position: relative; white-space: nowrap; }

	div.slider div.handle { width:10px; height:15px; background-color:#f00; cursor:move; position: absolute; }
	
	</style>
	<script type="text/javascript" src="<?=base_url()?>js/prototype.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/scriptaculous/src/scriptaculous.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/json2.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/jscolor.js" ></script>

	<script type="text/javascript">
		var pays='<?=$pays?>';
		var magazine='<?=$magazine?>';
		var etape_ouverture='<?=is_null($etape_ouverture) || empty($etape_ouverture) ? '_' : $etape_ouverture?>';
		var privilege='<?=$privilege?>';
		
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
				<?php if ($privilege == 'Admin' || $privilege == 'Enregistrement') {?>
					<a style="display:none" id="save_png" href="javascript:void(0)">Enregistrer comme image PNG</a>
				<?php } ?>
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
	<?php } ?>
	<div id="chargement">
	</div>
	<div id="erreurs"></div>
	<?php if ($privilege !='Affichage') { ?>
		<a id="toggle_helpers" href="javascript:void(0)">Cacher l'assistant</a>
	<?php } ?>