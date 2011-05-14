<html>
<head>
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>../csstabs.css" />
	<style type="text/css">
	html, body, #chargement, #chargement span {
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

	#viewer h1 {
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
		padding-right:15px;
		width:200px;
		height:100%;
		border-right:1px solid black;
		background-color: white;
		z-index: 500;
	}

	#viewer_inner {
		height:100%;
		overflow-x:auto;
		overflow-y:auto;
		width:100%;
	}
	
	#corps {
		position:absolute;
		left:225px;
		padding-left:10px;
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
			background:url('<?=base_url()?>system/application/views/images/checkedbox.png') no-repeat center center;
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
	<script type="text/javascript" src="<?=base_url()?>system/application/views/js/prototype.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>system/application/views/js/scriptaculous/src/scriptaculous.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>system/application/views/js/json2.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>system/application/views/js/jscolor.js" ></script>
	
	<script type="text/javascript">
		var pays='<?=$pays?>';
		var magazine='<?=$magazine?>';
		var base_url='<?=base_url()?>';

		var urls=new Array();
		urls['numerosdispos']='<?=site_url('numerosdispos')?>/';
		urls['parametrageg']='<?=site_url('parametrageg')?>/';
		urls['modifierg']='<?=site_url('modifierg')?>/';
		urls['supprimerg']='<?=site_url('supprimerg')?>/';
		urls['listerg']='<?=site_url('listerg')?>/';
		urls['etendre']='<?=site_url('etendre')?>/';
		urls['viewer']='<?=site_url('viewer/index/'.$pays.'/'.$magazine)?>/';
	</script>
	<script type="text/javascript" src="<?=base_url()?>system/application/views/js/edgecreator.js" ></script>
	
	<title><?=$title?></title>
</head>
<body id="body" style="margin:0;padding:0">
	<div id="viewer">
		<div id="viewer_inner">
			<div id="zoom_slider" class="slider">
				<div class="handle"></div>
			</div>&nbsp;Zoom : <span id="zoom_value"></span>
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
				<a style="display:none" id="save_png" href="javascript:void(0)">Enregistrer comme image PNG</a>
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
		<h1>Mod&egrave;le de tranche</h1>
		<div id="nom_magazine"></div>
		<br />
	</div>
	<div id="infos">
		<div id="helpers"></div>
	</div>
	<div id="chargement">
	</div>
	<div id="erreurs"></div>
	<a id="toggle_helpers" href="javascript:void(0)">Cacher l'assistant</a>