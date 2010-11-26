<?php 
if (!Admin::est_admin() && Admin::est_en_travaux()) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" >
   <head>
       <title>DucksManager - <?=EN_MAINTENANCE?></title>
       <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
   </head>
   <body style="background:transparent url('images/travaux.png') no-repeat top right">
		<div style="text-align:left; font-size: 15px; width: 70%; margin:auto; position: absolute; left: 150px; top: 200px;">
		<?=EST_EN_MAINTENANCE?><br />
		<?=DETAILS_MAINTENANCE_1?><?=A?> 13h<?=DETAILS_MAINTENANCE_2?><br /><br />
		<?=MERCI_COMPREHENSION?><br />
		<?=L_ADMIN?>
		</div>
	</body>
</html>

<?php exit(0);
}
?>