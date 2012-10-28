<? header("Last-Modified: " . gmdate( "D, j M Y H:i:s" ) . " GMT"); // Date in the past 
header("Expires: " . gmdate( "D, j M Y H:i:s", time() ) . " GMT"); // always modified 
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1 
header("Cache-Control: post-check=0, pre-check=0", FALSE); 
header("Pragma: no-cache"); ?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/sunny/jquery-ui-1.9.0.custom.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/csstabs.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/edgecreator.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/edgecreator_wizard.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/edgecreator_gallery.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/farbtastic.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/tipTip.css" />
	<link rel="shortcut icon" href="<?=base_url()?>images/favicon.ico" />
	
	<script type="text/javascript" src="<?=base_url()?>js/jquery-1.8.2.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/jquery.serializeObject.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/jquery.ba-resize.min.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/jquery.tipTip.minified.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/jquery-ui-1.9.0.custom.min.js" ></script>
	
	<!-- <script type="text/javascript" src="<?=base_url()?>js/jquery.scrollbarTable.js" ></script>!-->
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
		   				   'modifierg','supprimerg','listerg','etendre','insert_wizard','upload_wizard','supprimer_wizard','viewer','viewer_wizard','viewer_myfonts');
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
	<div id="zone_ajout_etape">
		<img id="ajout_etape" class="tip2 cache"
			 src="../../images/icones/add.png" title="Ajouter une &eacute;tape ici"/>
	</div>
	<?php
	if (!empty($erreur)) {
		echo $erreur;
		?><br /><?php
	}?>