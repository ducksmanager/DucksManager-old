<? header("Last-Modified: " . gmdate( "D, j M Y H:i:s" ) . " GMT"); // Date in the past 
header("Expires: " . gmdate( "D, j M Y H:i:s", time() ) . " GMT"); // always modified 
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1 
header("Cache-Control: post-check=0, pre-check=0", FALSE); 
header("Pragma: no-cache"); ?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/sunny/jquery-ui-1.8.17.custom.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/csstabs.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/edgecreator.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/edgecreator_wizard.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/farbtastic.css" />
	
	<script type="text/javascript" src="<?=base_url()?>js/jquery-1.7.1.min.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/jquery-ui-1.8.18.custom.min.js" ></script>
	
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
		urls['edgecreatorg']='<?=site_url('edgecreatorg')?>/';
		urls['tranchesencours']='<?=site_url('tranchesencours')?>/';
		urls['numerosdispos']='<?=site_url('numerosdispos')?>/';
		urls['parametrageg']='<?=site_url('parametrageg')?>/';
		urls['parametrageg_wizard']='<?=site_url('parametrageg_wizard')?>/';
		urls['modifierg']='<?=site_url('modifierg')?>/';
		urls['supprimerg']='<?=site_url('supprimerg')?>/';
		urls['listerg']='<?=site_url('listerg')?>/';
		urls['etendre']='<?=site_url('etendre')?>/';
		urls['viewer']='<?=site_url('viewer')?>/';
		urls['viewer_wizard']='<?=site_url('viewer_wizard')?>/';
	</script>
	<script type="text/javascript" src="<?=base_url()?>js/edgecreator.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/edgecreator_wizard.js" ></script>
	
	<title><?=$title?></title>
</head>
<body id="body" style="margin:0;padding:0">
	<?php
	if ($just_connected) { ?>
		
	<?php 
	}
	if (!empty($erreur)) {
		echo $erreur;
		?><br /><?php
	}
	/*if ($privilege==null) {?>
		Identifiez-vous<br /><br />
		<form method="post" action="<?=site_url('edgecreatorg/index/'.$pays.'/'.$magazine)?>">
			<table border="0">
				<tr><td>Nom d'utilisateur :</td><td><input type="text" name="user" /></td></tr>
				<tr><td>Mot de passe :</td><td><input type="password" name="pass" /></td></tr>
				<tr><td><input style="float:right" type="checkbox" name="mode_expert" /></td><td>Mode expert</td></tr>
				<tr><td align="center" colspan="2"><input type="submit" value="Connexion"/></td></tr>
			</table>
		</form>
		</body></html><?php
		exit(0);
	}*/?>