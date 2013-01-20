<style type="text/css">
	.manquant {
		background-color: red;
	}
	
	textarea {
		border: 0px;
		width: 300px;
	}
</style>

<table border="1" style="border-collapse: collapse">
	<thead>
    <?php
    include_once('../Util.class.php');
    global $regex;
    global $l10n;
    $regex="#'((?:(?!',').)*)','((?:(?!'\)).)*)'\);#";
    $l10n=array();
    $locales=array('en'=>'Anglais','fr'=>'Francais');
    
    ?><tr><td>Element<?php
    foreach($locales as $locale_abbr=>$locale) {
    	?><td><?=$locale?></td><?php
    	lire_locale($locale_abbr);
    }
    ksort($l10n);
    ?></tr><?php
    
    foreach($l10n as $cle=>$traductions) {
	    ?><tr><td><?=$cle?></td><?php
	    foreach(array_keys($locales) as $locale_abbr) {
	    	if (isset($traductions[$locale_abbr])) {
	    		?><td><textarea readonly="readonly"><?=$traductions[$locale_abbr]?></textarea></td><?php
	    	}
	    	else {
	    		?><td class="manquant"></td><?php
	    	}
	    		
	    }
	    ?></tr><?php
    }
  ?>
</table>


<?php
function lire_locale($lang) {
	global $regex;
	global $l10n;
	
	$fic=Util::lire_depuis_fichier($lang.'.php');
	preg_match_all($regex,$fic,$matches);
	for($i=0;$i<count($matches[0]);$i++) {
		$cle=$matches[1][$i];
		$valeur=str_replace("\\'","'",$matches[2][$i]);
		if (!array_key_exists($cle,$l10n))
			$l10n[$cle]=array();
		$l10n[$cle][$lang]=$valeur;
	}
}
?>