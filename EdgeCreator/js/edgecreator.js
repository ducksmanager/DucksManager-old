var first_cell=null;
var zoom=1.5;
var numeros_dispos;
	var selecteur_cellules='#table_numeros tr:not(.ligne_entete)>td:not(.intitule_numero):not(.cloner)';
var colonne_ouverte=false;

function element_to_numero(elements) {
	if (! Object.isArray(elements))
		elements=new Array(elements);
	var numeros=new Array();
	$A(elements).each(function(element) {
		var id=element.readAttribute('id');
		numeros.push(id.substring(id.lastIndexOf('_')+1,id.length));
	});
	return numeros;
}

function disableselect(e){
	return false
}

function reEnable(){
	return true
}
function reload_observers_cells() {
	if (privilege =='Affichage')
		return;
	
	$$(selecteur_cellules).invoke('stopObserving','mousedown')
						  .invoke('stopObserving','mouseup')
						  .invoke('stopObserving','mousemove')
						  .invoke('stopObserving','click')
						  .invoke('observe','mousedown',function(event) {
								$('table_numeros').onselectstart=new Function ("return false")

								// if NS6
								if (window.sidebar){
									$('table_numeros').onmousedown=disableselect
									$('table_numeros').onclick=reEnable
								}
								if (Event.element(event).hasClassName('cloner') || Event.element(event).up().hasClassName('cloner'))
									return;
								first_cell=Event.element(event);
								if (first_cell.tagName=='DIV')
									first_cell=first_cell.up();
								marquer_cellules(first_cell,first_cell);
						  })

						  .invoke('observe','mousemove',function(event) {

								if (first_cell != null) {
									var element=Event.element(event);
									var this_cell=element;
									if (this_cell.tagName=='DIV')
										this_cell=this_cell.up();
									if (first_cell.previousSiblings().length == this_cell.previousSiblings().length) { // M?
																														// colonne
										marquer_cellules(first_cell,this_cell);
									}
								}
							});

	new Event.observe(window, 'mouseup',function() {
		$$('.tmp').invoke('removeClassName','tmp');
		first_cell=null;
	});
}

function reload_observers_options() {
	$$('.lien_option').invoke('stopObserving','click');
}

function reload_observers_etapes() {

	$$('.lien_etape>span').invoke('stopObserving','click')
						  .invoke('observe','click',function (event) {
		var element=Event.element(event);
		if (element.tagName!='TH')
			element=element.up('th');
		var num_etape=$('table_numeros').down('tr').down('th',element.previousSiblings().length).retrieve('etape');
		charger_etape(num_etape);
	});
	
	if (privilege =='Affichage')
		return;

	$$('.supprimer_etape').invoke('stopObserving','click');
	$$('.supprimer_etape').invoke('observe','click',function (event) {

		var element=Event.element(event).up('th');
		if (element.hasClassName('nouvelle')) {
			window.location=get_current_url();
			return;
		}
		var num_etape_a_supprimer=element.retrieve('etape');
		if (confirm('Etes vous sur(e) de vouloir supprimer l\'etape '+num_etape_a_supprimer+" ?")) {
			$('chargement').update('Suppression de l\'&eacute;tape '+num_etape_a_supprimer+'...');
			new Ajax.Request(urls['supprimerg']+'index/'+pays+'/'+magazine+'/'+num_etape_a_supprimer, {
				method: 'post',
				onSuccess:function(transport) {
					if (transport.responseText.indexOf('Erreur') != -1)
						alert(transport.responseText);
					else
						window.location=get_current_url();
				}
			});
		}
	});

	$$('.ajouter_etape').invoke('stopObserving','click');
	$$('.ajouter_etape').invoke('observe','click',function (event) {
		if ($$('.nouvelle').length > 0) {
			alert('Une etape est deja en train d\'etre ajoutee');
			return;
		}
		var element=Event.element(event).up('th');
		num_etape_avant_nouvelle=element.retrieve('etape');
		if (typeof(num_etape_avant_nouvelle) == 'undefined')
			location.reload();
		fermer_etapes();
		var liste_possibilites=new Element('select',{'id':'liste_possibilites_fonctions'});
		if ($$('[name="entete_etape_-1"]').length >0) {
			liste_possibilites.insert(new Element('option',{'title':'Remplir'}).update('Remplir une zone avec une couleur'))
							  .insert(new Element('option',{'title':'Degrade'}).update('Remplir une zone avec un d&eacute;grad&eacute;'))
							  .insert(new Element('option',{'title':'Agrafer'}).update('Agrafer la tranche'))
							  .insert(new Element('option',{'title':'DegradeTrancheAgrafee'}).update('Remplir la tranche avec un d&eacute;grad&eacute; et l\'agrafer'))
							  .insert(new Element('option',{'title':'Texte'}).update('Ajouter du texte'))
							  .insert(new Element('option',{'title':'Image'}).update('Ins&eacute;rer une image'))
							  .insert(new Element('option',{'title':'Rectangle'}).update('Dessiner un rectangle'))
							  .insert(new Element('option',{'title':'Polygone'}).update('Dessiner un polygone'))
							  .insert(new Element('option',{'title':'Arc_cercle'}).update('Dessiner un arc de cercle'));
		}
		else
			liste_possibilites.update(new Element('option',{'title':'Dimensions'}).update('Sp&eacute;cifier les dimensions d\'une tranche'));
		var bouton_ok=new Element('button').update('OK');
		$('helpers').update('Si ce n\'est pas encore fait, prenez en photo avec un appareil photo num&eacute;rique la tranche que vous souhaitez recr&eacute;er.')
					   .insert(new Element('br'))
					   .insert('Stockez cette photo sur votre ordinateur, vous allez en avoir besoin !')
					   .insert(new Element('br'))
					   .insert('Que voulez-vous faire ? ')
					   .insert(new Element('br'))
					   .insert(liste_possibilites)
					   .insert(bouton_ok);
		bouton_ok.observe('click',function() {
			if ($$('.nouvelle').length > 0 ) {
				alert('Une etape est deja en train d\'etre ajoutee !');
				return;
			}
			var name_sel=$('liste_possibilites_fonctions').down($('liste_possibilites_fonctions').selectedIndex).title;
			var nom_helper='';
			switch(name_sel) {
				case 'Texte':
					nom_helper='whatthefont';
				break;
				default:
					nom_helper=name_sel.toLowerCase();
				break;
			}
			charger_helper(nom_helper+'_1','helper_'+nom_helper,name_sel);
		});
	});
}

function reload_observers_tranches() {
	$$('.ligne_previews .image_etape img')
		.invoke('observe','mouseover', function(ev) {
	   $('chargement').setStyle({'display':'block'});
	   var tranche=Event.element(ev);
	   tranche.setStyle({'cursor':'crosshair'});
	   var nom_option_sel=get_nom_option_sel();
	   if (nom_option_sel != null) {
		   var element_modif=$('valeur_modifiee').down('input,select');
		   if (!isNaN(parseFloat($F(element_modif)))) {
			   var valeur_modifiee_actuelle=$F(element_modif) * zoom;
			   if (nom_option_sel.match(new RegExp(/_x$/)) != null) {
				   $('viewer').insert(new Element('div').addClassName('repere')
										.setStyle({'position':'absolute','top':tranche.cumulativeOffset()['top']+'px','width': '1px', 
												   'borderLeft':'2px solid black', 'height': tranche.height+'px',
												   'left': parseInt(tranche.cumulativeOffset()['left']+valeur_modifiee_actuelle)+'px'}));
			   }
			   else  {
				   if (nom_option_sel.match(new RegExp(/_y$/)) != null) {
					   $('viewer').insert(new Element('div').addClassName('repere')
											.setStyle({'position':'absolute','left':tranche.cumulativeOffset()['left']+'px','height': '1px', 
													   'borderTop':'2px solid black', 'width': tranche.width+'px',
													   'top': parseInt(tranche.cumulativeOffset()['top']+valeur_modifiee_actuelle)+'px'}));
				   }
			   }
		   }
		   adapter_scroll_reperes();
	   }
	   inserer_elements_coord();
	})
		.invoke('observe','mouseout',function(ev) {
	   $('chargement').update();
	   var tranche=Event.element(ev);
	   tranche.setStyle({'cursor':''});
	   $$('.repere').invoke('remove');
	})
		.invoke('observe','mousemove',function(ev) {
	   var tranche=Event.element(ev);
	   var x =Event.pointerX(ev) + $('viewer_inner').scrollLeft - tranche.cumulativeOffset()['left'];
	   var y =Event.pointerY(ev) + $('viewer_inner').scrollTop- tranche.cumulativeOffset()['top'];
	   
	   var x_valeur = toAlwaysFloat(parseInt(10 * x/zoom)/10);
	   var y_valeur = toAlwaysFloat(parseInt(10 * y/zoom)/10);
	   var x_pct = toAlwaysFloat(parseInt(1000 * x / tranche.width)/10);
	   var y_pct = toAlwaysFloat(parseInt(1000 * y / tranche.height)/10);
	   if (!$('X'))
		   inserer_elements_coord();
	   $('X').update(x_valeur+'mm (Largeur x '+x_pct+'%)');
	   $('Y').update(y_valeur+'mm (Hauteur x '+y_pct+'%)');
	});
}

function inserer_elements_coord() {
	$('chargement').update('X = ')
	  .insert(new Element('span',{'id':'X'}))
	  .insert(new Element('br'))
	  .insert('Y = ')
	  .insert(new Element('span',{'id':'Y'}));
}

function est_dans_intervalle(numero,intervalle) {
	if (numero==null || intervalle.indexOf('Tous') != -1 || numero==intervalle)
		return true;
	if (intervalle.indexOf('~')!=-1) {
		var numeros_debut_fin=intervalle.split('~');
		var numeros_debut=numeros_debut_fin[0].split(';');
		var numeros_fin=numeros_debut_fin[1].split(';');
	}
	else {
		var numeros_debut=intervalle.split(';');
		var numeros_fin=intervalle.split(';');
	}
	var trouve=false;
	Object.keys(numeros_debut).each(function(i) {
		var numero_debut=numeros_debut[i];
		var numero_fin=numeros_fin[i];
		if (numero_debut === numero_fin) {
			if (numero_debut == numero) {
				trouve=true;
				return;
			}
		}
		else {
			numero_debut_trouve=false;
			for(numero_dispo in numeros_dispos) {
				if (numero_dispo==numero_debut)
					numero_debut_trouve=true;
				if (numero_dispo==numero && numero_debut_trouve) {
					trouve=true;
					return;
				}
				if (numero_dispo==numero_fin) 
					return;
			}
		}
	});
	return trouve;
}

var chargements;
var chargement_courant;
var numero_chargement;

function preview_numero(element) {
	$$('.regle').invoke('setStyle',{'display':'none'});
	if (privilege == 'Admin' || privilege == 'Edition')
		$$('#save_png,#save_pngs').invoke('setStyle',{'display':'block'});
	var numero=element.up('tr').readAttribute('id').substring('ligne_'.length,element.up('tr').readAttribute('id').length);
	
	var table=new Element('table');
	switch(onglet_sel) {
	   case 'Builder':
		   $('numero_preview').store('numero',numero)
							  .update('N&deg; '+numero);
		for (var i=0;i<=4;i++) {
			var tr=new Element('tr');
			var td=new Element('td');
			if (i==3) {
				tr.addClassName('ligne_previews');
				var image_regle_v=new Element('img',{'id':'regle_verticale','src':base_url+'../images/regle.png'}).addClassName('regle').addClassName('cache');
				td.update(image_regle_v);
			}
			tr.insert(td);
			element.up('tr').select('.num_checked').each(function(td_etape) {
				var num_etape=td_etape.retrieve('etape');
				if (num_etape != -1) {
					var td=new Element('td').store('etape',num_etape+'');
					switch(i) {
						case 0:
							td.addClassName('reload');
							var image_reload=new Element('img',{'src':base_url+'../images/reload.png'}).addClassName('pointer');
							td.update(image_reload);
							image_reload.observe('click',function(event) {
								var element=Event.element(event).up('td');
								var num_etape=element.retrieve('etape');
								reload_etape(num_etape,true);
							});
							break;
						case 1:
							td.update(num_etape)
							.addClassName('num_etape_preview');
							break;
						case 2:
							
							break;
						case 3:
							td.addClassName('image_etape');
							break;
						case 4:
							var image_fond_noir=new Element('img',{'src':base_url+'../images/fond_noir.png','title':'Voir sous fond noir'}).addClassName('pointer');
							td.update(image_fond_noir).addClassName('fond_noir_inactive')
							  .setStyle({'verticalAlign':'top'})
							  .writeAttribute({'id':'fond_noir_'+num_etape});
							image_fond_noir.observe('click',function(event) {
								var element=Event.element(event).up('td');
								var num_etape=element.retrieve('etape');
								element.toggleClassName('fond_noir_active').toggleClassName('fond_noir_inactive');
								reload_etape(num_etape, false);
							});
							break;
						}
						tr.insert(td);
					}
				});
				var tranche_finale=new Element('td').store('etape','final').addClassName('image_etape');
				if (i==1)
					tranche_finale.update('Tranche')
				.addClassName('num_etape_preview final');
				tr.insert(tranche_finale);
				table.insert(tr);
			}
			$('contenu_'+onglet_sel.toLowerCase()).down('.previews').update(table);
			chargements=new Array();
			numero_chargement=numero;
			$$('.num_etape_preview').each(function(td_num_etape) {
				if (td_num_etape.hasClassName('final')) {
					var num_etape=$$('.num_etape_preview:not(.final)').invoke('retrieve','etape');
				}
				else {
					var num_etape=td_num_etape.retrieve('etape');
				}
				chargements.push(num_etape+'');
			});
			chargement_courant=0;
			charger_preview_etape(chargements[chargement_courant],true);
	   break;
	   case 'Previews':
		   if (typeof($('numero_preview_debut').retrieve('numero')) == 'undefined') {
			   changer_titres_images_view('Selectionner le dernier numero a previsualiser');
			   
			   alert('Vous allez previsualiser les tranches a partir du numero '+numero+'\n'
					+'Cliquez sur le lien "Preview" du dernier numero a previsualiser');
			   $('numero_preview_debut').store('numero',numero)
										.update('N&deg; '+numero);
		   }
		   else {
			   changer_titres_images_view('Selectionner le premier numero a previsualiser');
			   
			   $('montrer_details').setStyle({'display':'inline'});
			   $('numero_preview_fin').store('numero',numero)
										.update('N&deg; '+numero);
										
			   var numero_debut = $('numero_preview_debut').retrieve('numero');
			   var numero_fin = $('numero_preview_fin').retrieve('numero'); 
			   for (var ligne=0;ligne<=2;ligne++) {
				var tr=new Element('tr');
				var td=new Element('td');
				if (ligne==2) {
					tr.addClassName('ligne_previews');
					var image_regle_v=new Element('img',{'id':'regle_verticale','src':base_url+'../images/regle.png'}).addClassName('regle').addClassName('cache');
					td.update(image_regle_v);
				}
				tr.insert(td);
				var numero=numero_debut;
				do {
					var numero_fin_depasse = numero==numero_fin;
					var td=new Element('td').store('numero',numero);
					switch(ligne) {
						case 0:
							td.addClassName('reload');
							var image_reload=new Element('img',{'src':base_url+'../images/reload.png'}).addClassName('pointer');
							td.update(image_reload);
							image_reload.observe('click',function(event) {
								var element=Event.element(event).up('td');
								var numero=element.retrieve('numero');
								reload_numero(numero);
							});
						break;
						case 1:
							td.update(numero).addClassName('numero_preview').setStyle({'textAlign':'center'});
						break;
						case 2:
							td.addClassName('image_numero');
						break;
					}
					tr.insert(td);
					if (typeof ($('ligne_'+numero).next()) == 'undefined' || numero_debut == numero_fin)
						break;
					numero = $('ligne_'+numero).next().retrieve('numero');
				} while (!numero_fin_depasse);
				
				table.insert(tr);
			}
			$('contenu_'+onglet_sel.toLowerCase()).down('.previews').update(table);
			
			numero_chargement=null;
			chargements=new Array();
			$$('.numero_preview').each(function(td_numero) {
				var numero=td_numero.retrieve('numero');
				chargements.push(numero.toString());
			});
			chargement_courant=0;
			charger_previews_numeros(chargements[chargement_courant],true);
		}
		   
	   break;
	}
	
}

function fixer_regles(image) {
	$('regle_verticale').setStyle({'height':(300*zoom)});
	
	var image_regle_h=new Element('img',{'id':'regle_horizontale','src':base_url+'../images/regle_h.png'}).addClassName('regle');

	var div_regle_h=new Element('div',{'id':'zone_regle_horizontale'}).addClassName('zone_regle');

	div_regle_h.update(image_regle_h);
	// $('viewer_inner').insert(div_regle_h);

	$$('.regle').invoke('removeClassName','cache').invoke('setStyle',{'display':'block'});
	if (image == null) {
		$$('.image_preview').each(function(image_preview) {
			if (image_preview.retrieve('etape') == $$('.regle')[0].retrieve('etape'))
				image=image_preview;
		});
		if (image == null)
			image=$('contenu_'+onglet_sel.toLowerCase()).down('.ligne_previews').down('.image_preview');
	}
	$('regle_verticale').writeAttribute({'height':image.height*1.1+'px','width':(3*zoom)});
	// $('regle_horizontale').writeAttribute({'width':(300*zoom)});
	$$('.regle').each(function(regle) {
		var offset_left=image.cumulativeOffset().left;
		var offset_top=image.cumulativeOffset().top;
		
		regle.store('etape',image.retrieve('etape'));
		var zone_regle=regle.up('.zone_regle');
		var left,top;
		if (regle.readAttribute('id') == 'regle_horizontale') {
			left=offset_left;
			top=offset_top-regle.height;
			zone_regle.setStyle({'height':regle.height+'px'});
			zone_regle.setStyle({'width':image.width*1.1+'px'});
		}
		if (left < 0) {
			$('viewer_inner').setStyle({'paddingLeft':(-1*left)+'px'});
			left=0;
		}
		else
			$('viewer_inner').setStyle({'paddingLeft':'0px'});
		new Draggable(regle,{'constraint':'horizontal',
							 'starteffect':function(){}
							});
		// zone_regle.setStyle({'left':left+'px'});
		// zone_regle.setStyle({'top':top+'px'});
	});
	$$('.regle').invoke('observe','dblclick',function(ev) {
		var regle=Event.element(ev);
		regle.setStyle({'left':''});
	});
}

function reload_etape(num_etape,recharger_finale) {
	if ($$(selecteur_cellules_preview).length == 2)
		recharger_finale=false;
	var num_etapes_final=$$('.num_etape_preview:not(.final)').invoke('retrieve','etape');
	chargements=new Array();
	chargements[0]=num_etape;
	if (typeof(recharger_finale) == 'undefined' || recharger_finale)
		chargements.push(num_etapes_final);
	chargement_courant=0;
	charger_preview_etape(chargements[chargement_courant],true);
}

function reload_numero(numero) {
	chargements=new Array();
	chargements.push(numero);
	chargement_courant=0;
	charger_previews_numeros(chargements[chargement_courant],true);
}

function charger_previews_numeros(numero,est_visu) {
	numero_chargement=numero;
	var parametrage=new Object();
	var zoom_utilise= est_visu ? zoom : 1.5;
		
	$('chargement').update('Chargement de la preview de la tranche');
	charger_image('numero',urls['viewer']+'/'+[numero,zoom_utilise,'all',JSON.stringify(parametrage),(est_visu?'false':'save'),'false'].join('/'),numero);
}

function charger_preview_etape(etapes_preview,est_visu) {
	var parametrage=new Object();
	var zoom_utilise= est_visu ? zoom : 1.5;
	if (etapes_preview == '')
		etapes_preview=-1;
	if ((typeof(etapes_preview) == 'string' && etapes_preview.indexOf(',') == -1)
	 || typeof(etapes_preview) == 'number')  {
		$('chargement').update('Chargement de la preview de l\'&eacute;tape '+etapes_preview);
		var fond_noir=($('fond_noir_'+etapes_preview) && $('fond_noir_'+etapes_preview).hasClassName('fond_noir_active')) ? 'true':'false';
		var etapes_preview2 = etapes_preview;
		etapes_preview=new Array();
		etapes_preview.push(etapes_preview2);
	}
	else {
		$('chargement').update('Chargement de la preview de la tranche');
		var fond_noir=false;
		if (typeof(etapes_preview) == 'string')
			etapes_preview=etapes_preview.split(new RegExp(/,/g));
	}
	charger_image('etape',urls['viewer']+[numero_chargement,zoom_utilise,etapes_preview.join("-"),JSON.stringify(parametrage),(est_visu?'false':'save'),fond_noir].join('/'),etapes_preview.join("-"));
}

var selecteur_cellules_preview=null;


function charger_image(type_chargement,src,num) {
	var random=Math.random();
	src+='/'+random;
	var image=new Element('img').addClassName('image_preview').store(type_chargement,num);
	var est_visu=src.indexOf('/save') == -1;
	if (!est_visu) {
		switch(privilege) {
			case 'Admin':break;
			case 'Edition':
				if (!confirm('Votre modele de tranche va etre envoye au webmaster pour validation. Continuer ?'))
					return;
			break;
			default:
				alert('Vous ne possedez pas les droits necessaires pour cette action');
				return;
			break;
		}
	}
	var cellules_previews=$$(selecteur_cellules_preview);
	if (type_chargement == 'etape') {
		if (cellules_previews.invoke('retrieve','etape').indexOf(num)==-1) // Numéro d'étape non trouvé
			cellules_previews.last().update(image);
		else
			cellules_previews.each(function(element) {
				if (element.retrieve('etape') == num)
					element.update(image);
			});
	}
	else {
		cellules_previews.each(function(element) {
			if (element.retrieve('numero') == num)
				element.update(image);
		});
	}
	image.writeAttribute({'src':src});
	image.observe('load',function(ev) {
		chargement_courant++;
		var image=Event.element(ev);
		
		if ($$(selecteur_cellules_preview).length == 2)
			$$(selecteur_cellules_preview)[$$(selecteur_cellules_preview).length-1]
				.update(image.clone(true));
		image.observe('click',function(ev) {
			var image=Event.element(ev);
			fixer_regles(image);
		});
		if (!est_visu && chargement_courant >= chargements.length) {
			switch(privilege) {
				case 'Admin':
					if (type_chargement=='etape')
						alert('Image enregistree');
					else
						alert('Images enregistrees');
					$('ligne_'+numero_chargement).addClassName('tranche_prete');
					
				break;
				case 'Edition':
					if (type_chargement=='etape')
						alert('Votre proposition de modele a ete envoyee au webmaster pour validation. Merci !');
					else
						alert('Vos propositions de modeles ont ete envoyees au webmaster pour validation. Merci !');
					$('ligne_'+numero_chargement).addClassName('tranche_en_validation');
					
				break;
			}
		}
		
		// $('regle').writeAttribute({'height':(300*val_zoom)});
		$('chargement').update();
		$('erreurs').update();
		if (chargement_courant < chargements.length) {
			if (type_chargement=='etape')
				charger_preview_etape(chargements[chargement_courant],est_visu);
			else
				charger_previews_numeros(chargements[chargement_courant],est_visu);
		}
		else {
			fixer_regles(null);
			reload_observers_tranches();
		}
			
	});
	image.observe('error',function(event) {
		if (est_visu) {
			// $('regle').writeAttribute({'height':0});
			$('chargement').update('Erreur !');
			$('erreurs').update(new Element('iframe',{'src':Event.element(event).src+'/debug'}));
			chargement_courant++;
			if (chargement_courant < chargements.length) {
				if (type_chargement=='etape')
					charger_preview_etape(chargements[chargement_courant],true);
				else
					charger_previews_numeros(chargements[chargement_courant],true);
			}
		}
	});
}

function toAlwaysFloat(val) {
	return parseInt(val) == parseFloat(val) ? val+'.0' : val;
}

function marquer_cellules(first_cell,last_cell) {
	if (!colonne_ouverte)
		etape_en_cours=$$('.ligne_etapes')[0].down('th',first_cell.previousSiblings().length).retrieve('etape');
	$$('.selected.tmp').invoke('removeClassName','selected tmp');
	   
	var pos_colonne=first_cell.previousSiblings().length;
	$$('td.selected').each(function(selected) {
		if (selected.previousSiblings().length != pos_colonne) // Une cellule precedemment selctionnee n'est pas dans la meme colonne que celle(s) selectionnee(s) maintenant
			selected.removeClassName('selected');
	});
	if (first_cell.up('tr').previousSiblings().length > last_cell.up('tr').previousSiblings().length) { 
		// Echange de la 1ere et derniere cellule
		var temp_cell=first_cell;
		first_cell=last_cell;
		last_cell=temp_cell;
	}
	var current_cell=first_cell;
	while (true) {
		if (!(current_cell.hasClassName('tmp'))) {
			current_cell.toggleClassName('selected');
			current_cell.addClassName('tmp');
		}
		if (current_cell == last_cell) // Derniere cellule de la selection
			break;
		if (current_cell.up('tr').nextSiblings().length==0) // Derniere ligne du tableau
			break;
		current_cell=current_cell.up('tr').next().down('td',pos_colonne);
	}
	assistant_cellules_sel();
}

var nom_option = null; // Nom d'option en cours de modif

function assistant_cellules_sel() {
	if ($$('td.selected').length > 0) {
		var texte=new Element('div').insert(new Element('span').setStyle({'fontWeight':'bold'}).update($$('td.selected').length+' num&eacute;ro(s) s&eacute;lectionn&eacute;(s)'))
									.insert(new Element('br'));
		nom_option=get_nom_option_sel();
		texte.insert('Etape : '+etape_en_cours+'&nbsp;');
		if (nom_option=='Actif') {
			var lien_charger_etape=new Element('a',{'href':'javascript:void(0)'}).update('D&eacute;velopper l\'&eacute;tape');
			texte.insert(lien_charger_etape).insert(new Element('br'));
			lien_charger_etape.observe('click',function() {
				charger_etape(etape_en_cours);
			});
		}
		texte.insert('Option : '+nom_option)
			 .insert(new Element('br'))
			 .insert(new Element('i').update(typeof(descriptions_options[nom_option]) == 'undefined' ? '' : descriptions_options[nom_option]))
			 .insert(new Element('br'))
			 .insert('Valeurs actuelles :');
		var liste_valeurs=new Element('ul');

		var texte_erreurs=new Array('Erreur : ');
		$A(sans_doublons($$('td.selected'))).each(function(td_sel) {
			liste_valeurs.insert(new Element('li').insert(td_sel.retrieve('valeur_reelle') == null ? '[Non d&eacute;fini]' : td_sel.retrieve('valeur_reelle')));
		});
		texte.insert(liste_valeurs);
		var section_modifier_valeur=new Element('div').writeAttribute({'id':'modifier_valeur'})
													  .insert('Modifier la valeur : ')
													  .insert(new Element('br'))
													  .insert(new Element('div').writeAttribute({'id':'valeur_modifiee'}));
		
		if (privilege != 'Affichage')
			$('helpers').update(texte).insert(section_modifier_valeur);
		section_modifier_valeur_terminee=false;
		var succes_formatage=formater_modifier_valeur(nom_option);
		if (succes_formatage) {
			section_modifier_valeur.insert(new Element('button',{'id':'modifier_valeur_ok'}).update('OK'));
			$('modifier_valeur_ok').observe('click',valider_modifier_valeur);
		}
		else {
			section_modifier_valeur.insert('L\'un au moins des num&eacute;ros s&eacute;lectionn&eacute;s n\'est pas d&eacute;fini pour cette &eacute;tape.')
								   .insert(new Element('br'))
								   .insert('Commencez par d&eacute;finir l\'&eacute;tape comme active pour ce num&eacute;ro.');
		}

		jscolor.init();
		
		if (texte_erreurs.length == 1)
			$('erreurs').update();
		else
			$('erreurs').update(texte_erreurs.join('<br />'));
	}
	else if (privilege !='Affichage')
		$('helpers').update();
}

function valider_modifier_valeur() {
	$('modifier_valeur_ok').writeAttribute({'disabled':'disabled'});
	$('chargement').update('Enregistrement des param&egrave;tres...');
	var numeros=element_to_numero($$('td.selected').invoke('up','tr')).join('~');
	var nouvelle_valeur=escape(get_nouvelle_valeur(nom_option)).replace(/%/g,'!');
	var est_nouvelle_fonction=etape_temporaire_to_definitive() ? 'true':'false';
	new Ajax.Request(urls['modifierg']+['index',pays,magazine,etape_en_cours,numeros,nom_option,nouvelle_valeur,plage.join('/'),nom_nouvelle_fonction==null?'Dimensions':nom_nouvelle_fonction,est_nouvelle_fonction].join('/'), {
		method: 'post',
		onSuccess:function(transport) {
			if (transport.responseText.indexOf('Erreur') != -1) {
				alert(transport.responseText);
				return;
			}
				
			$('chargement').update();
			var recharger_etape = nom_option != 'Actif';
			
			reload_observers_etapes();
			
			if (nom_option=='Actif') {
				$$('td.selected')
					.invoke(nouvelle_valeur=='on' ? 'addClassName':'removeClassName','num_checked');
				if ($$('.etape_ouverte').length > 0 && $$('.etape_ouverte')[0].retrieve('etape') == etape_en_cours) {
					fermer_etapes();
					charger_etape(etape_en_cours);
				}
			}
			else {
				if (numeros.split('~').indexOf($('numero_preview').retrieve('numero')) != -1 && recharger_etape) {
					if (etape_en_cours == -1) {
						var num_etapes=$$('.num_etape_preview').invoke('retrieve','etape');
						chargements=num_etapes;
						chargement_courant=0;
						charger_preview_etape(chargements[chargement_courant],true);
					}
					if ($$('.num_etape_preview').invoke('retrieve','etape').indexOf(etape_en_cours+'') != -1)
						reload_etape(etape_en_cours+'');
				}
			}
			etape_temporaire_to_definitive();
			
			if (recharger_etape) {
				charger_etape(etape_en_cours, numeros, nom_option, true);
			}
			$('modifier_valeur_ok').writeAttribute({'disabled':''});
		}
	});
  }

function get_nom_option_sel() {
	if ($$('td.selected').length==0)
		return null;
	var pos_colonne_sel=$$('td.selected')[0].previousSiblings().length;
	var nom_option=$$('.ligne_noms_options')[0].down('th',pos_colonne_sel).retrieve('nom_option');
	if (typeof(nom_option) == 'undefined' || nom_option == '')
		nom_option='Actif';
	return nom_option;
}

function etape_temporaire_to_definitive() {
	var etapes_maj=false;
	$$('.lien_etape').each(function(td_etape) {
		var etape=td_etape.retrieve('etape');
		if (parseInt(etape) != etape) {
			etape=parseInt(etape+.5);
			td_etape.writeAttribute({'name':'entete_etape_'+etape})
					.store('etape',etape)
					.select('.numero_etape').flatten().invoke('update','Etape '+etape);
			etapes_maj=true;
		}
	});
	if (parseInt(etape_en_cours) != etape_en_cours)
		etape_en_cours+=0.5;
	
	$$('.num_checked').each(function(td) {
		var etape=td.retrieve('etape');
		if (parseInt(etape) != etape)
			td.store('etape',parseInt(etape+.5));
	});
	
	$$('.nouvelle').invoke('removeClassName','nouvelle');
	
	if (etapes_maj) {
		reload_observers_etapes();
		return true;
	}
	return false;
}

function sans_doublons(tab){
	NvTab= new Array();
	var q=0;
	tab.each(function(x){
		if (NvTab.invoke('retrieve','valeur_reelle').indexOf(x.retrieve('valeur_reelle')) == -1)
			NvTab[q++]=x;
	});
	return NvTab;
}

var types_options=new Array();
types_options['Actif']='actif';

function formater_valeur(td,nom_option,valeur) {
	if (valeur == null || typeof (valeur) == 'undefined')
		valeur='[Non d&eacute;fini]';

	else if (nom_option.indexOf('Couleur') != -1) {
		if (valeur.indexOf(',') == -1)
			var rgb=[hexToR(valeur),hexToG(valeur),hexToB(valeur)];
		else
			var rgb=valeur.split(new RegExp(/,/g));
		var couleur_texte =  0.213 * rgb[0] +
							 0.715 * rgb[1] +
							 0.072 * rgb[2]
							 < 0.5 ? '#FFF' : '#000';
		td.setStyle({'backgroundColor':'rgb('+rgb.join(',')+')',
					 'color':couleur_texte});
	}
	else if (nom_option.indexOf('Dimension') != -1 || nom_option.indexOf('Decalage') != -1 || nom_option.indexOf('Pos_x') != -1 || nom_option.indexOf('Pos_y') != -1)
		valeur+=' mm';
	else if (nom_option.indexOf('Compression') != -1)
		valeur=parseInt(valeur*100)+'%';
	else if (nom_option.indexOf('Rotation') != -1)
		valeur+='&deg;';
	td.update(valeur);
	return td;
}

function hexToR(h) {return parseInt((cutHex(h)).substring(0,2),16);}
function hexToG(h) {return parseInt((cutHex(h)).substring(2,4),16);}
function hexToB(h) {return parseInt((cutHex(h)).substring(4,6),16);}
function cutHex(h) {return (h.charAt(0)=="#") ? h.substring(1,7):h;}

var section_modifier_valeur_terminee=false;
function formater_modifier_valeur(nom_option) {
	if (nom_option=='Actif') {
		$('valeur_modifiee').update(new Element('input').writeAttribute({'type':'checkbox','checked':'checked'}))
							.insert('&nbsp;Utilis&eacute;');
		return true;
	}
	if ($$('td.selected').invoke('hasClassName','non_concerne').indexOf(true) != -1) { // Au moins un des numeros n'est pas defini pour cette etape
		return false;
	}
	var premiere_valeur_sel=$$('td.selected')[0].retrieve('valeur_reelle');
	if (typeof(premiere_valeur_sel) == 'undefined')
		premiere_valeur_sel='';

	var input_valeur = null;
	switch(types_options[nom_option]) {
		case 'couleur':
			input_valeur=new Element('input').addClassName('color')
								.writeAttribute({'type':'text','value':premiere_valeur_sel});
			$('valeur_modifiee').update(input_valeur);
		break;
		case 'liste': case 'fichier_ou_texte':
			var arg='_';
			if (nom_option=='Source')
				arg=pays;
			new Ajax.Request(urls['listerg']+['index',nom_option,arg].join('/'), {
				method: 'post',
				parameters:'select=true',
				onSuccess:function(transport) {
					if (section_modifier_valeur_terminee)
						return;
					var select = new Element('select').addClassName('switchable');
					var valeur_trouvee=false;
					for(var nom in transport.headerJSON) {
						var option=new Element('option',{'value':nom}).update(transport.headerJSON[nom]);
						if (option.value==premiere_valeur_sel) {
							option.writeAttribute({'selected':'selected'});
							valeur_trouvee=true;
						}
						select.insert(option);
					}
					$('valeur_modifiee').update(select);
					if (types_options[nom_option] == 'fichier_ou_texte' && !$('section_texte_variable')) {
						var div_texte_variable=new Element('div',{'id':'section_texte_variable'});
						$('valeur_modifiee').insert({'after':div_texte_variable});
						var lien_texte_variable=new Element('a').update('nom de fichier variable').addClassName('switchable');
						var lien_nom_fichier=new Element('a').update('nom de fichier fixe').addClassName('switchable cache');
						var input_valeur=new Element('input',{'type':'text','value':premiere_valeur_sel})
													.addClassName('switchable cache');
						div_texte_variable.insert(input_valeur)
			  			  				  .insert('&nbsp;ou&nbsp;')
							  			  .insert(lien_texte_variable)
							  			  .insert(lien_nom_fichier);
						[lien_texte_variable,lien_nom_fichier].invoke('observe','click',toggleSwitchables);
						
						if (!valeur_trouvee && premiere_valeur_sel != '')
							toggleSwitchables();
						section_modifier_valeur_terminee=true;
					}
				}
			});
		break;
		default:
			input_valeur = new Element('input').writeAttribute({'type':'text','value':premiere_valeur_sel});
			$('valeur_modifiee').update(input_valeur);
		break;
	}
	
	if (input_valeur != null) {
		input_valeur.observe('keydown',function(ev) {
			if (ev.keyCode == Event.KEY_RETURN)
				valider_modifier_valeur();
		});
	}

	return true;
}

function toggleSwitchables(ev) {
	if (!$('valeur_modifiee').down('select').hasClassName('cache') && !$('section_texte_variable').down('input').hasClassName('cache'))
		$('valeur_modifiee').down('select').addClassName('cache');
	else
		$('modifier_valeur').select('.switchable').invoke('toggleClassName','cache');
}

function get_nouvelle_valeur(nom_option) {
	switch(types_options[nom_option]) {
		case 'fichier_ou_texte':
			var element= $('valeur_modifiee').down().hasClassName('cache') ? $('section_texte_variable').down() : $('valeur_modifiee').down();
			return $F(element);
		break;
		default:
			return $F($('valeur_modifiee').down());
		break;
	}
}

var valeurs_defaut_options;
var etapes_utilisees=new Array();
var etapes_valides=new Array();
var etape_en_cours=null;

var nb_lignes=null;

var image_ajouter=new Element('img',{'title':'Ajouter une etape','src':base_url+'images/ajouter.png'})
							 .addClassName('ajouter_etape');
var image_supprimer=new Element('img',{'title':'Supprimer l\'etape','src':base_url+'images/supprimer.png'})
							 .addClassName('supprimer_etape');

var num_etape_avant_nouvelle=null;

var parametres_helper=new Object();

var onglet_sel=null;

var pays_sel=null;

new Event.observe(window, 'load',function() {
	if (!$('viewer'))
		return;
	
	$$('.tabnav a').invoke('observe','click',function(ev) {
		var element=Event.element(ev);
		toggle_item_menu(element);
	});
	 
	new Resizeable($('viewer'));
	toggle_item_menu($('Builder'));
	
	$('liste_pays').observe('change',function(ev) {
		var element=Event.element(ev);
		var nouveau_pays=element.options[element.options.selectedIndex].value;
		window.location=urls['edgecreatorg']+'index/'+nouveau_pays;
	});
	
	new Ajax.Request(urls['numerosdispos']+'index', {
		method:'post',
		onSuccess:function(transport) {
			for (var i in transport.headerJSON.pays) {
				$('liste_pays')
					.insert(new Element('option',{'value':i})
							  .update(transport.headerJSON.pays[i]));
			}
			pays_sel = pays == '' || typeof($('liste_pays').down('[value="'+pays+'"]')) == 'undefined' ? 'fr' : pays;
			$('liste_pays').selectedIndex=$('liste_pays').down('[value="'+pays_sel+'"]').index;
			
			$('liste_magazines').observe('change',function(ev) {
				var element=Event.element(ev);
				var nouveau_magazine=element.options[element.options.selectedIndex].value;
				window.location=urls['edgecreatorg']+'index/'+pays_sel+'/'+nouveau_magazine;
			});
			new Ajax.Request(urls['numerosdispos']+'index/'+pays_sel, {
				method:'post',
				onSuccess:function(transport) {
					for (var i in transport.headerJSON.magazines) {
						$('liste_magazines')
							.insert(new Element('option',{'value':i})
								  .update(transport.headerJSON.magazines[i]));
					}
					$('liste_magazines').selectedIndex=$('liste_magazines').down('[value="'+magazine+'"]').index;
					
				}
			});
		}
	});
	
	if (pays != "" && magazine != "") {
		$('chargement').update('Chargement de la liste INDUCKS...');
		new Ajax.Request(urls['numerosdispos']+['index',pays,magazine].join('/'), {
			method: 'post',
			onSuccess:function(transport) {
				if (transport.responseText.indexOf('Nombre d\'arguments insuffisant') != -1) {
					$('nom_magazine').update('Utilisez un nom de magazine valide');
					return;
				}
				numeros_dispos=transport.headerJSON.numeros_dispos;
				var tranches_pretes=transport.headerJSON.tranches_pretes;
	
				var table=new Element('table',{'id':'table_numeros'}).addClassName('bordered').writeAttribute({'border':'1'})
							.insert(new Element('tr').addClassName('ligne_entete ligne_etapes')
													 .insert(new Element('th'))
													 .insert(new Element('th'))
													 .insert(new Element('th')) // Cellule
																				// temporaire
								   )
							.insert(new Element('tr').addClassName('ligne_entete ligne_noms_options')
													 .insert(new Element('th'))
													 .insert(new Element('th'))
													 .insert(new Element('th'))
								   );
				$('corps').insert(table);
				
				
				$$('#filtre_debut,#filtre_fin').each(function(filtre_select) {
					for (var numero_dispo in numeros_dispos)
						if (numero_dispo != 'Aucun') {
							var est_dispo=typeof(tranches_pretes[numero_dispo]) != 'undefined';
							var option=new Element('option',{'value':numero_dispo}).update(numero_dispo);
							if (est_dispo)
								option.addClassName('tranche_prete');
							filtre_select.insert(option);
						}
				});
				$('filtre_fin').selectedIndex = $('filtre_fin').select('option:last')[0].index;

				$('filtre_numeros').down('img').observe('click',function(ev) {
					if (confirm('La page va etre rechargee avec l\'intervalle de numeros selectionne.\nContinuer ?')) {
						plage[0]=$('filtre_debut').options[$('filtre_debut').selectedIndex].value;
						plage[1]=$('filtre_fin').options[$('filtre_fin').selectedIndex].value;
						
						window.location=get_current_url();
					}
				});
				
				if (plage[0]!='null') // Filtre defini dans la page precedente
					recharger_selects_filtres();
				
				var nb_numeros_plage=$('filtre_fin').selectedIndex-$('filtre_debut').selectedIndex;
				if (nb_numeros_plage >= 1000)
					if (restriction_plage())
						return;

	
				var debut_plage_atteint=false;
				var fin_plage_atteint=false;
				for (var numero_dispo in numeros_dispos) {
					if (plage[0] != 'null') {
						if (!debut_plage_atteint) {
							if (numero_dispo == plage[0])
								debut_plage_atteint=true;
							else
								continue;
						}
						if (debut_plage_atteint) {
							if (fin_plage_atteint)
								break;
							if (numero_dispo == plage[1])
								fin_plage_atteint=true;
						}
					}
					if (numero_dispo == 'Aucun')
						continue; 
					var td_cloner=new Element('td');
					if (privilege == 'Admin' || privilege == 'Edition') {
						var image_cloner = new Element('img').writeAttribute({'src':base_url+'images/clone.png','title':'Cloner le numero'})
															 .addClassName('cloner');
						
						td_cloner.update(image_cloner);
					}
					var tr=new Element('tr').writeAttribute({'id':'ligne_'+numero_dispo}).addClassName('ligne_dispo').store('numero',numero_dispo)
											.insert(td_cloner);
					if (typeof(tranches_pretes[numero_dispo]) != 'undefined') {
						tr.addClassName('tranche_prete');
						tr.writeAttribute({'title':'Cette tranche est deja prete'});
					}
					var td=new Element('td').addClassName('intitule_numero')
											.insert(numero_dispo).insert('&nbsp;');
					var span_preview=new Element('span').addClassName('preview')
						.update(new Element('img',{'src':base_url+'images/view.png',
												   'title':'Voir la tranche'}).addClassName('view_preview'));						
					tr.insert(td.insert(span_preview));
					table.insert(tr);
					span_preview.observe('click',function(event) {
						preview_numero(Event.element(event));
					});
					
					if (privilege == 'Admin' || privilege == 'Edition') {
						image_cloner.store('numero',numero_dispo)
									.observe('click',cloner_numero);
					}
								
					var td_temp=new Element('td');
					tr.insert(td_temp);
				}
				$('chargement').update('Chargement des &eacute;tapes...');
				table.insert($$('.ligne_noms_options')[0].clone(true))
					 .insert($$('.ligne_etapes')[0].clone(true));
				new Ajax.Request(urls['parametrageg']+'index/'+pays+'/'+magazine+'/null/null', {
					method: 'post',
					onSuccess:function(transport) {
						var etapes=transport.headerJSON;
						nb_lignes = $('table_numeros').select('tr').length;
						etapes_valides=new Array();
						$$('#table_numeros tr:not(.ligne_entete)').each(function(tr) {
							for (var etape=0;etape<etapes.length;etape++) {
								if (etapes[etape].Ordre == -1 || est_dans_intervalle(tr.retrieve('numero'), etapes[etape].Numero_debut+'~'+etapes[etape].Numero_fin)) {
									if (etapes_valides.indexOf(etapes[etape]) == -1) {
										etapes_valides.push(etapes[etape]);
										continue;
									}
								}
							}
						});
						
						if (etapes_valides.length * nb_numeros_plage >= 1000)
							if (restriction_plage())
								return;

						etapes_valides.sort(function(etape1,etape2) {
							if (parseInt(etape1.Ordre)<parseInt(etape2.Ordre))
								return -1;
							if (parseInt(etape1.Ordre)>parseInt(etape2.Ordre))
								return 1;
							return 0;
						});
						$('table_numeros').select('tr').each(function(tr) {
							for (var i=0;i<etapes_valides.length;i++) {
								charger_etape_ligne(etapes_valides[i],tr);
							}
						});
	
	
						$$('.ligne_entete td').each(function(td) {
							td.replace(new Element('th'));
						});
						
						$$(selecteur_cellules).each(function(td) {
							td.store('valeur_reelle',td.hasClassName('num_checked') ? 'Utilis&eacute;' : 'Non utilis&eacute;')
							  .store('etape',$$('.ligne_etapes')[0].down('th',td.previousSiblings().length).retrieve('etape'));
						});
	
	
						reload_observers_etapes();
						
						reload_observers_cells();
						$('chargement').update();
	
						//Event.observe(window, 'scroll', function(ev) {
						//	setupFixedTableHeader();
						//});
					}
				});
			}
		});
	}

	var zoom_slider = $('zoom_slider');
	new Control.Slider(zoom_slider.down('.handle'), zoom_slider, {
	  values : [1,1.5,2,4,8],
	  range: $R(1,8),
	  sliderValue: 1.5,
	  onChange: function(value) {
		$('zoom_value').update(value);
		zoom=value;
		if (onglet_sel == 'Builder') {
			if ($('numero_preview').retrieve('numero') != null)
				preview_numero($('ligne_'+$('numero_preview').retrieve('numero')).down('.intitule_numero'));
		}
		else {
		   var numero_debut = $('numero_preview_debut').retrieve('numero');
		   var numero_fin = $('numero_preview_fin').retrieve('numero'); 
		   
		   var numero=numero_debut;
		   var chargements=new Array();
		   do {
			   var numero_fin_depasse = numero==numero_fin;
			   chargements.push(numero);
			   numero = $('ligne_'+numero).next().retrieve('numero');
		   } while (!numero_fin_depasse);
		   
		   chargement_courant=0;
		   charger_previews_numeros(chargements[chargement_courant],true);
		}
		$$('.regle').invoke('setStyle',{'display':'none'});
	  },
	  onSlide: function(value) {
		$('zoom_value').update(value);
	  }
	});
	$('zoom_value').update(zoom);
	
	
	$$('.option_previews input').invoke('observe','click',function(ev) {
		var element=Event.element(ev);
		switch (element.id) {
			case 'option_details':
				$('contenu_previews').select('.numero_preview, .reload')
					.invoke('setStyle',{'display':element.checked ? 'block' : 'none'});
			break;
			case 'option_pretes_seulement':
				
			break;
		}
	});
	
	if (privilege == 'Admin' || privilege == 'Edition') {
		$('save_png').observe('click',function() {
		   if (typeof (numero_chargement) != null) {
			   var num_etapes_final=$$('.num_etape_preview:not(.final)').invoke('retrieve','etape');
				chargements=new Array();
				chargements.push(num_etapes_final);
				chargement_courant=0;
				charger_preview_etape(chargements[chargement_courant],false);
		   }
		});
		$('save_pngs').observe('click',function() {
			numero_chargement=null;
			chargements=new Array();
			$$('.numero_preview').each(function(td_numero) {
				var numero=td_numero.retrieve('numero');
				chargements.push(numero.toString());
			});
			chargement_courant=0;
			charger_previews_numeros(chargements[chargement_courant],false);
		});
	}
	if (privilege != 'Affichage') {
		$('toggle_helpers').observe('click',function() {
			$('toggle_helpers').update(
				($('infos').hasClassName('cache') ? 'Cacher':'Montrer') 
			   +' l\'assistant');
		   $('infos').toggleClassName('cache');
		});
	}
	
	$('viewer_inner').observe( 'scroll', function() {
			adapter_scroll_reperes();
		});
	});

function adapter_scroll_reperes() {
	$$('.repere').each(function(repere) {
	repere.setStyle({'marginTop':((-1)*$('viewer_inner').scrollTop)+'px',
					 'marginLeft':((-1)*$('viewer_inner').scrollLeft)+'px'});
	});
}

function toggle_item_menu (element) {
	onglet_sel = element.id;
	var type_chargement=onglet_sel=='Builder' ? 'etape' : 'numero';
	selecteur_cellules_preview='#contenu_'+onglet_sel.toLowerCase()+' .ligne_previews .image_'+type_chargement;
	element=element.tagName=='LI' ? element : element.up();
	element.up().select('li.active').invoke('removeClassName','active');
	$(element).toggleClassName('active');
	element.up().select('li a').pluck('name').each(function(nom) {
		$('contenu_'+nom.toLowerCase()).setStyle({'display':'none'});
	});
	$('contenu_'+element.down().name).setStyle({'display':'block'});
	
	// Sp?fique EdgeCreator
	if (onglet_sel=='Builder')
		var titre_image_view='Voir la composition de cette tranche';
	else
		titre_image_view='Selectionner le premier numero a previsualiser';
	changer_titres_images_view(titre_image_view);
}

function changer_titres_images_view(titre_image_view){
	$$('.preview img').invoke('writeAttribute',{'title':titre_image_view});
}

	function removeFixedTableHeader() {
		$$('.header_fixe').invoke('remove');
}

function setupFixedTableHeader() {
	
	var setup=$('body').scrollTop >= $('table_numeros').cumulativeOffset()['top'] ; // Scroll
																					// en-dessous
																					// du
																					// header
																					// de
																					// la
																					// table
		
	if ($$('.header_fixe').length > 0) {
		if (setup)
			$$('.header_fixe').invoke('removeClassName','cache');
		else
		   $$('.header_fixe').invoke('addClassName','cache');
	}
	if ($$('.header_fixe').length == 0 && setup) {
		var div=new Element('div')
				  .insert($('table_numeros')
							.down('tr').clone(true)
							.addClassName('header_fixe')
						)
				  .insert($('table_numeros')
							.down('tr',1).clone(true)
							.addClassName('header_fixe')
						)
				  .setStyle({'left':$('table_numeros').cumulativeOffset()['left']+$('table_numeros').offsetLeft,
							 'position':'fixed','display':'table',
							 'borderSpacing': '2px 2px',
							 'backgroundColor':'white'});
		$('body').insert(div);
		$$('.header_fixe').invoke('setStyle',{'width':'','height':''});
		$$('.header_fixe').each(function(header_fixe) {
			var i=0;
			header_fixe.select('th').each(function(th) {
				th.writeAttribute({'width':$('table_numeros').down('tr',header_fixe.hasClassName('ligne_etapes') ? 0 : 1)
															 .down('th',i).offsetWidth})
				  .addClassName('header_fixe_col');
				i++;
			});
		});
		reload_observers_etapes();
	}
}

function charger_etape_ligne (etape, tr, est_nouvelle) {
	est_nouvelle=typeof(est_nouvelle) != 'undefined';
	var est_ligne_header = typeof(tr.down('th')) != 'undefined';
	var balise_cellule = est_ligne_header ? 'th':'td';
	var num_etape=etape.Ordre;
	if (num_etape==-1) { // cellule deja existante
		var cellule=tr.down(balise_cellule,2);
	}
	else {
		var num_etape_precedente=parseInt(num_etape-.5);
		var cellule=new Element(balise_cellule).store('etape',num_etape);
		if (num_etape != parseInt(num_etape)) {// Nouvelle etape
			
			tr.down(balise_cellule,$$('[name="entete_etape_'+num_etape_precedente+'"]')[0].previousSiblings().length)
			  	.insert({'after':cellule});
		}
		else
			tr.insert(cellule);
	}
	switch(tr.previousSiblings().length) {
		case 0: case nb_lignes-1:// Ligne des etapes

			var nom_fonction=etape.Nom_fonction;
			cellule
			  .addClassName('lien_etape'+(est_nouvelle ? ' nouvelle':''));
			
			if (privilege !='Affichage')
			  cellule.update(image_supprimer.clone(true));
			
			cellule
			  .insert(new Element('span').addClassName('numero_etape')
										 .update(num_etape == -1 
												 ? 'Dimensions' 
												 : (est_nouvelle ? 'Nouvelle &eacute;tape' : 'Etape '+num_etape)))
			  .insert(new Element('br'))
			  .insert(new Element('img',{'height':18,'src':base_url+'images/'+nom_fonction+'.png',
										 'title':nom_fonction,'alt':nom_fonction}).addClassName('logo_option'));
			  
			if (privilege !='Affichage')
				cellule.insert(image_ajouter.clone(true));
				
			cellule
			  .store('etape',num_etape)
			  .writeAttribute({'name':'entete_etape_'+num_etape});
		break;
		case 1: case nb_lignes-2 :// Ligne des options, vide
			cellule.addClassName('etape_active')
				   .insert(new Element('a',{'href':'javascript:void(0)'}));
		break;
		default:
			if (est_dans_intervalle(tr.retrieve('numero'), etape.Numero_debut+'~'+etape.Numero_fin))
				cellule.update().addClassName('num_checked');
		break;
	}
}

var numero_a_cloner=null;

function cloner_numero (ev) {
	var numero = Event.element(ev).retrieve('numero');
	if (numero_a_cloner == null) {
		numero_a_cloner=numero;
		alert('Vous allez cloner le numero '+numero_a_cloner+'\n'
			 +'Selectionnez le numero vers lequel cloner ses informations');
	}
	else {
		$('chargement').update('Clonage en cours...');
		var nouveau_numero=numero;
		new Ajax.Request(urls['etendre']+'index/'+pays+'/'+magazine+'/'+numero_a_cloner+'/'+nouveau_numero, {
			method: 'post',
			onSuccess:function(transport) {
				if (transport.responseText.indexOf('Erreur') != -1)
					alert(transport.responseText);
				else
					window.location=get_current_url();
			},
			onFailure:function() {
				numero_a_cloner=null;
				alert('Erreur');
			}
		});
	}
}

var nom_nouvelle_fonction=null;

function fermer_etapes() {
	$$('.ligne_noms_options')[0].select('.option_etape').each(function (colonne_entete) {
		var num_colonne=colonne_entete.previousSiblings().length;
		$$('.ligne_dispo,.ligne_noms_options').each(function(ligne) {
			ligne.down('td,th',num_colonne).remove();
		});
	});
	$$('.lien_etape').invoke('writeAttribute',{'colspan':1});
	$$('.etape_ouverte').invoke('removeClassName','etape_ouverte');
	$$('.etape_active').invoke('down','a').invoke('update','');
	colonne_ouverte=false;
}

var descriptions_options=new Array();

function charger_etape(num_etape, numeros_sel, nom_option_sel, recharger) {
	if ($$('.ligne_noms_options')[0].select('.option_etape').length > 0) {
		var est_etape_ouverte= num_etape == $$('.etape_ouverte')[0].retrieve('etape');
		fermer_etapes();
		if (est_etape_ouverte && typeof(recharger) == 'undefined')
			return;
	}
	
	var element=$$('[name="entete_etape_'+num_etape+'"]:not(.header_fixe_col)')[0];

	var num_colonne=element.previousSiblings().length;
	if (num_etape == -1)
		$('chargement').update('Chargement des param&egrave;tres des dimensions de tranche...');
	else
		$('chargement').update('Chargement des param&egrave;tres de l\'&eacute;tape '+num_etape+'...');
	removeFixedTableHeader();
	new Ajax.Request(urls['parametrageg']+['index',pays,magazine,num_etape,nom_nouvelle_fonction==null?'null':nom_nouvelle_fonction].join('/'), {
		method: 'post',
		parameters: 'etape='+num_etape,
		onSuccess:function(transport) {
			$$('[name="entete_etape_'+num_etape+'"]')[0].addClassName('etape_ouverte');
			colonne_ouverte=true;
			etape_en_cours=num_etape;
			var nb_options=Object.values(transport.headerJSON).length;
			
			$$('.ligne_etapes').each(function(ligne_etape) {
				var colspan = ligne_etape.down('th',num_colonne).readAttribute('colspan');
				ligne_etape.down('th',num_colonne)
						   .writeAttribute({'colspan':parseInt(colspan == null ? 1:colspan)+nb_options});
			});
			var i=0;
			var contenu;
			var texte='';
			types_options=new Array();
			valeurs_defaut_options=new Array();
			for (var option_nom in transport.headerJSON) {
				types_options[option_nom]=transport.headerJSON[option_nom]['type'];
				// if
				// (typeof(transport.headerJSON[option_nom]['valeur_defaut'])
				// != 'undefined')
				// valeurs_defaut_options[option_nom]=transport.headerJSON[option_nom]['valeur_defaut'];

				$$('.ligne_noms_options').each(function(ligne) {
					var nouvelle_cellule=new Element('th')
											.addClassName('etape_'+num_etape+'__option')
											.addClassName('option_etape')
											.store('etape',num_etape);
					contenu=new Element('a',{'href':'javascript:void(0)'}).insert(option_nom);
					if (transport.headerJSON[option_nom]['description'] != '') {
						contenu.insert(new Element('span').update(transport.headerJSON[option_nom]['description']));
						descriptions_options[option_nom]=transport.headerJSON[option_nom]['description'];
					}
					
					nouvelle_cellule.insert(contenu)
									.store('nom_option',option_nom);
					ligne.down('th',num_colonne+i).insert({'before':nouvelle_cellule});
				});
				i++;
			}

			i=0;
			for (var option_nom in transport.headerJSON) {
				$$('.ligne_dispo').each(function(ligne) {
					nouvelle_cellule=new Element('td');
					var numero=ligne.retrieve('numero');
					var etape_utilisee=ligne.down('td',$$('[name="entete_etape_'+num_etape+'"]')[0].previousSiblings().length+i).hasClassName('num_checked');
					if (etape_utilisee) {
						if (typeof(transport.headerJSON[option_nom])=='string')
							transport.headerJSON[option_nom]=new Array(transport.headerJSON[option_nom]);

						texte=null;
						for (var intervalle in transport.headerJSON[option_nom]) {
							if (intervalle != 'type' && intervalle != 'valeur_defaut' && intervalle !='description') {
								if (intervalle == "" && typeof(transport.headerJSON[option_nom][intervalle]) !='undefined')
									texte=transport.headerJSON[option_nom]['valeur_defaut'];
								else if (est_dans_intervalle(numero, intervalle))
									texte=transport.headerJSON[option_nom][intervalle];
							}
						}
						nouvelle_cellule=formater_valeur(nouvelle_cellule,option_nom,texte)
										 .store('valeur_reelle',texte);
					}
					else
						nouvelle_cellule.update().addClassName('non_concerne');
					ligne.down('td',num_colonne+i).insert({'before':nouvelle_cellule});

				});
				i++;
			}
			if (typeof(numeros_sel) != 'undefined') {
				numeros_sel.split(new RegExp(/~/g)).each(function(numero_sel) {
					$$('.ligne_noms_options')[0].select('.option_etape').each(function(nom_option_th) {
						if (nom_option_th.retrieve('nom_option') == nom_option_sel)
							$('ligne_'+numero_sel).down('td',nom_option_th.previousSiblings().length).addClassName('selected');
					});
				});
				assistant_cellules_sel();
			}
			
			$$('.etape_active').invoke('down','a').invoke('update','Active');
			
			$('chargement').update();
			reload_observers_cells();
			//setupFixedTableHeader();
		}
	});
}

function restriction_plage() {
	if (confirm('Le nombre d\'informations sur les tranches de ce magazine semble tres important.\n'
			   +'Pour des raisons de fluidite, il est conseille de restreindre la plage de numeros a afficher.\n'
			   +'Voulez vous indiquer une plage de numeros ?')) {
		alert('Utilisez les listes deroulantes en haut de la page pour indiquer le premier et le dernier numero de la plage, puis cliquez sur le filtre pour valider');
		$('chargement').update();
		return true;/* 
		var plage_debut_entre=prompt('Entrez le premier numero de la plage');
		 if (plage_debut_entre == null)
			 alert('Operation annulee');
		 else {
			 var plage_fin_entre=prompt('Entrez le dernier numero de la plage');
			 if (plage_fin_entre == null)
				 alert('Operation annulee');
			 else {
				 if (Object.values(numeros_dispos).indexOf(""+plage_debut_entre) == -1) {
					alert('Le numero de debut ne fait pas partie de la liste Inducks, abandon.');
				 }
				 else if (Object.values(numeros_dispos).indexOf(""+plage_fin_entre) == -1) {
					alert('Le numero de fin ne fait pas partie de la liste Inducks, abandon.');
				 }
				 else {
					 plage=new Array(plage_debut_entre,plage_fin_entre);
					 recharger_selects_filtres();
				 }
			 }
		 }*/
	}
	return false;
}

function recharger_selects_filtres() {
	$('filtre_debut').select('option').each(function(option) {
		if (option.value == plage[0])
			$('filtre_debut').selectedIndex = option.index;
	});

	$('filtre_fin').select('option').each(function(option) {
		if (option.value == plage[1])
			$('filtre_fin').selectedIndex = option.index;
	});
}

function charger_helper(nom_helper, nom_div, nom_fonction) {
	$('liste_possibilites_fonctions').selectedIndex = $('liste_possibilites_fonctions').down('[title="'+nom_fonction+'"]').index;
	
	if (!$(nom_div))
		$('helpers').insert(new Element('div',{'id':nom_div}));
	new Ajax.Request(base_url+'index.php/helper/index/'+nom_helper+'.html', {
		method: 'post',
		parameters: 'nom_helper='+nom_helper,
		onFailure:function() {
			alert('Page de helper introuvable : '+nom_helper+'.html');
		},
		
		onSuccess:function(transport) {
			var suivant_existe=transport.responseText.indexOf('...') != -1;
			var texte=transport.responseText;
			var nom_fonction_fin=texte.match(new RegExp('!([^!]+)!','g'));
			var est_dernier=nom_fonction_fin != null;
			texte=texte.replace(new RegExp('\\.\\.\\.','g'),'')
					   .replace(new RegExp('!([^!]+)!','g'),'');
			var numero_helper=nom_helper.substring(nom_helper.length-1,nom_helper.length);
			if (numero_helper>1) {
				var lien_precedent=new Element('a');//.update('&lt;&lt; Pr&eacute;c&eacute;dent');
				$(nom_div).update(new Element('br'))
						  .insert(lien_precedent);
				lien_precedent.observe('click',function() {
					var nom_helper_suivant= nom_helper.substring(0,nom_helper.length-1)+(parseInt(numero_helper)-1);
					charger_helper(nom_helper_suivant,nom_div,false,nom_fonction);
				});
			}
			else
				$(nom_div).update();
			
			$(nom_div).insert(texte)
					  .store('numero_helper',numero_helper);
			if (suivant_existe) {
				var lien_suivant=new Element('a').update('Suivant &gt;&gt;');
				$(nom_div).insert(lien_suivant);
				lien_suivant.observe('click',function() {
					var nom_helper_suivant= nom_helper.substring(0,nom_helper.length-1)+(parseInt(numero_helper)+1);
					charger_helper(nom_helper_suivant,nom_div,nom_fonction);
				});
			}
			if (est_dernier) {
				var nouvelle_etape=new Object();
				nouvelle_etape['Nom_fonction']=nom_fonction_fin[0].replace(new RegExp('!','g'),'');
				nouvelle_etape['Numero_debut']='';
				nouvelle_etape['Numero_fin']='';
				nouvelle_etape['Ordre']=parseInt(num_etape_avant_nouvelle)+.5;
				nom_nouvelle_fonction=nouvelle_etape['Nom_fonction'];
				$('table_numeros').select('tr').each(function(tr) {
					charger_etape_ligne(nouvelle_etape,tr, true);
				});
				reload_observers_etapes();
				reload_observers_cells();
			}

		}
	});
}

function remplacer_caracteres_whatthefont() {
	var nom_police=$('url_police').value.replace(new RegExp(/(?:http:\/\/)?(?:new\.)?myfonts.com\/fonts\/(.*)\//g),'$1');
	nom_police=nom_police.replace(new RegExp(/\//g),'.');
	$('nom_police').update('Notez le nom de la police correspondant &agrave; votre texte :')
				   .insert(new Element('br'))
				   .insert(new Element('b').update(nom_police));
}

function get_current_url() {
	var url=urls['edgecreatorg']+['index',pays,magazine].join('/');
	url+='/'+etape_ouverture;
	if (plage[0] != 'null')
		url+='/'+plage[0];
	if (plage[1] != 'null')
		url+='/'+plage[1];
	return url;
}