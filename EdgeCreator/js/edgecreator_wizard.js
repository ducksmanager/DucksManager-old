/*
 * jQuery serializeObject - v0.2 - 1/20/2010
 * http://benalman.com/projects/jquery-misc-plugins/
 * 
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function($,a){$.fn.serializeObject=function(){var b={};$.each(this.serializeArray(),function(d,e){var f=e.name,c=e.value;b[f]=b[f]===a?c:$.isArray(b[f])?b[f].concat(c):[b[f],c]});return b}})(jQuery);

var wizard_options={};
var id_wizard_courant=null;
var id_wizard_precedent=null;
var num_etape_courante=null;

var url_viewer='viewer_wizard';
var NB_MAX_TRANCHES_SIMILAIRES_PROPOSEES=10/2;
var LARGEUR_DIALOG_TRANCHE_FINALE=65;
var LARGEUR_INTER_ETAPES=60;
var OPTIONS_FONCTIONS={'Remplir':['Pos_x','Pos_y','Couleur'],
					   'TexteMyFonts':['URL','Couleur_texte','Couleur_fond','Largeur','Chaine',
					                   'Pos_x','Pos_y','Compression_x','Compression_y',
					                   'Rotation','Demi_hauteur','Mesure_depuis_haut']};

function can_launch_wizard(id) {
	if (! (id.match(/^wizard\-[a-z0-9-]+$/g))) {
		jqueryui_alert('Identifiant d\'assistant invalide : '+id);
		return false;
	}
	if ($('#'+id).length == 0) {
		jqueryui_alert('Assistant inexistant : '+id);
		return false;
	}
	return true;
}

function launch_wizard(id) {
	var buttons={};
	
	id_wizard_courant=id;
	
	$('#'+id+' .buttonset').buttonset();
	$('#wizard-1 .buttonset .disabled').button("option", "disabled", true);
	
	if (id != 'wizard-1') {
		buttons["Precedent"]=function() {
			$( this ).dialog( "close" );
			launch_wizard(id_wizard_precedent);
		};
	}

	if (! $('#'+id).hasClass('dead-end')) {
		buttons["Suivant"]=function() {
			var id_wizard_suivant=wizard_check($(this).attr('id'));
			if (id_wizard_suivant != null) {
				wizard_goto($(this),id_wizard_suivant);
			}
		};
	}
	
	$('#'+id).dialog({
		width: 650,
		position: 'top',
		modal: true,
		resizable: $('#'+id).hasClass('resizable'),
		buttons: buttons,
		open:function(event,ui) {
			$(this).css({'maxHeight':($('#body').height()
									 -$(this).parent().find('.ui-dialog-titlebar').height()
									 -$(this).parent().find('.ui-dialog-buttonpane').height()*2)+'px'});
			wizard_init($(this).attr('id'));
		}
	});
}

function wizard_goto(wizard_courant, id_wizard_suivant) {
	if (can_launch_wizard(id_wizard_suivant)) {
		wizard_options[wizard_courant.attr('id')]=wizard_courant.find('form').serializeObject();
		id_wizard_precedent=wizard_courant.attr('id');
		wizard_courant.dialog( "close" );
		launch_wizard(id_wizard_suivant);
	}
}

function wizard_check(wizard_id) {
	var erreur=null;
	var choix = $('#'+wizard_id).find('form').find('[name="choix"]');
	var valeur_choix = choix.filter(':checked').val();
	if (choix.length != 0 && typeof(valeur_choix) == 'undefined') {
		erreur='Le formulaire n\'est pas correctement rempli';
	}
	else {
		if (valeur_choix.match(/to\-wizard\-[0-9]*/g)) {
			switch(wizard_id) {
				case 'wizard-1':
					if (valeur_choix == 'to-wizard-conception'
					 && $('#'+wizard_id).find('form').find('[name="choix_tranche_en_cours"]').filter(':checked').length == 0) {
						erreur='Si vous souhaitez poursuivre une cr&eacute;ation de tranche, cliquez dessus pour la s&eacute;lectionner.<br />'
							  +'Sinon, cliquez sur "Cr&eacute;er une tranche de magazine" ou "Modifier une tranche de magazine".';
					}
				break;
				case 'wizard-creer':
					if (chargement_listes)
						erreur='Veuillez attendre que la liste des num&eacute;ros soit charg&eacute;e';
					else if (valeur_choix != 'to-wizard-numero-inconnu'
						  && $('#wizard_numero').find('option:selected').hasClass('tranche_prete')) {
						erreur='La tranche de ce num&eacute;ro est d&eacute;j&agrave; disponible.<br />'
							  +'S&eacute;lectionnez "Modifier un num&eacute;ro" dans l\'&eacute;cran pr&eacute;c&eacute;dent pour la modifier '
							  +'ou s&eacute;lectionnez un autre num&eacute;ro.';
					}
				break;
					
				case 'wizard-proposition-clonage':
					if (valeur_choix == 'to-wizard-clonage'
					 && $('#'+wizard_id).find('form').find('[name="tranche_similaire"]').filter(':checked').length == 0) {
						erreur='Si vous avez trouv&eacute; une tranche similaire, cliquez dessus pour la s&eacute;lectionner.<br />'
							  +'Sinon, cliquez sur "Cr&eacute;er une tranche originale".';
					}
				break;
			}
		}
	}
	if (erreur != null) {
		jqueryui_alert(erreur);
	}
	else
		return valeur_choix.replace(/to\-(wizard\-[0-9]*)/g,'$1');
}

var chargement_listes=false;
modification_etape=null;
function wizard_init(wizard_id) {
	switch(wizard_id) {
		case 'wizard-1':
			$.ajax({
				url: urls['tranchesencours']+['index'].join('/'),
				dataType:'json',
				type: 'post',
				success:function(data) {
					var tranches_en_cours_existent=typeof(data) == 'object' && data.length > 0;
					if (tranches_en_cours_existent) {
						$('#'+wizard_id+' #tranches_en_cours').removeClass('cache');
						$('#'+wizard_id+' #to-wizard-conception').button('option','disabled',false);
						for (var i_tranche_en_cours in data) {
							var tranche_en_cours=data[i_tranche_en_cours];
							var str_tranche=tranche_en_cours.Pays+'_'+tranche_en_cours.Magazine+'_'+tranche_en_cours.Numero;
							var str_tranche_userfriendly=tranche_en_cours.Magazine_complet+' n&deg;'+tranche_en_cours.Numero;
							var bouton_tranche_en_cours=$('#numero_tranche_en_cours').clone(true).removeClass('init');
							var label_tranche_en_cours=$('#numero_tranche_en_cours').next('label').clone(true);
							bouton_tranche_en_cours.attr({'id':str_tranche,'value':str_tranche});
							label_tranche_en_cours.attr({'for':str_tranche}).html(str_tranche_userfriendly);
							$('#'+wizard_id+' #tranches_en_cours').append(bouton_tranche_en_cours).append(label_tranche_en_cours);
						}
						$('#numero_tranche_en_cours').next('label').remove();
						$('#numero_tranche_en_cours').remove();
						$('#'+wizard_id+' #tranches_en_cours').buttonset();

						$('#'+wizard_id+' #to-wizard-creer, #'+wizard_id+' #to-wizard-modifier').click(function() {
							$('#'+wizard_id+' #tranches_en_cours .ui-state-active').removeClass('ui-state-active');
						});
						$('#'+wizard_id+' #tranches_en_cours label').click(function() {
							$('#'+wizard_id+' #to-wizard-conception').click();
						});
					}
				}
			});
			
		break;
		case 'wizard-creer':
			if (get_option_wizard('wizard-creer', 'wizard_pays') != undefined)
				break;
			
			$('#wizard_pays').change(function() {
				chargement_listes=true;
				var element=$(this);
				var nouveau_pays=element.val();
				wizard_charger_liste_magazines(nouveau_pays);
			});

			$('#wizard_magazine').change(function() {
				chargement_listes=true;
				wizard_charger_liste_numeros($(this).val());
			});
			chargement_listes=true;
			$.ajax({
				url: urls['numerosdispos']+['index'].join('/'),
				dataType:'json',
				type: 'post',
				success:function(data) {
					$('#wizard_pays').html('');
					for (var i in data.pays) {
						$('#wizard_pays')
							.append($('<option>').val(i)
										.html(data.pays[i]));
					}
					$('#wizard_pays').val(get_option_wizard('wizard_pays') || 'fr');
	
					wizard_charger_liste_magazines(pays_sel);
				}
			});
		break;
		
		case 'wizard-proposition-clonage':
			if (get_option_wizard('wizard-proposition-clonage', 'tranche_similaire') != undefined)
				break;
			pays=get_option_wizard('wizard-creer', 'wizard_pays');
			magazine=get_option_wizard('wizard-creer', 'wizard_magazine');
			numero=get_option_wizard('wizard-creer', 'wizard_numero');
			zoom=2;
			selecteur_cellules_preview='#'+wizard_id+' #tranches_pretes_magazine td';
			
			var numero_selectionne=numero;
			var index_numero_selectionne=$('#wizard_numero option[value="'+numero_selectionne+'"]').prop('index');
			var tranches_pretes=new Array();

			var nouvelle_tranche_placee=false;
			var nb_tranches_suivantes=0;
			$.each($('#wizard_numero option.tranche_prete'),function() {
				var index_numero_courant = $('#wizard_numero option[value="'+$(this).val()+'"]').prop('index');
				if (index_numero_courant > index_numero_selectionne) {
					if (!nouvelle_tranche_placee) {
						if (tranches_pretes.length > NB_MAX_TRANCHES_SIMILAIRES_PROPOSEES) // Filtre sur les 5 dernières précédentes
							tranches_pretes=tranches_pretes.slice(tranches_pretes.length-NB_MAX_TRANCHES_SIMILAIRES_PROPOSEES, tranches_pretes.length);
						tranches_pretes.push(numero_selectionne);
						nouvelle_tranche_placee=true;
					}
					if (nb_tranches_suivantes < NB_MAX_TRANCHES_SIMILAIRES_PROPOSEES) {
						tranches_pretes.push($(this).val());
						nb_tranches_suivantes++;
					}
				}
				else {
					tranches_pretes.push($(this).val());
				}
			});
			
			if (!nouvelle_tranche_placee) {
				// Entrer ici signifie qu'il n'y a pas de tranches prêtes après le numéro sélectionné
				if (tranches_pretes.length > NB_MAX_TRANCHES_SIMILAIRES_PROPOSEES) // Filtre sur les 5 dernières précédentes
					tranches_pretes=tranches_pretes.slice(tranches_pretes.length-NB_MAX_TRANCHES_SIMILAIRES_PROPOSEES, tranches_pretes.length);
				tranches_pretes.push(numero_selectionne);
			}
			
			// Pas de proposition de tranche
			if (tranches_pretes.length <= 1) {
				$('#'+wizard_id+' #tranches_pretes_magazine').html('Pas de tranche similaire');
				wizard_goto($('#'+wizard_id),'wizard-conception');
				return;
			}
			
			
			var tableau_tranches_pretes=$('<table>');
			var ligne_numeros_tranches_pretes1=$('<tr>');
			var ligne_tranches_pretes=$('<tr>');
			var ligne_tranche_selectionnee=$('<tr>');
			var ligne_numeros_tranches_pretes2=$('<tr>');
			tableau_tranches_pretes.append(ligne_numeros_tranches_pretes1)
								   .append(ligne_tranches_pretes)
								   .append(ligne_tranche_selectionnee)
								   .append(ligne_numeros_tranches_pretes2);
			$('#'+wizard_id+' #tranches_pretes_magazine').html($('<div>').addClass('buttonset').html(tableau_tranches_pretes));

			for (i in tranches_pretes) {
				var numero_tranche_prete = tranches_pretes[i];
				ligne_tranches_pretes.append($('<td>').data('numero',numero_tranche_prete));
				var td_numero=$('<td>').data('numero',numero_tranche_prete);
				if (numero_tranche_prete == numero_selectionne) {
					td_numero.append($('<b>').html('n&deg;'+numero_tranche_prete+'<br />(Votre tranche)'));
					ligne_tranche_selectionnee.append($('<td>'));
				}
				else {
					td_numero.html('n&deg;'+numero_tranche_prete);
					ligne_tranche_selectionnee.append($('<td>').html($('<input>',{'type':'radio', 'name':'tranche_similaire','readonly':'readonly'}).val(numero_tranche_prete)));
					reload_numero(numero_tranche_prete);
				}
				ligne_numeros_tranches_pretes1.append(td_numero);
				ligne_numeros_tranches_pretes2.append(td_numero.clone(true));
			}
			
			$('#wizard-proposition-clonage .image_preview').click(function(a,b) {
				$('#wizard-proposition-clonage .image_preview').removeClass('selected');
				$(this).addClass('selected');
				$('#wizard-proposition-clonage input[type="radio"][value="'+$(this).data('numero')+'"]').prop('checked',true);
			});
		break;
		
		case 'wizard-clonage':
			$('#'+wizard_id).parent().find('.ui-dialog-buttonpane button').button("option", "disabled", true);
			var numero_a_cloner = get_option_wizard('wizard-proposition-clonage', 'tranche_similaire');
			var nouveau_numero=numero;
			$('#'+wizard_id+' .nouveau_numero').html(nouveau_numero);
			$('#'+wizard_id+' .numero_similaire').html(numero_a_cloner);
			$.ajax({
				url: urls['etendre']+['index',pays,magazine,numero_a_cloner,nouveau_numero].join('/'),
				type: 'post',
				success:function(data) {
					if (typeof(data.erreur) !='undefined')
						jqueryui_alert(data);
					else {
						$('#'+wizard_id).parent().find('.ui-dialog-buttonpane button').button("option", "disabled", false);
						$('#'+wizard_id+' .loading').addClass('cache');
						$('#'+wizard_id+' .done').removeClass('cache');
					}
				},
				failure:function(data) {
					jqueryui_alert('Erreur : '+data);
				}
			});			
		break;
		case 'wizard-conception':
			if (get_option_wizard('wizard-1','choix_tranche_en_cours') != undefined) {
				var tranche_en_cours=get_option_wizard('wizard-1','choix_tranche_en_cours');
				var regex=/([^_]+)_([^_]+)_(.*)/g;
				pays=tranche_en_cours.replace(regex,'$1');
				magazine=tranche_en_cours.replace(regex,'$2');
				numero=tranche_en_cours.replace(regex,'$3');
			}
			selecteur_cellules_preview='.wizard.preview_etape div.image_etape';
			$('#'+wizard_id).dialog('option','position',['right','top']);
			$('#'+wizard_id).parent().css({'left':($('#'+wizard_id).parent().offset().left-LARGEUR_DIALOG_TRANCHE_FINALE-20)+'px'});
			
			$.ajax({
				url: urls['parametrageg_wizard']+['index',pays,magazine,numero,'null','null'].join('/'),
				type: 'post',
				dataType: 'json',
				success:function(data) {
					var etapes=data;
					etapes_valides=new Array();
					for (var etape=0;etape<etapes.length;etape++) {
						etapes_valides.push(etapes[etape]);
					}
					
					etapes_valides.sort(function(etape1,etape2) {
						if (parseInt(etape1.Ordre)<parseInt(etape2.Ordre))
							return -1;
						if (parseInt(etape1.Ordre)>parseInt(etape2.Ordre))
							return 1;
						return 0;
					});
					
					$.ajax({
						url: urls['parametrageg_wizard']+['index',pays,magazine,numero,-1,'null','null'].join('/'),
						type: 'post',
						dataType:'json',
						success:function(data) {
							var texte="";
							for (var option_nom in data) {
								for (var intervalle in data[option_nom]) {
									if (intervalle != 'type' && intervalle != 'valeur_defaut' && intervalle !='description') {
										if (intervalle == "valeur" && typeof(data[option_nom][intervalle]) =='undefined')
											texte=data[option_nom]['valeur_defaut'];
										else 
											texte=data[option_nom][intervalle];
									}
								}
								switch(option_nom) {
									case 'Dimension_x':
										$('#dimension_x').val(texte);
									break;
									case 'Dimension_y':
										$('#dimension_y').val(texte);
									break;
								}
							}
							$('#modifier_dimensions')
								.removeClass('cache')
								.button()
								.click(function() {
									alert( "Modification des dimensions" );
								});
							
							$('#wizard-conception').find('.chargement,form').toggleClass('cache');
							
							var liste_num_etapes=new Array();
							for (var i=0;i<etapes_valides.length;i++) {
								var etape=etapes_valides[i];
								var num_etape=etape.Ordre;
								if (num_etape != -1) {
									var nom_fonction=etapes_valides[i].Nom_fonction;
									
									var wizard_etape = $('.wizard.preview_etape.initial').clone(true);
									var div_preview=$('<div>').data('etape',num_etape+'').addClass('image_etape');
									wizard_etape.html(div_preview);
									
									var posX = $('#wizard-conception').parent().offset().left-LARGEUR_INTER_ETAPES*(etapes_valides.length-i);
									wizard_etape.dialog({
										resizable: false,
										draggable: false,
										width: 'auto',
										minWidth: 0,
										height: 'auto',
										position: [posX,0],
									    closeOnEscape: false,
										modal: false,
										open:function(event,ui) {
											$(this).removeClass('initial');
											$(this).data('etape',num_etape)
												   .data('id_etape',i);
											$(this).closest('.ui-dialog').addClass('dialog-preview-etape')
																		 .data('etape',num_etape)
																		 .data('nom_fonction',nom_fonction);
											$(this).closest('.ui-dialog').find(".ui-dialog-titlebar-close").hide();
											$(this).closest('.ui-dialog').find('.ui-dialog-titlebar').css({'padding':'.3em .6em;'})
																						.html($('<img>',{'height':18,'src':base_url+'images/fonctions/'+nom_fonction+'.png',
								  			   	   											    		 'alt':nom_fonction}))
								  			   	   										.append($('<span>').addClass('cache').html(nom_fonction))
								  			   	   										.addClass('logo_option');
										}
									});
									chargements.push(num_etape+'');
								}
								liste_num_etapes.push(num_etape);
							}
							
							var wizard_etape_finale = $('.wizard.preview_etape.initial').clone(true);
							var div_preview=$('<div>').data('etape','final').addClass('image_etape finale');
							wizard_etape_finale.html(div_preview);
							
							wizard_etape_finale.dialog({
								resizable: false,
								draggable: false,
								width: LARGEUR_DIALOG_TRANCHE_FINALE,
								minWidth: 0,
								height: 'auto',
								position: ['right','top'],
								closeOnEscape: false,
								modal: false,
								open:function(event,ui) {
									$(this).removeClass('initial').addClass('final');
									$(this).data('etape','finale');
									$(this).closest('.ui-dialog').data('etape','finale');
									$(this).closest('.ui-dialog').find(".ui-dialog-titlebar-close").hide();
									$(this).closest('.ui-dialog').find('.ui-dialog-titlebar').css({'padding':'.3em .6em;','text-align':'center'})
																				.html('Tranche<br />finale');
								}
							});

							chargements.push(liste_num_etapes+'');
							
							numero_chargement=numero;
							zoom=1.5;
							chargement_courant=0;
				            charger_preview_etape(chargements[0],true);
				            
							$('.wizard.preview_etape:not(.final)').click(function() {
								var dialogue=$(this).closest('.ui-dialog');
								if (modification_etape != null) {
									if (dialogue.data('etape') == modification_etape.data('etape'))
										return;
									else
										fermer_dialogue_preview(modification_etape);
								}
								modification_etape=dialogue;
								
								var num_etape=dialogue.data('etape');
								num_etape_courante=num_etape;
								var nom_fonction=dialogue.data('nom_fonction');
								
								var section_preview_etape=dialogue.find('.preview_etape');
								section_preview_etape.addClass('modif');
								
								section_preview_etape.dialog('option', 'width', largeur_max_preview_etape_ouverte());
								dialogue.find('.ui-dialog-titlebar').find('span').removeClass('cache');
								dialogue.find('.image_etape').after($('#options-etape--'+nom_fonction).removeClass('cache'));
								placer_dialogues_preview();
								section_preview_etape.dialog('option','buttons',{
									'Annuler': function() {
										
									},
									'Tester': function() {
										tester_options_preview();
									},
									'Valider': function() {
									}
								});

								$.ajax({
							        url: urls['parametrageg_wizard']+['index',pays,magazine,numero,num_etape,'null','null'].join('/'),
							        type: 'post',
							        dataType:'json',
							        success:function(data) {
							        	var valeurs={};
							        	for (var option_nom in data) {
							                if (typeof(data[option_nom]['valeur']) =='undefined')
							                    data[option_nom]['valeur']=data[option_nom]['valeur_defaut'];
							                valeurs[option_nom]=data[option_nom]['valeur'];
							        	}
							        	alimenter_options_preview(valeurs, section_preview_etape, nom_fonction);
							        }
								});
							});
						}
					});
					
				}
			});
		break;
	}
}

function largeur_max_preview_etape_ouverte() {
	var largeur_autres=0;
	$.each($('.wizard.preview_etape:not(.initial),#wizard-conception'), function() {
		largeur_autres+=$(this).dialog('option','width')+LARGEUR_INTER_ETAPES;
	});
	return $(window).width()-largeur_autres;
}

function fermer_dialogue_preview(dialogue) {
	dialogue.dialog('option', 'width', 'auto');
	dialogue.find('.ui-dialog-buttonpane').remove();
	dialogue.find('.ui-dialog-titlebar').find('span').addClass('cache');
	dialogue.find('.options_etape').addClass('cache');
	dialogue.find('[name="form_options"]').remove();
	dialogue.find('.preview_etape').removeClass('modif');
}

function placer_dialogues_preview() {
	for (var id_etape=etapes_valides.length-1;id_etape>0;id_etape--) {
		var dialogue=$('.preview_etape').getElementsWithData('id_etape',id_etape).parent();
		var dialogue_suivant=$('.preview_etape').getElementsWithData('id_etape',parseInt(id_etape)+1).parent();
		var largeur_dialog=dialogue.width();
		if (id_etape === etapes_valides.length-1)// Par rapport au wizard
			var posX=$('#wizard-conception').parent().offset().left-largeur_dialog-25;
		else // Par rapport au dialogue suivant
			var posX=dialogue_suivant.offset().left-largeur_dialog-25;
		if (posX < 0)
			posX=0;
		dialogue.css({'left':posX+'px'});
	}
}

var farbs;
function alimenter_options_preview(valeurs, section_preview_etape, nom_fonction) {
	farbs={};
	var classes_farbs={};
	
	var form_userfriendly=section_preview_etape.find('.options_etape');
	var form_options=$('<form>',{'name':'form_options'});
	for(var nom_option in valeurs) {
		form_options.append($('<input>',{'name':nom_option,'type':'hidden'}).val(valeurs[nom_option]));
	}
	section_preview_etape.append(form_options);
	
	
	var image = section_preview_etape.find('.image_preview');
	switch(nom_fonction) {
		case 'Remplir':
			classes_farbs['Couleur']='';
			
			var cote_croix_remplissage=form_userfriendly.find('.point_remplissage').width();
			var limites_drag=[(image.offset().left				 -cote_croix_remplissage/2+1),
			                  (image.offset().top 				 -cote_croix_remplissage/2+1),
			                  (image.offset().left+image.width() -cote_croix_remplissage/2-1),
			                  (image.offset().top +image.height()-cote_croix_remplissage/2-1)];
			form_userfriendly.find('.point_remplissage').css({'left':(limites_drag[0]+valeurs['Pos_x']*zoom)+'px', 
										 					  'top': (limites_drag[1]+valeurs['Pos_y']*zoom)+'px'})
													    .draggable({containment:limites_drag,
														   		    stop:function(event, ui) {
														   		    	tester_option_preview(nom_fonction,'Pos_x'); 
														   		    	tester_option_preview(nom_fonction,'Pos_y');
														   		    }});
		break;
		case 'TexteMyFonts':
			classes_farbs['Couleur_texte']='.texte';
			classes_farbs['Couleur_fond']='.fond';
			
			form_userfriendly.find('input[name="option-Chaine"]').val(valeurs['Chaine']);
			form_userfriendly.find('input[name="option-Police"]').val(valeurs['URL']);
			form_userfriendly.find('input[name="option-Rotation"]').val(valeurs['Rotation']);
	}
	
	for (var nom_option in classes_farbs) {
		var input=form_userfriendly.find('input[name="option-'+nom_option+'"]');
		ajouter_farb(section_preview_etape.find('.picker'+classes_farbs[nom_option]), 
					 input, nom_fonction, nom_option, '#'+valeurs[nom_option]);
		input.click(function() {
			var this_picker=$(this).prevAll('.picker');
			$('.picker').not(this_picker).addClass('cache');
			this_picker.toggleClass('cache');
		});
	}
}


function ajouter_farb(picker, input, nom_fonction, nom_option, valeur) {
	farbs[nom_option]=$.farbtastic(picker)
					  .linkTo(function() {callback_change_picked_color($(this),input);},
							  function() {callback_test_picked_color  ($(this),input,nom_fonction,nom_option);})
					  .setColor(valeur);
	
}

function tester_options_preview() {
	var dialogue=$('.wizard.preview_etape.modif').closest('.ui-dialog');

	var form_options=dialogue.find('[name="form_options"]');
	chargements=new Array();
	chargements.push(num_etape_courante+'');
	zoom=1.5;
	chargement_courant=0;
	var parametrage=form_options.serialize();
    charger_preview_etape(chargements[0],true,parametrage);
}

function tester_option_preview(nom_fonction,nom_option) {
	var dialogue=$('.wizard.preview_etape.modif').closest('.ui-dialog');
	var form_options=dialogue.find('[name="form_options"]');
	var nom_fonction=dialogue.data('nom_fonction');
	
	var val=null;
	switch(nom_fonction) {
		case 'Remplir':
			var point_remplissage=dialogue.find('.point_remplissage');
			switch(nom_option) {
				case 'Couleur':
					var farb=farbs[nom_option];
					val=farb.color.replace(/#/g,'');
				break;
				case 'Pos_x':
					var limites_drag_point_remplissage=point_remplissage.draggable('option','containment');
					val = new String(parseFloat((point_remplissage.offset().left - limites_drag_point_remplissage[0])/zoom)+'')
							.replace(/([0-9]+\.[0-9]{2}).*/g,'$1');
				break;
				case 'Pos_y':
					var limites_drag_point_remplissage=point_remplissage.draggable('option','containment');
					val = new String(parseFloat((point_remplissage.offset().top - limites_drag_point_remplissage[1])/zoom)+'')
							.replace(/([0-9]+\.[0-9]{2}).*/g,'$1');
				break;
			}
		break;
		case 'TexteMyFonts':
			switch(nom_option) {
				case 'Couleur_fond': case 'Couleur_texte':
					var farb=farbs[nom_option];
					val=farb.color.replace(/#/g,'');
				break;
			}
		break;
	}
	form_options.find('[name="'+nom_option+'"]').val(val);
	
}


function callback_change_picked_color(farb, input_couleur) {
	var couleur=farb[0].color.replace(/#/g,'');
	var r=couleur.substring(0,2),
		g=couleur.substring(2,4),
		b=couleur.substring(4,6);
	var couleur_foncee=parseInt(r,16)
					  *parseInt(g,16)
					  *parseInt(b,16) < 100*100*100;
	input_couleur
		.css({'background-color':'#'+couleur, 
			  'color':couleur_foncee ? '#ffffff' : '#000000'});
	
}


function callback_test_picked_color(farb, input_couleur,nom_fonction,nom_option) {
	tester_option_preview(nom_fonction,nom_option);
}


function wizard_charger_liste_magazines(pays_sel) {
	pays=pays_sel;
	$('#wizard_magazines').children().remove();
	$.ajax({
		url: urls['numerosdispos']+['index',pays].join('/'),
		type:'post',
		dataType: 'json',
		success:function(data) {
			$('#wizard_magazine').html('');
			for (var i in data.magazines) {
				$('#wizard_magazine')
					.append($('<option>').val(i)
						  .html(data.magazines[i]));
			}
			if (get_option_wizard('wizard_magazine') != undefined)
				$('#wizard_magazine').val(get_option_wizard('wizard_magazine'));
			wizard_charger_liste_numeros($('#wizard_magazine').val());
		}
	});
}

function wizard_charger_liste_numeros(magazine_sel) {
	magazine=magazine_sel;
	$.ajax({
		url: urls['numerosdispos']+['index',pays,magazine].join('/'),
		type: 'post',
		dataType: 'json',
		success:function(data) {
			numeros_dispos=data.numeros_dispos;
			var tranches_pretes=data.tranches_pretes;

			$('#wizard_numero').html('');
			for (var numero_dispo in numeros_dispos) {
				if (numero_dispo != 'Aucun') {
					var option=$('<option>').val(numero_dispo).html(numero_dispo);
					var est_dispo=typeof(tranches_pretes[numero_dispo]) != 'undefined';
					if (est_dispo) {
						option.addClass(tranches_pretes[numero_dispo] == 'par_moi'
										 ? 'cree_par_moi'
										 : 'tranche_prete');
					}
					$('#wizard_numero').append(option);
				}
			}
			if (get_option_wizard('wizard_numero') != undefined)
				$('#wizard_numero').val(get_option_wizard('wizard_numero'));
			chargement_listes=false;
		}
	});
}


function get_option_wizard(nom_option) {
	return get_option_wizard(id_wizard_courant, nom_option);
}

function get_option_wizard(id_wizard, nom_option) {
	var options_wizard = wizard_options[id_wizard];
	if (options_wizard == undefined || options_wizard == null)
		return undefined;
	return options_wizard[nom_option] || undefined;
}