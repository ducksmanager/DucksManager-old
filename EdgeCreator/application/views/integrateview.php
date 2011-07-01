<html>
	<head>
		<script type="text/javascript" src="<?=base_url()?>js/prototype.js" ></script>
		<script type="text/javascript" src="<?=base_url()?>js/edgecreatorlib.js" ></script>
		
		<script type="text/javascript">

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

		
		var pays='<?=$pays?>';
		var magazine='<?=$magazine?>';
		var numero='<?=$numero?>';
		var username='<?=$username?>';

		var etapes=new Array();
		var options=new Array();

		var valeurs_options=new Array();
		<?php foreach($options as $num_etape => $valeurs_options) {
			?>valeurs_options[<?=$num_etape?>]=new Array();<?php 
			foreach($valeurs_options as $nom_option=>$valeur_option) {
				?>valeurs_options[<?=$num_etape?>]['<?=$nom_option?>']='<?=$valeur_option?>';<?php	
			}
		}?>
		
		</script>
		<script type="text/javascript" src="<?=base_url()?>js/edgecreator_integrate.js" ></script>
	</head>
	<body id="body">
		<table><tr>
			<td><div id="log"></div></td>
			<td><div id="section_image"></div></td>
		</tr></table>
		<pre><?php print_r($options);?></pre>
	</body>
</html>
