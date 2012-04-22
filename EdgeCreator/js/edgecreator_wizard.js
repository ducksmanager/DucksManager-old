var wizard_options={};
var id_wizard_courant=null;
var id_wizard_precedent=null;
var num_etape_courante=null;

zoom=1.5;
var url_viewer='viewer_wizard';
var NB_MAX_TRANCHES_SIMILAIRES_PROPOSEES=10/2;
var LARGEUR_DIALOG_TRANCHE_FINALE=65;
var LARGEUR_INTER_ETAPES=25;
var MARGE_DROITE_TRANCHE_FINALE=10;
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
		modal: false,
		autoResize: true,
		resizable: $('#'+id).hasClass('resizable'),
		buttons: buttons,
		open:function(event,ui) {
			var dialog=$(this).closest('.ui-dialog');
			$(this).css({'max-height':(
										$('#body').height()
									   -dialog.find('.ui-dialog-titlebar').height()
									   -dialog.find('.ui-dialog-buttonpane').height()*2
									   -dialog.css('top')
									 )+'px'});
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
	var choix = $('#'+wizard_id+' form [name="choix"]');
	var valeur_choix = choix.filter('[checked="checked"]').val();
	if (choix.length != 0 && typeof(valeur_choix) == 'undefined') {
		erreur='Le formulaire n\'est pas correctement rempli';
	}
	else {
		if (valeur_choix.match(/to\-wizard\-[0-9]*/g)) {
			switch(wizard_id) {
				case 'wizard-1':
					if (valeur_choix == 'to-wizard-conception'
					 && $('#'+wizard_id+' form [name="choix_tranche_en_cours"]').filter(':checked').length == 0) {
						erreur='Si vous souhaitez poursuivre une cr&eacute;ation de tranche, cliquez dessus pour la s&eacute;lectionner.<br />'
							  +'Sinon, cliquez sur "Cr&eacute;er une tranche de magazine" ou "Modifier une tranche de magazine".';
					}
				break;
				case 'wizard-creer':
					if (chargement_listes)
						erreur='Veuillez attendre que la liste des num&eacute;ros soit charg&eacute;e';
					else if (valeur_choix != 'to-wizard-numero-inconnu'
						  && $('#'+wizard_id+' [name="wizard_numero"]').find('option:selected').hasClass('tranche_prete')) {
						erreur='La tranche de ce num&eacute;ro est d&eacute;j&agrave; disponible.<br />'
							  +'S&eacute;lectionnez "Modifier une tranche de magazine" dans l\'&eacute;cran pr&eacute;c&eacute;dent pour la modifier '
							  +'ou s&eacute;lectionnez un autre num&eacute;ro.';
					}
				break;
				case 'wizard-modifier':
					if (chargement_listes)
						erreur='Veuillez attendre que la liste des num&eacute;ros soit charg&eacute;e';
					else if (valeur_choix == 'to-wizard-clonage-silencieux'
						  && !$('#'+wizard_id+' [name="wizard_numero"]').find('option:selected').hasClass('tranche_prete')) {
						erreur='La tranche de ce num&eacute;ro n\'existe pas.<br />'
							  +'S&eacute;lectionnez "Cr&eacute;er une tranche de magazine" dans l\'&eacute;cran pr&eacute;c&eacute;dent pour la cr&eacute;er '
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
							var str_tranche_userfriendly=tranche_en_cours.Magazine_complet+' n&deg;'+tranche_en_cours.Numero;
							var str_tranche=str_tranche_userfriendly+'('+tranche_en_cours.Pays+'_'+tranche_en_cours.Magazine+'_'+tranche_en_cours.Numero+')';
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
		case 'wizard-creer': case 'wizard-modifier':
			if (get_option_wizard('wizard-creer', 'wizard_pays') != undefined)
				break;
			
			$('#'+wizard_id+' [name="wizard_pays"]').change(function() {
				chargement_listes=true;
				var element=$(this);
				var nouveau_pays=element.val();
				wizard_charger_liste_magazines(nouveau_pays);
			});

			$('#'+wizard_id+' [name="wizard_magazine"]').change(function() {
				chargement_listes=true;
				wizard_charger_liste_numeros($(this).val());
			});
			chargement_listes=true;
			wizard_charger_liste_pays();
		break;
		
		case 'wizard-proposition-clonage':
			if (get_option_wizard('wizard-proposition-clonage', 'tranche_similaire') != undefined)
				break;
			pays=get_option_wizard('wizard-creer', 'wizard_pays');
			magazine=get_option_wizard('wizard-creer', 'wizard_magazine');
			numero=get_option_wizard('wizard-creer', 'wizard_numero');
			selecteur_cellules_preview='#'+wizard_id+' #tranches_pretes_magazine td';
			
			var numero_selectionne=numero;
			var index_numero_selectionne=$('#'+wizard_id+' [name="wizard_numero"] option[value="'+numero_selectionne+'"]').prop('index');
			var tranches_pretes=new Array();

			var nouvelle_tranche_placee=false;
			var nb_tranches_suivantes=0;
			$.each($('#'+wizard_id+' [name="wizard_numero"] option.tranche_prete'),function() {
				var index_numero_courant = $('#'+wizard_id+' [name="wizard_numero"] option[value="'+$(this).val()+'"]').prop('index');
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
			
			$('#wizard-proposition-clonage .image_preview').click(function() {
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
				error:function(data) {
					jqueryui_alert('Erreur : '+data);
				}
			});
		break;

		case 'wizard-clonage-silencieux':
			$('#'+wizard_id).parent().find('.ui-dialog-buttonpane button').button("option", "disabled", true);
			pays=get_option_wizard('wizard-modifier', 'wizard_pays');
			magazine=get_option_wizard('wizard-modifier', 'wizard_magazine');
			numero=get_option_wizard('wizard-modifier', 'wizard_numero');
			
			$.ajax({
				url: urls['etendre']+['index',pays,magazine,numero,numero].join('/'),
				type: 'post',
				success:function(data) {
					$('#'+wizard_id).parent().find('.ui-dialog-buttonpane button').button("option", "disabled", false);
					if (typeof(data.erreur) !='undefined')
						jqueryui_alert(data);
					else {
						$('#'+wizard_id+' .loading').addClass('cache');
						$('#'+wizard_id+' .done').removeClass('cache');
					}
				},
				error:function(data) {
					$('#'+wizard_id).parent().find('.ui-dialog-buttonpane button').button("option", "disabled", false);
					jqueryui_alert('Erreur : '+data);
				}
			});
		break;
		
		case 'wizard-conception':
			var numero_complet_userfriendly=null;
			if (get_option_wizard('wizard-1','choix_tranche_en_cours') != undefined) {
				var tranche_en_cours=get_option_wizard('wizard-1','choix_tranche_en_cours');
				var regex=/([^\(]+)\(([^_]+)_([^_]+)_([^\(]+)\)/g;
				numero_complet_userfriendly=tranche_en_cours.replace(regex,'$1');
				pays=tranche_en_cours.replace(regex,'$2');
				magazine=tranche_en_cours.replace(regex,'$3');
				numero=tranche_en_cours.replace(regex,'$4');
			}
			else {
				numero_complet_userfriendly='';
			}
			$('#nom_complet_tranche_en_cours').html(numero_complet_userfriendly);
			$('#action_bar').removeClass('cache');
			selecteur_cellules_preview='.wizard.preview_etape div.image_etape';
			$('#'+wizard_id).dialog('option','position',['right','top']);
			$('#'+wizard_id).parent().css({'left':($('#'+wizard_id).parent().offset().left-LARGEUR_DIALOG_TRANCHE_FINALE-20)+'px'});
			
			$.ajax({ // Numéros d'étapes
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
					
					$.ajax({ // Détails des étapes
						url: urls['parametrageg_wizard']+['index',pays,magazine,numero,-1,'null','null'].join('/'),
						type: 'post',
						dataType:'json',
						success:function(data) {
							$('#zoom').removeClass('cache');
							$('#zoom_slider').slider({change:function(event,ui) {
					            zoom=valeurs_possibles_zoom[ui.value];
					            $('#zoom_value').html(zoom);
								if (etapes_valides.length > 1) {
					    			selecteur_cellules_preview='.wizard.preview_etape div.image_etape';
					            	chargements=new Array();
									for (var i=0;i<etapes_valides.length;i++) {
										if (etapes_valides[i].Ordre != -1)
											chargements.push(etapes_valides[i].Ordre+'');
									}
									chargements.push(chargements+''); // Etape finale
									
									chargement_courant=0;
						            charger_preview_etape(chargements[0],true,undefined /*<-- Parametrage */,function(image) {
						            	var dialogue=image.closest('.ui-dialog');
										var num_etape=dialogue.data('etape');
										var section_preview_etape=dialogue.find('.preview_etape');
										
										recuperer_et_alimenter_options_preview(num_etape, section_preview_etape);						            	
						            });
				            	}
							}});
							
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
							
							for (var i=0;i<etapes_valides.length;i++) {
								var etape=etapes_valides[i];
								var num_etape=etape.Ordre;
								if (num_etape != -1) {
									var nom_fonction=etapes_valides[i].Nom_fonction;
									
									var wizard_etape = $('.wizard.preview_etape.initial').clone(true);
									var div_preview=$('<div>').data('etape',num_etape+'').addClass('image_etape');
									wizard_etape.html(div_preview);
									
									var posX = $('#wizard-conception').parent().offset().left-(etapes_valides.length-i);
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
									wizard_etape.closest('.ui-dialog').bind('resize',function() {
										placer_dialogues_preview();
									});
									chargements.push(num_etape+'');
								}
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
									$(this).closest('.ui-dialog').addClass('dialog-preview-etape finale')
																 .data('etape','finale');
									$(this).closest('.ui-dialog').find(".ui-dialog-titlebar-close").hide();
									$(this).closest('.ui-dialog').find('.ui-dialog-titlebar').css({'padding':'.3em .6em;','text-align':'center'})
																				.html('Tranche<br />finale');
								}
							});

							chargements.push(chargements+''); // On ajoute l'étape finale
							
							numero_chargement=numero;
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
								dialogue.addClass('modif');
								
								var image=dialogue.find('.image_etape');
								var largeur_tranche=image.width();
								section_preview_etape.dialog('option', 'width', largeur_max_preview_etape_ouverte());
								dialogue.find('.ui-dialog-titlebar').find('span').removeClass('cache');
								image.after($('#options-etape--'+nom_fonction)
												.removeClass('cache')
												.css({'margin-left':(image.position().left+largeur_tranche+5*zoom)+'px'}));
								//placer_dialogues_preview();
								section_preview_etape.dialog('option','buttons',{
									'Annuler': function() {
										
									},
									'Tester': function() {
										tester();
									},
									'Valider': function() {
										valider();
									}
								});

								recuperer_et_alimenter_options_preview(num_etape, section_preview_etape);
								
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
	dialogue.removeClass('modif')
			.css({'width':'auto'});
	dialogue.dialog('option', 'width', 'auto');
	dialogue.find('.ui-dialog-buttonpane').remove();
	dialogue.find('.ui-dialog-titlebar').find('span').addClass('cache');
	dialogue.find('.options_etape').addClass('cache');
	dialogue.find('[name="form_options"]').remove();
	dialogue.find('.preview_etape').removeClass('modif');
}

function placer_dialogues_preview() {
	var dialogues=$('.dialog-preview-etape').add($('#wizard-conception').closest('.ui-dialog'));
	dialogues.sort(function(dialogue1,dialogue2) { // Triés par offset gauche, de droite à gauche
		return $(dialogue2).offset().left - $(dialogue1).offset().left;
	});
	$.each(dialogues,function(i,dialogue) {
		var largeur=$(dialogue).width();
		if (i == 0) {
			$(dialogue).css({'left':$(window).width()-largeur-MARGE_DROITE_TRANCHE_FINALE});			
		}
		else {
			var dialogue_suivant=$(dialogues[i-1]);
			var marge_gauche=dialogue_suivant.offset().left-largeur-LARGEUR_INTER_ETAPES;
			if (marge_gauche < 10) {
				marge_gauche=10;
				$(dialogue).width(dialogue_suivant.offset().left-LARGEUR_INTER_ETAPES-10);
			}
			$(dialogue).css({'left':marge_gauche+'px'});
		}
	});
}

function recuperer_et_alimenter_options_preview(num_etape, section_preview_etape) {
	var nom_fonction=section_preview_etape.closest('.ui-dialog').data('nom_fonction');
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
			
			var largeur_croix=form_userfriendly.find('.point_remplissage').width()/2;
			var limites_drag=[(image.offset().left			 	 -largeur_croix+1),
			                  (image.offset().top 			 	 -largeur_croix+1),
			                  (image.offset().left+image.width() -largeur_croix-1),
			                  (image.offset().top +image.height()-largeur_croix-1)];
			form_userfriendly.find('.point_remplissage').css({'left':(image.position().left-largeur_croix+1+parseFloat(valeurs['Pos_x'])*zoom)+'px', 
										 					  'top': (image.position().top -largeur_croix+1+parseFloat(valeurs['Pos_y'])*zoom)+'px'})
										 				.removeClass('cache')
													    .draggable({containment:limites_drag,
														   		    stop:function(event, ui) {
														   		    	tester_option_preview(nom_fonction,'Pos_x'); 
														   		    	tester_option_preview(nom_fonction,'Pos_y');
														   		    }});
		break;
		case 'TexteMyFonts':
			classes_farbs['Couleur_texte']='.texte';
			classes_farbs['Couleur_fond']='.fond';
			
			$.each($(['Chaine','URL','Largeur']),function(i,option_nom) {
				form_userfriendly.find('input[name="option-'+option_nom+'"]').val(valeurs[option_nom]);				
			});
			
			form_userfriendly.find('input[name="option-Chaine"],input[name="option-URL"],input[name="option-Largeur"]').blur(function() {
				var nom_option=$(this).attr('name').replace(/option\-([A-Za-z0-9]+)/g,'$1');
				tester_option_preview(nom_fonction,nom_option);
				load_myfonts_preview(true,true,true);
			});
			
			form_userfriendly.find('input[name="option-Demi_hauteur"]')
				.attr('checked',valeurs[nom_option]=='Oui' ? 'checked':'')
				.change(function() {
					var nom_option=$(this).attr('name').replace(/option\-([A-Za-z0-9]+)/g,'$1');
					tester_option_preview(nom_fonction,nom_option);
					load_myfonts_preview(true,true,true);
			});

			$(document).mouseup( stopRotate );
			var input_rotation=form_userfriendly.find('input[name="option-Rotation"]');
			input_rotation.data('currentRotation',0)
						  .mousedown( startRotate );
			$('[name~="fixer_rotation"]').click(function() {
				var angle=$(this).prop('name').split(/ /g)[1];
				rotateImageDegValue(input_rotation,angle);
			});
			rotateImageDegValue(input_rotation,-1*parseFloat(valeurs['Rotation']));
			
			
			form_userfriendly.find('.accordion').accordion({
				active: 0,
				change:function(event,ui) {
					var section_active_integration=$(this).find('.ui-accordion-content-active').hasClass('finition_texte_genere');
					if (section_active_integration)
						placer_extension_largeur_preview();
					
					var section_active_positionnement=$(this).find('.ui-accordion-content-active').hasClass('positionnement');
					if (section_active_positionnement) {
						tester(function() {
							load_myfonts_preview(false,false,true,function() {
								var dialogue=form_userfriendly.closest('.ui-dialog');
								var valeurs=dialogue.find('[name="form_options"]').serializeObject();
								var image=dialogue.find('.image_preview');
								
								var position_texte=form_userfriendly.find('.position_texte');
								var image_preview_ajustee=$('.positionnement .apercu_myfonts img');
								var ratio_image_preview_ajustee=image_preview_ajustee.prop('width')/image_preview_ajustee.prop('height');
								
								var largeur=toFloat2Decimals(image.width() * parseFloat(valeurs['Compression_x']));
								var hauteur=toFloat2Decimals(image.width() * parseFloat(valeurs['Compression_y']) / ratio_image_preview_ajustee);
								
								var pos_x=image.position().left+parseFloat(valeurs['Pos_x'])*zoom;
								var pos_y=image.position().top +parseFloat(valeurs['Pos_y'])*zoom;
								if (valeurs['Mesure_depuis_haut'] == 'Non') { // Le pos_y est mesuré entre le haut de la tranche et le bas du texte
									pos_y-=parseFloat(hauteur);
								}
	
								var limites_drag=[(image.offset().left			 -parseFloat(largeur)),
								                  (image.offset().top 			 -parseFloat(hauteur)),
								                  (image.offset().left+image.width()),
								                  (image.offset().top +image.height())];
								position_texte.css({'left':pos_x+'px', 
		  										    'top': pos_y+'px',
		  										    'width':largeur+'px',
		  										    'height':hauteur+'px'})
		  									  .removeClass('cache')
		  									  .draggable({//containment:limites_drag, 
										  		  stop:function(event, ui) {
									   		    	tester_option_preview(nom_fonction,'Pos_x'); 
									   		    	tester_option_preview(nom_fonction,'Pos_y');
									   		      }
											  })
											  .resizable({
													stop:function(event, ui) {
										   		    	tester_option_preview(nom_fonction,'Compression_x'); 
										   		    	tester_option_preview(nom_fonction,'Compression_y');
										   		    }
											  });
							});
						});
					}
					else
						form_userfriendly.find('.position_texte').addClass('cache');
				}
			});
			form_userfriendly.find('.accordion').accordion().change();
			load_myfonts_preview(true,true,true);
			
		break;
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

function tester(callback) {
	var dialogue=$('.wizard.preview_etape.modif').closest('.ui-dialog');

	var form_options=dialogue.find('[name="form_options"]');
	chargements=new Array();
	chargements.push(num_etape_courante+'');
	chargement_courant=0;
	var parametrage=form_options.serialize();
    charger_preview_etape(chargements[0],true,parametrage,function() { // Test de l'étape finale
    	chargements=new Array();
		chargements.push('all'); // Etape finale
		
    	chargement_courant=0;
    	if (callback == undefined)
    		charger_preview_etape(chargements[0],true,num_etape_courante+"."+form_options.serialize());
    	else
    		charger_preview_etape(chargements[0],true,num_etape_courante+"."+form_options.serialize(),callback);
    		
    });
}

function valider() {
	/*$.ajax({
	    url: urls['update_wizard']+['index',pays,magazine,numero,ordre,parametrage].join('/'),
	    type: 'post',
	    dataType:'json',
	    success:function(data) {
	    	
	    }
	});*/
}

function tester_option_preview(nom_fonction,nom_option) {
	var dialogue=$('.wizard.preview_etape.modif').closest('.ui-dialog');
	var form_options=dialogue.find('[name="form_options"]');
	var form_userfriendly=dialogue.find('.options_etape');
	var nom_fonction=dialogue.data('nom_fonction');
	var image=dialogue.find('.image_preview');
	
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
					val = toFloat2Decimals(parseFloat((point_remplissage.offset().left - limites_drag_point_remplissage[0])/zoom));
				break;
				case 'Pos_y':
					var limites_drag_point_remplissage=point_remplissage.draggable('option','containment');
					val = toFloat2Decimals(parseFloat((point_remplissage.offset().top - limites_drag_point_remplissage[1])/zoom));
				break;
			}
		break;
		case 'TexteMyFonts':
			var positionnement=dialogue.find('.position_texte');
			switch(nom_option) {
				case 'Pos_x':
					val = toFloat2Decimals(parseFloat((positionnement.offset().left - image.offset().left)/zoom));
				break;
				case 'Pos_y':
					var pos_y_rectangle=positionnement.offset().top - image.offset().top;
					val = toFloat2Decimals(parseFloat((pos_y_rectangle)/zoom));
					form_options.find('[name="Mesure_depuis_haut"]').val('Oui');
				break;
				case 'Compression_x':
					val = toFloat2Decimals(positionnement.width()/image.width());
				break;
				case 'Compression_y':
					var compression_x=parseFloat(form_options.find('[name="Compression_x"]').val());
					
					var image_preview_ajustee=dialogue.find('.positionnement .apercu_myfonts img');
					var ratio_image_preview_ajustee=image_preview_ajustee.prop('width')/image_preview_ajustee.prop('height');
					var ratio_positionnement=positionnement.width()/positionnement.height();
					val = toFloat2Decimals(compression_x*(ratio_image_preview_ajustee/ratio_positionnement));
				break;
				case 'Couleur_fond': case 'Couleur_texte':
					val=farbs[nom_option].color.replace(/#/g,'');
				break;
				case 'Chaine': case 'URL':
					val=form_userfriendly.find('input[name="option-'+nom_option+'"]').val();
				break;
				case 'Largeur':
					var largeur_courante=form_options.find('[name="Largeur"]').val();
					var largeur_physique_preview=dialogue.find('div.extension_largeur').offset().left
												-dialogue.find('.finition_texte_genere .apercu_myfonts img').offset().left;
					val=parseFloat(largeur_courante)* (largeur_physique_preview/largeur_physique_preview_initiale);
				break;
				case 'Demi_hauteur':
					val=form_userfriendly.find('input[name="option-'+nom_option+'"]').prop('checked') ? 'Oui' : 'Non';					
				break;
				case 'Rotation':
					val=-1*radToDeg(form_userfriendly.find('input[name="option-'+nom_option+'"]').data('currentRotation'));
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
	if (nom_fonction=='TexteMyFonts') {
		load_myfonts_preview(true,true,true);
	}
}

function load_myfonts_preview(preview1, preview2, preview3, callback) {
	var dialogue=$('.wizard.preview_etape.modif').closest('.ui-dialog');
	var form_options=dialogue.find('[name="form_options"]');
	var selectors=[];
	if (preview1)
		selectors.push('.proprietes_texte .apercu_myfonts');
	if (preview2)
		selectors.push('.finition_texte_genere .apercu_myfonts');
	if (preview3)
		selectors.push('.positionnement .apercu_myfonts');
	var apercus=$(selectors.join(','));
	var images=apercus.find('img');
	if (images.length == 0) {
		apercus.html($('<img>'));
		images=apercus.find('img');
	}
	images.addClass('loading');
	
	$.each(images,function() {
		var url_appel=urls['viewer_myfonts']+"index";
		$.each($(['URL','Couleur_texte','Couleur_fond','Largeur','Chaine','Demi_hauteur']),function(i,nom_option) {
			url_appel+="/"+form_options.find('[name="'+nom_option+'"]').val();
		});
		if ($(this).closest('.positionnement').length != 0)
			url_appel+="/"+form_options.find('[name="Rotation"]').val();
		else
			url_appel+='/null';

		$(this).attr({'src':url_appel});	
		$(this).load(function() {
			$(this).removeClass('loading');
			if ($(this).closest('.finition_texte_genere').length > 0) {
				var section_active_integration=$(this).closest('.ui-accordion-content-active').length > 0;
				if (section_active_integration)
					placer_extension_largeur_preview();
			}
			if (callback != undefined)
				callback();
		});
	});
}

var largeur_physique_preview_initiale=null;

function placer_extension_largeur_preview() {
	var dialogue=$('.wizard.preview_etape.modif').closest('.ui-dialog');
	var image_integration=dialogue.find('.finition_texte_genere img');
	largeur_physique_preview_initiale=image_integration.width();
	
	dialogue.find('.finition_texte_genere .extension_largeur').removeClass('cache')
			.css({'margin-left':(image_integration.width())+'px',
				  'height':image_integration.height()+'px',
				  'left':''})
			.draggable({
				axis: 'x',
				stop:function(event,ui) {
					tester_option_preview('TexteMyFonts','Largeur');
					load_myfonts_preview(false,true,false);	
				}
			});
}

function wizard_charger_liste_pays() {
	var wizard_pays=$('#'+id_wizard_courant+' [name="wizard_pays"]');
	$.ajax({
		url: urls['numerosdispos']+['index'].join('/'),
		dataType:'json',
		type: 'post',
		success:function(data) {
			wizard_pays.html('');
			for (var i in data.pays) {
				wizard_pays.append($('<option>')
						   .val(i)
						   .html(data.pays[i]));
			}
			wizard_pays.val(get_option_wizard('wizard_pays') || 'fr');

			wizard_charger_liste_magazines(pays_sel);
		}
	});
}


function wizard_charger_liste_magazines(pays_sel) {
	pays=pays_sel;
	var wizard_magazine=$('#'+id_wizard_courant+' [name="wizard_magazine"]');
	$.ajax({
		url: urls['numerosdispos']+['index',pays].join('/'),
		type:'post',
		dataType: 'json',
		success:function(data) {
			wizard_magazine.html('');
			for (var i in data.magazines) {
				wizard_magazine.append($('<option>')
							   .val(i)
							   .html(data.magazines[i]));
			}
			if (get_option_wizard('wizard_magazine') != undefined)
				wizard_magazine.val(get_option_wizard('wizard_magazine'));
			wizard_charger_liste_numeros(wizard_magazine.val());
		}
	});
}

function wizard_charger_liste_numeros(magazine_sel) {
	magazine=magazine_sel;
	var wizard_numero=$('#'+id_wizard_courant+' [name="wizard_numero"]');
	$.ajax({
		url: urls['numerosdispos']+['index',pays,magazine].join('/'),
		type: 'post',
		dataType: 'json',
		success:function(data) {
			numeros_dispos=data.numeros_dispos;
			var tranches_pretes=data.tranches_pretes;

			wizard_numero.html('');
			for (var numero_dispo in numeros_dispos) {
				if (numero_dispo != 'Aucun') {
					var option=$('<option>').val(numero_dispo).html(numero_dispo);
					var est_dispo=typeof(tranches_pretes[numero_dispo]) != 'undefined';
					if (est_dispo) {
						option.addClass(tranches_pretes[numero_dispo] == 'par_moi'
										 ? 'cree_par_moi'
										 : 'tranche_prete');
					}
					wizard_numero.append(option);
				}
			}
			if (get_option_wizard('wizard_numero') != undefined)
				wizard_numero.val(get_option_wizard('wizard_numero'));
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


function toFloat2Decimals(floatVal) {
	return new String(floatVal).replace(/([0-9]+)(\.[0-9]{0,2})?.*/g,'$1$2');
}

function init_action_bar() {
	$.each($('#action_bar img.action'), function() {
		var nom=$(this).attr('name');
		$(this).attr({'src':'../images/'+nom+'.png'});
		
		$(this).click(function() {
			var nom=$(this).attr('name');
			switch(nom) {
				case 'home':
					location.reload();
				break;
			}
			
		});
	});
}