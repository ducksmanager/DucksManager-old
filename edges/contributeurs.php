<?php
include_once('../Database.class.php');
include_once('../authentification.php');

if (isset($_GET['contributions'])) {
	$requete_contributions='SELECT publicationcode, issuenumber FROM tranches_pretes '
						  .'WHERE createurs REGEXP \'(^|,)('.$_GET['contributeur'].')($|,)\' '
						  .'ORDER BY publicationcode';
	$resultat_contributions=DM_Core::$d->requete_select($requete_contributions);

	$contributions=array();
	foreach($resultat_contributions as $contribution) {
		$contributions[]=$contribution;
	}
	echo json_encode($contributions);
	exit(0);
}
elseif (isset($_GET['ajouter_contributeur'])) {
	if (strpos($_GET['issuenumber'],'->') !== false) {
		list($debut,$fin)=explode('->',$_GET['issuenumber']);
		if (intval($debut) != $debut) {
			echo $debut." n'est pas un entier, abandon\n";
		}
		elseif (intval($fin) != $fin) {
			echo $fin." n'est pas un entier, abandon\n";
		}
		else {
			for ($numero=$debut;$numero<=$fin;$numero++) {
				ajouter_contributeur($_GET['publicationcode'], $numero, utf8_encode($_GET['contributeur']));
			}
		}
	}
	else {	
		ajouter_contributeur($_GET['publicationcode'], $_GET['issuenumber'], utf8_encode($_GET['contributeur']));
	}
	exit(0);
}

function ajouter_contributeur($publicationcode, $issuenumber, $contributeur) {
	$requete_tranche_prete='SELECT 1 FROM tranches_pretes '
								   .'WHERE publicationcode=\''.$publicationcode.'\' '
								     .'AND issuenumber=\''.$issuenumber.'\'';
	if (count(DM_Core::$d->requete_select($requete_tranche_prete)) == 0) {
		echo 'La tranche '.$publicationcode.' '.$issuenumber.' n\'est pas pr&ecirc;te'."\n";
	}
	else {
		$requete_contribution_existante='SELECT 1 FROM tranches_pretes '
									   .'WHERE publicationcode=\''.$publicationcode.'\' '
										 .'AND issuenumber=\''.$issuenumber.'\' '
										 .'AND createurs REGEXP \'(^|,)('.$contributeur.')($|,)\' ';
		$contribution_existe=count(DM_Core::$d->requete_select($requete_contribution_existante))> 0;
		if ($contribution_existe) {
			echo $contributeur.' est d&eacute;j&agrave; marqu&eacute; '
				.'comme contributeur &agrave; '.$publicationcode.' '.$issuenumber."\n";
		}
		else {
			$requete='UPDATE tranches_pretes '
					.'SET createurs=CONCAT(createurs, \','.$contributeur.'\') '
					.'WHERE publicationcode=\''.$publicationcode.'\' '
					  .'AND issuenumber=\''.$issuenumber.'\'';
			DM_Core::$d->requete($requete);
			echo $contributeur.' ajout&eacute; '
				.'comme contributeur &agrave; '.$publicationcode.' '.$issuenumber."\n";
		}
	}
}

$requete_utilisateurs='SELECT username FROM users ORDER BY UPPER(username)';
$resultat_utilisateurs=DM_Core::$d->requete_select($requete_utilisateurs);
$utilisateurs=array();
foreach($resultat_utilisateurs as $utilisateur) {
	$utilisateurs[]=utf8_decode($utilisateur['username']);
}

?>
<html>
<head>
<style type="text/css">
	td {
		vertical-align: top;	
	}
</style>
<script type="text/javascript" src="../EdgeCreator/js/jquery-1.8.2.js"></script>

<script type="text/javascript">
	var matches;
	var current_match;
	var regex=/([^\/]+\/[^ ,$]+)(?:(?: ([^,$]+)(?:, |$))|(?:, |$))/g;
	var contributeur;
	
	$(window).load(function() {
		$('#utilisateurs').change(function(ui,event) {
			charger_contributions($('#utilisateurs').val());
		});

		$('#modifier').click(function() {
			contributeur=$('#utilisateurs').val();
			matches=$('#contributions').val().match(regex);
			$('#log').empty();
			current_match=0;
			traiter_match_suivant();
		});
	});

	function traiter_match_suivant() {
		if (current_match>=matches.length) {
			$('#log').html($('#log').html()+"Termin&eacute;\n");
			return;
		}
		var publicationcode=matches[current_match].replace(regex,'$1');
		var issuenumber=matches[current_match].replace(regex,'$2');
		$.ajax({
			url: 'contributeurs.php?ajouter_contributeur&contributeur='+contributeur
			   +'&publicationcode='+publicationcode+'&issuenumber='+issuenumber,
			type:'get',
			success:function(data) {
				$('#log').html($('#log').html()+data);
				current_match++;
				traiter_match_suivant();
			}
		});
	}
	
	function charger_contributions(contributeur) {
		$('#utilisateurs,#modifier').attr({'disabled':'disabled'});
		var texte_contributions = '';
		$.ajax({
			url: 'contributeurs.php?contributions&contributeur='+contributeur,
			type:'get',
			dataType:'json',
			success:function(data) {
				for(var i in data) {
					texte_contributions+=data[i].publicationcode+" "+data[i].issuenumber+', ';
				}
				$('#contributions').val(texte_contributions);
				$('#utilisateurs,#modifier').removeAttr('disabled');
			}
		});
	}

	function isInteger(s) {
		return s.toString().search(/^[0-9]+$/) == 0;
	}
</script>

</head>
<body>
<table>
	<tr>
		<td>
			<select id="utilisateurs">
				<?php foreach($utilisateurs as $utilisateur) {
					?><option><?=$utilisateur?></option><?php
				}?>
			</select>
		</td>
		<td>
			<textarea id="contributions" rows="30" cols="100"></textarea>
		</td>
		<td>
			<button id="modifier">Modifier</button>
		</td>
		<td>
			<textarea id="log" cols="55" rows="30"></textarea>
		</td>
	</tr>
</table>

</body>
</html>