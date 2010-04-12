var l10n_calculs_auteurs=new Array('calcul_en_cours','calcul_termine');

var types_listes=null;
var statAjax;
function implement_drags() {
	var draggable_boxes=$$('.draggable_box');
	for(var i=0;i<draggable_boxes.length;i++) {
		new Draggable(draggable_boxes[i]);
		
	}
}

function get_types_listes() {
	var myAjax = new Ajax.Request('Liste.class.php', {
		   method: 'post',
		   parameters:'types_liste=true',
		   onSuccess:function(transport,json) {
		    	var reg=new RegExp(";", "g");
		    	types_listes=transport.responseText.split(reg);
		   }
	});
}

function init_autocompleter_auteurs() {
	l10n_action('fillArray',l10n_calculs_auteurs,'l10n_calculs_auteurs');
	if (!($('auteur_cherche'))) return;
	new Ajax.Autocompleter ('auteur_cherche',
        'liste_auteurs',
        'auteurs_choix.php',
        {
            method: 'post',
            paramName: 'value',
            afterUpdateElement: ac_return
		});
}


function ac_return(field, item){
	$('auteur_nom').value=field.value;
	$('auteur_id').value=item.down().down().next().title;
}

function ajouter_auteur() {
	var nom_auteur=new Element('div').update($('auteur_cherche').value);
	var abbrev_auteur=new Element('div',{'class':'abbrev'}).update($('auteur_id'));
	$('auteurs_ajoutes').insert(nom_auteur).insert(abbrev_auteur);
}



function stats_auteur(/*id_event,*/id_user) {
	/*$('update_stats').update('<div style="border:1px solid black;width:100px;">'
			+'<div id="progressbar"
			
			 style="background-color:blue;width:0%;">&nbsp;</div>'
			+'</div>');
	initProgressBar(id_event);*/
	$('resultat_stats').update(l10n_calculs_auteurs['calcul_en_cours']);
	var myAjax3 = new Ajax.Request('stats_auteur2.php', {
	   	method: 'post',
	   	parameters:'id_user='+id_user,
	   	onSuccess:function(transport,json) {
	   		$('resultat_stats').update(l10n_calculs_auteurs['calcul_termine']);
	   		window.location.reload();
	   		//statAjax.stop();
	   	}
	});
}
