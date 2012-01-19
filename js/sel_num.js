var selection_couleur='indefini';
var selection_activee=false;
var debut_selection=-1;
var current_line=null;
var now_selecting=false;
var element_opacity_temp=1;
var liste;
var text_couleur=null;
var l10n_acquisitions=new Array('modifier_acquisition','supprimer_acquisition','description',
                                'date_invalide','date_invalide','suppression_acquisition_confirmation',
                                'date','nouvelle_acquisition_sauvegarder','selectionner_numeros_a_marquer',
                                'les','numeros_selectionnes_enregistres','avec_etat','et','avec_acquisition','confirmer');

function disableselect(e){
return false
}

function reEnable(){
return true
}

function start_selection(sel) {
	
	//if IE4+
	$('liste_numeros').onselectstart=new Function ("return false")
	
	//if NS6
	if (window.sidebar){
		$('liste_numeros').onmousedown=disableselect
		$('liste_numeros').onclick=reEnable
	}
	$('menu_contextuel').hide();
	debut_selection=parseInt(sel.id.substring(1,sel.id.length+1));
	now_selecting=true;
}

function stop_selection(sel) {
	now_selecting=false;
	var j=0;
	
	var fin_selection=parseInt(sel.id.substring(1,sel.id.length+1));
	
	if (debut_selection==-1) return;
	if (debut_selection>fin_selection) {
		var tmp=debut_selection;
		debut_selection=fin_selection;
		fin_selection=tmp;
	}
	for (var i=debut_selection;i<=fin_selection;i++) {
		if ($('n'+i)) {
			$('n'+i).setOpacity(1);
			if ($('n'+i).hasClassName('num_checked')) {
				$('n'+i).removeClassName('num_checked');
				$('nb_selection').update(parseInt($('nb_selection').innerHTML)-1);
				continue;	
			}
			else {
				$('n'+i).addClassName('num_checked');
				$('nb_selection').update(parseInt($('nb_selection').innerHTML)+1);
			}
		}
	}
	var nb_numeros_sel=parseInt($('nb_selection').innerHTML);
	debut_selection=null;
	if (nb_numeros_sel>1) {
		$('numero_selectionne').setStyle({'display':'none'});
		$('numeros_selectionnes').setStyle({'display':'inline'});
	}
	else {
		$('numero_selectionne').setStyle({'display':'inline'});
		$('numeros_selectionnes').setStyle({'display':'none'});
	}
}

function effacer_infos_acquisition() {
	$('nouvelle_acquisition').update();
}

function changer_affichage(type_numeros) {
	$('nb_selection').update(0);
	$('menu_contextuel').hide();
	if ($('sel_numeros_'+type_numeros).checked) {
		$$('.num_'+type_numeros).each (function(numero) {
			numero.setStyle({'display':''});
		});
	}
	else {
		$$('.num_'+type_numeros).each (function(numero) {
			numero.setStyle({'display':'none'});
			var i=0;
			while ($('n'+i)) {
				$('n'+i).removeClassName('num_checked');
				i++;
			}
		});
	}
		
}

function deselect_old(select) {
	
}
function modifier_acquisition(id_acquisition,item) {
	l10n_action('fillArray',l10n_acquisitions,'l10n_acquisitions');
	var select=$('date_acquisition');
	for (var i=0;i<select.options.length;i++) {
		if (select.options[i].label==id_acquisition) {
			select.selectedIndex=i;
			break;
		}
	}
	$('nouvelle_acquisition').update();
	var date=item.substring(item.indexOf('[')+1,item.indexOf(']'));
	var date_annee=date.substring(0,date.indexOf('-'));
	var date_mois=date.substring(date.indexOf('-')+1,date.lastIndexOf('-'));
	var date_jour=date.substring(date.lastIndexOf('-')+1,date.length);
	var description=item.substring(item.indexOf(' ')+1,item.length);
	var el_date_jour=new Element('input',{'id':'date_jour','type':'text','size':'2','value':date_jour});
	var el_date_mois=new Element('input',{'id':'date_mois','type':'text','size':'2','value':date_mois});
	var el_date_annee=new Element('input',{'id':'date_annee','type':'text','size':'4','value':date_annee});
	var el_description=new Element('input',{'id':'description_acquisition','type':'text','size':'15','value':description});
	var el_modifier_acquisition=new Element('button').insert(l10n_acquisitions['modifier_acquisition']);
	var el_supprimer_acquisition=new Element('button').insert(l10n_acquisitions['supprimer_acquisition']);
	$('nouvelle_acquisition').insert('<br />').insert(l10n_acquisitions['date']).insert(' : ');
	$('nouvelle_acquisition').insert(el_date_jour).insert(' / ').insert(el_date_mois).insert(' / ').insert(el_date_annee).insert('<br />');
	$('nouvelle_acquisition').insert(l10n_acquisitions['description']).insert(' : <br />').insert(el_description);
	$('nouvelle_acquisition').insert('<br />').insert(el_modifier_acquisition);
	el_modifier_acquisition.observe('click',function() {
		if (!isDate(el_date_jour.value+'/'+el_date_mois.value+'/'+el_date_annee.value)) {
			alert(l10n_acquisitions['date_invalide']);
			return; 
		}
		if (el_description.textLength>30) {
			alert(l10n_acquisitions['description_invalide']);
			return;
		}
		var myAjax = new Ajax.Request('Database.class.php', {
		   method: 'post', 
		   parameters:'database=true&modif_acquisition=true&id_acquisition='+id_acquisition+'&date='+el_date_annee.value+'-'+el_date_mois.value+'-'+el_date_jour.value+'&description='+el_description.value,
		   onSuccess:function(transport,json) {
		    	location.reload(true);
		   }
		});
	});
	$('nouvelle_acquisition').insert('<br />').insert(el_supprimer_acquisition);
	el_supprimer_acquisition.observe('click',function() {
		if (confirm()) {
			var myAjax = new Ajax.Request('Database.class.php', {
			   method: 'post', 
			   parameters:'database=true&supprimer_acquisition=true&id_acquisition='+id_acquisition,
			   onSuccess:function(transport,json) {
			    	location.reload();
			   }
			});
		}
	});
}
function changer_date_acquisition(element,afficher_non_specifiee) {
	
	$('nouvelle_acquisition').update();
	var option_sel=$('date_acquisition').options[$('date_acquisition').selectedIndex].value;
	if ($('date_acquisition').selectedIndex==$('date_acquisition').length-1) {
		var date_jour=new Element('input',{'id':'date_jour','type':'text','size':'2'});
		var date_mois=new Element('input',{'id':'date_mois','type':'text','size':'2'});
		var date_annee=new Element('input',{'id':'date_annee','type':'text','size':'4'});
		var description=new Element('input',{'id':'description_acquisition','type':'text','size':'15'});
		var enregistrer_acquisition=new Element('button').insert(l10n_acquisitions['nouvelle_acquisition_sauvegarder']);
		$('nouvelle_acquisition').insert('<br />').insert(l10n_acquisitions['date']).insert(' : ');
		$('nouvelle_acquisition').insert(date_jour).insert(' / ').insert(date_mois).insert(' / ').insert(date_annee).insert('<br />');
		$('nouvelle_acquisition').insert(l10n_acquisitions['description']).insert(' : <br />').insert(description);
		$('nouvelle_acquisition').insert('<br />').insert(enregistrer_acquisition);
		enregistrer_acquisition.observe('click',function() {
			if (!isDate(date_jour.value+'/'+date_mois.value+'/'+date_annee.value)) {
				alert(l10n_acquisitions['date_invalide']);
				return; 
			}
			if (description.textLength>30) {
				alert(l10n_acquisitions['description_invalide']);
				return;
			}
			var myAjax = new Ajax.Request('Database.class.php', {
			   method: 'post', 
			   parameters:'database=true&afficher_non_defini='+afficher_non_specifiee+'&acquisition=true&date_annee='+date_annee.value+'&date_mois='+date_mois.value+'&date_jour='+date_jour.value+'&description='+description.value,
			   onSuccess:function(transport,json) {
			    	location.reload();
			   }
			});
		});
	}
	
}

function enregistrer_changements_liste() {
	if (!numeros_marques()) {
		alert(l10n_acquisitions['selectionner_numeros_a_marquer']);
		return;
	}
	var acquisition_id=$('date_acquisition').selectedIndex;
	var acquisition_sel=$('date_acquisition').options[acquisition_id].value;
	/*
	if (acquisition_sel=='Nouvelle acquisition...') {
		alert(l10n_acquisitions['date_invalide']);
		return;
	}*/
	acquisition_sel=acquisition_sel.substring(acquisition_sel.indexOf('[')+1,acquisition_sel.indexOf(']'));
	var text_validation=l10n_acquisitions['les']+liste.length+l10n_acquisitions['numeros_selectionnes_enregistres'];
	if (text_couleur) {
		text_validation+=' '+l10n_acquisitions['avec_etat']+' "'+text_couleur+'"';
		if (acquisition_id!=0)
			text_validation+=l10n_acquisitions['et'];
	}
	if (acquisition_id!=0)
		text_validation+=l10n_acquisitions['avec_acquisition']+' '+$('date_acquisition').value;
	text_validation+='.\n'+l10n_acquisitions['confirmer'];
	var valider=confirm(text_validation);
	
	if (valider) {
		
		if (acquisition_id==0) // Date non spécifiée
			acquisition_sel=null;
		update_numeros(liste,selection_couleur,acquisition_sel);
	}
}

function pre_select(element) {
	if (now_selecting) {
		var selection_courante=parseInt(element.id.substring(1,element.id.length+1));
		var debut_selection_temp=debut_selection;
		var fin_selection=selection_courante;
		if (debut_selection==-1) return;
		if (debut_selection>selection_courante) {
			fin_selection=debut_selection;
			debut_selection_temp=selection_courante;
		}
		for (var i=debut_selection_temp;i<=fin_selection;i++) {
			$('n'+i).setOpacity(0.5);

		}
	}
}

function lighten (element) { 
	if (!now_selecting) {
		element.addClassName('survole');
	}
}

function unlighten (element) {
	if (!now_selecting) {
		element.removeClassName('survole');
	}
}

function lighten_colorselector (element) { 
	if (element.id!='colorsel_'+selection_couleur)
		element.style.border='1px solid gray'; 
}

function unlighten_colorselector (element) {
	if (element.id!='colorsel_'+selection_couleur)
		element.style.border=''; 
}

 function isDate(d) {
	 // Cette fonction permet de vérifier la validité d'une date au format jj/mm/aa ou jj/mm/aaaa
	 // Par Romuald
	
	 if (d == "") // si la variable est vide on retourne faux
	 return false;
	
	 e = new RegExp("^([0-9]{4})\-[0-9]{1,2}\-[0-9]{1,2}$");
	
	 if (!e.test(d)) // On teste l'expression régulière pour valider la forme de la date
	 return false; // Si pas bon, retourne faux
	
	 // On sépare la date en 3 variables pour vérification, parseInt() converti du texte en entier
	 a = parseInt(d.split("-")[0], 10); // année
	 m = parseInt(d.split("-")[1], 10); // mois
	 j = parseInt(d.split("-")[2], 10); // jour
	
	 // Définition du dernier jour de février
	 // Année bissextile si annnée divisible par 4 et que ce n'est pas un siècle, ou bien si divisible par 400
	 if (a%4 == 0 && a%100 !=0 || a%400 == 0) fev = 29;
	 else fev = 28;
	
	 // Nombre de jours pour chaque mois
	 nbJours = new Array(31,fev,31,30,31,30,31,31,30,31,30,31);
	
	 // Enfin, retourne vrai si le jour est bien entre 1 et le bon nombre de jours, idem pour les mois, sinon retourn faux
	 return ( m >= 1 && m <=12 && j >= 1 && j <= nbJours[m-1] );
 } 