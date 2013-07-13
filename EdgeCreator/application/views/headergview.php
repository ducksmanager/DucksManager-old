<? header("Last-Modified: " . gmdate( "D, j M Y H:i:s" ) . " GMT"); // Date in the past 
header("Expires: " . gmdate( "D, j M Y H:i:s", time() ) . " GMT"); // always modified 
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1 
header("Cache-Control: post-check=0, pre-check=0", FALSE); 
header("Pragma: no-cache"); ?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/sunny/jquery-ui-1.10.2.custom.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/edgecreator.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/edgecreator_wizard.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/edgecreator_gallery.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/farbtastic.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/jquery.jrac.css" />
	<link rel="shortcut icon" href="<?=base_url()?>images/favicon.ico" />
	
	<script type="text/javascript" src="<?=base_url()?>js/jquery-1.9.1.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/jquery.serializeObject.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/jquery.ba-resize.min.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/jquery.jrac.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/jquery-ui-1.10.2.custom.js" ></script>
	
	<script type="text/javascript" src="<?=base_url()?>js/jquery.dataSelector.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/json2.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/farbtastic.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/edgecreatorlib.js" ></script>

	<script type="text/javascript">
		var pays='<?=$pays?>';
		var magazine='<?=$magazine?>';
		var etape_ouverture='<?=is_null($etape_ouverture) || empty($etape_ouverture) ? '_' : $etape_ouverture?>';
		var privilege='<?=$privilege?>';
		var username = '<?=$user?>';
		var mode_expert = <?=$mode_expert===true?'true':'false'?>;
		
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
		<?php
		$controleurs=array('update_wizard','edgecreatorg','tranchesencours','numerosdispos','parametrageg','parametrageg_wizard',
		   				   'modifierg','supprimerg','listerg','etendre','insert_wizard','cloner','upload_wizard','supprimer_wizard','viewer','viewer_wizard','viewer_myfonts',
						   'dessiner','photo_principale','update_photo','rogner_image','desactiver_modele','valider_modele','check_logged_in');
		foreach($controleurs as $controleur) {
			?>urls['<?=$controleur?>']='<?=site_url($controleur)?>/';<?php
		}?>
	</script>
	<script type="text/javascript" src="<?=base_url()?>js/edgecreator.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/edgecreator_wizard.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/edgecreator_wizard_imagerotate.js" ></script>
	
	<title><?=$title?></title>
</head>
<body id="body" style="margin:0;padding:0">
	<img class="ajout_etape tip2 template hidden"
		 src="../../images/icones/add.png" title="Ajouter une &eacute;tape ici"/>
	<?php
	if (!empty($erreur)) {
		echo $erreur;
		?><br /><?php
	}?>