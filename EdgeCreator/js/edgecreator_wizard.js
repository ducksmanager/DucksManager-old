(function($,undefined){
	  $.fn.d = function(){
		  return this.closest('.ui-dialog');
	  };

	  $.fn.valeur = function(nom_option){
		 if (this.hasClass('options_etape'))
			 return this.find('[name="option-'+nom_option+'"]');
		 else
			 return this.find('[name="'+nom_option+'"]');
	  };
})(jQuery);

var wizard_options={};
var id_wizard_courant=null;
var id_wizard_precedent=null;
var num_etape_courante=null;

zoom=1.5;
var url_viewer='viewer_wizard';
var NB_MAX_TRANCHES_SIMILAIRES_PROPOSEES=10/2;
var LARGEUR_DIALOG_TRANCHE_FINALE=65;
var LARGEUR_INTER_ETAPES=25;
var SEPARATION_CONCEPTION_ETAPES=40;
var MARGE_DROITE_TRANCHE_FINALE=10;

var TEMPLATES ={'numero':/\[Numero\]/,
	            'numero[]':/\[Numero\[([0-9]+)\]\]/ig,
	            'largeur':/(?:([0-9.]+)(\*))?\[Largeur\](?:(\*)([0-9.]+))?/i,
	            'hauteur':/(?:([0-9.]+)(\*))?\[Hauteur\](?:(\*)([0-9.]+))?/i,
	            'caracteres_speciaux':/\Â°/i};

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
	
	switch(id) {
		case 'wizard-conception' :
			buttons["Soumettre la tranche"]=function() {
				chargements=['all'];
				
				numero_chargement=numero;
				chargement_courant=0;
	            charger_preview_etape(chargements[0],false);
			};
		break;
		case 'wizard-ajout-etape':
			$('#'+id).find('form input[name="etape"]').val($('#ajout_etape').data('etape'));
			$('#'+id).find('form input[name="pos"]').val($('#ajout_etape').data('pos'));
			buttons={
				'OK': function() {
					var formData=$(this).find('form').serializeObject();
					$.ajax({
						url: urls['insert_wizard']+['index',pays,magazine,numero,formData.pos,formData.etape,formData.nom_fonction].join('/'),
						type: 'post',
						success:function(data) {
							$('#wizard-ajout-etape').dialog( "close" );
							$('.dialog-preview-etape').remove();
							$('#wizard-conception').parent().remove();
							wizard_goto($(this),'wizard-conception');
						}
					});
				},
				'Annuler':function() {
					$( this ).dialog( "close" );
				}
			};
		break;
		default:
			if (! $('#'+id).hasClass('first')) {
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
		break;
	}
	$('#'+id).dialog({
		width: 475,
		position: 'top',
		modal: $('#'+id).hasClass('modal'),
		autoResize: true,
		resizable: $('#'+id).hasClass('resizable'),
		buttons: buttons,
		draggable: false,
		open:function(event,ui) {
			var dialog=$(this).d();
			if ($(this).hasClass('main'))
				dialog.addClass('main');
			
			$(this).css({'max-height':(
										$('#body').height()
									   -dialog.find('.ui-dialog-titlebar').height()
									   -dialog.find('.ui-dialog-buttonpane').height()*2
									   -dialog.css('top')
									 )+'px'});

			dialog.find(".ui-dialog-titlebar-close").hide();
			
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
				case 'wizard-dimensions':
					$.each($(['Dimension_x','Dimension_y']),function(i,nom_champ) {
						var valeur= $('#'+wizard_id+' [name="'+nom_champ+'"]').val();
						var bornes_valeur=nom_champ == 'Dimension_x' ? [3, 60] : [100, 450];
						if ( valeur == ''
						  || valeur.search(/^[0-9]+$/) != 0) {
							erreur="Le champ "+nom_champ+" est vide ou n'est pas un nombre";
						}
						valeur=parseInt(valeur);
						if (valeur < bornes_valeur[0] || valeur > bornes_valeur[1]) {
							erreur="Le champ "+nom_champ+" doit &ecirc;tre compris entre "+bornes_valeur[0]+" et "+bornes_valeur[1];
						}
						
					});
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
							$('#'+wizard_id+' #tranches_en_cours').append(bouton_tranche_en_cours)
																  .append(label_tranche_en_cours)
																  .append($('<br>'));
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
				wizard_goto($('#'+wizard_id),'wizard-dimensions');
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
				if (get_option_wizard('wizard-creer','wizard_pays') != undefined) {
					pays=get_option_wizard('wizard-creer','wizard_pays');
					magazine=get_option_wizard('wizard-creer','wizard_magazine');
					numero=get_option_wizard('wizard-creer','wizard_numero');
					
					// Ajout des dimensions en base
					$.ajax({
						url: urls['insert_wizard']+['index',pays,magazine,numero,-1,'Dimensions'].join('/'),
					    type: 'post',
					    async: false
					});
					// Mise à jour avec les valeurs entrées
					var parametrage_dimensions =  'Dimension_x='+get_option_wizard('wizard-dimensions','Dimension_x')
												+'&Dimension_y='+get_option_wizard('wizard-dimensions','Dimension_y');
					$.ajax({
						url: urls['update_wizard']+['index',pays,magazine,numero,-1,parametrage_dimensions].join('/'),
					    type: 'post',
					    async: false
					});
				}
				else {
					numero_complet_userfriendly='';
				}
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
								reload_all_previews();
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
										$('#Dimension_x').val(texte);
									break;
									case 'Dimension_y':
										$('#Dimension_y').val(texte);
									break;
								}
							}
							$('#modifier_dimensions')
								.removeClass('cache')
								.button()
								.click(function(event) {
							    	event.preventDefault();
									verifier_changements_etapes_sauves($('.modif').d(),'wizard-confirmation-rechargement',function() {
										var form_options=$(event.currentTarget).d().find('[name="form_options"]');
										var parametrage=form_options.serialize();
										
										$.ajax({
										    url: urls['update_wizard']+['index',pays,magazine,numero,-1,parametrage].join('/'),
										    type: 'post',
										    success:function(data) {
										    	reload_all_previews();
										    }
										});
									});
								});
							
							$('#wizard-conception .chargement').addClass('cache');
							$('#wizard-conception form').removeClass('cache');
							
							chargements=[];
							for (var i=0;i<etapes_valides.length;i++) {
								var etape=etapes_valides[i];
								var num_etape=etape.Ordre;
								if (num_etape != -1) {
									var nom_fonction=etapes_valides[i].Nom_fonction;
									
									var wizard_etape = $('.wizard.preview_etape.initial').clone(true);
									var div_preview=$('<div>').data('etape',num_etape+'').addClass('image_etape');
									var div_preview_vide=$('<div>')
										.addClass('preview_vide cache')
										.css({'width' :$('#Dimension_x').val()*zoom+'px', 
											  'height':$('#Dimension_y').val()*zoom+'px'});
									wizard_etape.append(div_preview)
												.append(div_preview_vide);
									
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
											$(this).d().addClass('dialog-preview-etape')
																		 .data('etape',num_etape)
																		 .data('nom_fonction',nom_fonction);
											$(this).d().find(".ui-dialog-titlebar-close").hide();
											$(this).d().find('.ui-dialog-titlebar').css({'padding':'.3em .6em;'})
																		 .html($('<img>',{'height':18,'src':base_url+'images/fonctions/'+nom_fonction+'.png',
				  			   	   											    		  'alt':nom_fonction}))
				  			   	   										 .append($('<span>').addClass('cache ui-dialog-title').html(nom_fonction))
				  			   	   										 .addClass('logo_option');
										}
									});
									wizard_etape.d().bind('resize',function() {
										placer_dialogues_preview();
									});
									chargements.push(num_etape+'');
								}
							}
							
							var wizard_etape_finale = $('.wizard.preview_etape.initial').clone(true);
							var div_preview=$('<div>').data('etape','final').addClass('image_etape finale');
							wizard_etape_finale.html(div_preview).append($('<span>',{'id':'photo_tranche'}).css({'margin-left':'10px'}));
							
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
									$(this).d().addClass('dialog-preview-etape finale')
																 .data('etape','finale');
									$(this).d().find(".ui-dialog-titlebar-close").hide();
									$(this).d().find('.ui-dialog-titlebar').css({'padding':'.3em .6em;','text-align':'center'})
																				.html('Tranche<br />finale');
								}
							});

							
							chargements.push('all'); // On ajoute l'étape finale
							
							numero_chargement=numero;
							chargement_courant=0;
				            charger_preview_etape(chargements[0],true,'_',function() {
								if (etapes_valides.length == 1) { // Dimensions seulement
									placer_dialogues_preview();
								}
				            });
				            
				            $('#zone_ajout_etape').hover(indiquer_ajout_etape,
				            					  		 effacer_ajout_etape);
				            
				            $('#ajout_etape').click(function() {
			                	launch_wizard('wizard-ajout-etape');
				            });
				            
							$('.wizard.preview_etape:not(.final)').click(function() {
								var dialogue=$(this).d();
								if (modification_etape != null) {
									if (dialogue.data('etape') == modification_etape.data('etape'))
										return;
									else {
										verifier_changements_etapes_sauves(modification_etape,'wizard-confirmation-annulation', function() { ouvrir_dialogue_preview(dialogue);});
										return;
									}
								}
								else {
									if (dialogue.find('.image_preview').length == 0) {
										return;
									}
								}
								ouvrir_dialogue_preview(dialogue);
							});
						}
					});
					
				}
			});
		break;
		case 'wizard-ajout-etape':
        	$.ajax({
                url: urls['listerg']+['index','Fonctions'].join('/'),
                dataType:'json',
                type: 'post',
                success:function(data) {
                	var select=$('<select>',{'name':'nom_fonction'});
                	for (var i in data) {
                		select.append($('<option>',{'value':i}).html(data[i]));
                	}
                	$('#liste_fonctions').html(select);
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

function ouvrir_dialogue_preview(dialogue) {
	modification_etape=dialogue;
	
	var num_etape=dialogue.data('etape');
	num_etape_courante=num_etape;
	var nom_fonction=dialogue.data('nom_fonction');
	
	var section_preview_etape=dialogue.find('.preview_etape');
	section_preview_etape.addClass('modif');
	dialogue.addClass('modif');

	section_preview_etape.find('img,.preview_vide').toggleClass('cache');
	
	var section_preview_vide=dialogue.find('.preview_vide');
	var largeur_tranche=section_preview_vide.width();
	section_preview_etape.dialog('option', 'width', largeur_max_preview_etape_ouverte());
	dialogue.find('.ui-dialog-titlebar').find('span').removeClass('cache');
	section_preview_vide.after($('#options-etape--'+nom_fonction)
						.removeClass('cache')
						.css({'margin-left':(section_preview_vide.position().left+largeur_tranche+5*zoom)+'px'}));

	section_preview_etape.dialog('option','buttons',{
		'Fermer': function() {
			verifier_changements_etapes_sauves($(this).d(),'wizard-confirmation-annulation');
		},
		'Tester': function() {
			tester();
		},
		'Valider': function() {
			valider();		
		}
	});
	section_preview_etape.find('button').button();
	recuperer_et_alimenter_options_preview(num_etape);
}

function fermer_dialogue_preview(dialogue) {
	dialogue.removeClass('modif')
			.css({'width':'auto'});
	dialogue.dialog('option', 'width', 'auto');
	dialogue.find('.ui-dialog-buttonpane').remove();
	dialogue.find('.ui-dialog-titlebar').find('span').addClass('cache');
	dialogue.find('.options_etape').addClass('cache');
	dialogue.find('.image_etape img, .preview_vide').toggleClass('cache');
	dialogue.find('[name="form_options"],[name="form_options_orig"]').remove();
	dialogue.find('.preview_etape').removeClass('modif');
	modification_etape=null;
}

function placer_dialogues_preview() {
	var dialogues=$('.dialog-preview-etape').add($('#wizard-conception').d());
	dialogues.sort(function(dialogue1,dialogue2) { // Triés par offset gauche, de droite à gauche
		return $(dialogue2).offset().left - $(dialogue1).offset().left;
	});
	var min_marge_gauche=0;
	$.each(dialogues,function(i,dialogue) {
		var largeur=$(dialogue).width();
		if (i == 0) {
			$(dialogue).css({'left':$(window).width()-largeur-MARGE_DROITE_TRANCHE_FINALE});			
		}
		else {
			var dialogue_suivant=$(dialogues[i-1]);
			var marge_gauche=dialogue_suivant.offset().left-largeur
							-(i==2 ? SEPARATION_CONCEPTION_ETAPES : LARGEUR_INTER_ETAPES);
			$(dialogue).css({'left':marge_gauche+'px'});
			min_marge_gauche=Math.min(marge_gauche,min_marge_gauche);
		}
	});
	
	if (min_marge_gauche < 0) {
		$.each(dialogues,function(i,dialogue) {
			$(dialogue).css({'left':parseInt($(dialogue).css('left'))-min_marge_gauche+'px'});			
		});
	}
	
	// Positionnement de la zone d'ajout d'étape
	var dialogue_droite=$(dialogues[1]); // Avant le dialogue de conception et la tranche finale
	var dialogue_gauche=$(dialogues[dialogues.size()-1]); // Première étape
	$('#zone_ajout_etape').css({'left':	 dialogue_gauche.offset().left - LARGEUR_INTER_ETAPES,
							    'top': 	 dialogue_gauche.offset().top,
							    'height': dialogue_gauche.height()});
	$('#zone_ajout_etape').css({'width':dialogue_droite.offset().left - $('#zone_ajout_etape').offset().left});
}

function indiquer_ajout_etape(e) {
	var ajout_etape = $('#ajout_etape');
	if (! ajout_etape.hasClass('cache'))
		return;
	var dialogues=$('.dialog-preview-etape:not(.finale)').add($('#wizard-conception').d());
	dialogues.sort(function(dialogue1,dialogue2) { // Triés par offset gauche, de droite à gauche
		return $(dialogue2).offset().left - $(dialogue1).offset().left;
	});
	var nearestLeftDialog=null,
		nearestRightDialog=null;
	var minDistanceToLeftEdge=null,
		minDistanceToRightEdge=null;
	$.each(dialogues,function(i,dialogue) {
		var currentDistanceToLeftEdge = $(dialogue).offset().left - e.pageX;
		if (currentDistanceToLeftEdge > 0 && (minDistanceToLeftEdge === null || currentDistanceToLeftEdge < minDistanceToLeftEdge )) {
			minDistanceToLeftEdge = currentDistanceToLeftEdge;
			nearestRightDialog=$(dialogue);
		}
		var currentDistanceToRightEdge = e.pageX - ($(dialogue).offset().left + $(dialogue).width());
		if (currentDistanceToRightEdge > 0 && (minDistanceToRightEdge === null || currentDistanceToRightEdge < minDistanceToRightEdge )) {
			minDistanceToRightEdge = currentDistanceToRightEdge;
			nearestLeftDialog=$(dialogue);
		}
	});
	
	if (nearestLeftDialog != null) {
		var etape = parseInt(nearestLeftDialog.data('etape'));
		ajout_etape.removeClass('cache')
				   .css({'left':(nearestLeftDialog.offset().left+nearestLeftDialog.width()+8)+'px'})
				   .data('etape',etape)
				   .data('pos','apres');
	}
	else if (nearestRightDialog != null) {
		var etape = parseInt(nearestRightDialog.data('etape')) || 0;
		ajout_etape.removeClass('cache')
				   .css({'left':(nearestRightDialog.offset().left-ajout_etape.width()-2)+'px'})
				   .data('etape',etape)
				   .data('pos','avant');
	}
    $('.tip2').tipTip({delay:0});
}

function effacer_ajout_etape() {
	$('#ajout_etape').addClass('cache');
}

function recuperer_et_alimenter_options_preview(num_etape) {
	var section_preview_etape=$('.wizard.preview_etape').getElementsWithData('etape',num_etape);
	var nom_fonction=section_preview_etape.d().data('nom_fonction');
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
	if (section_preview_etape.find('[name="form_options"]').length == 0) {
		var form_options=$('<form>',{'name':'form_options'});
		for(var nom_option in valeurs) {
			form_options.append($('<input>',{'name':nom_option,'type':'hidden'}).val(templatedToVal(valeurs[nom_option])));
		}
		section_preview_etape.append(form_options)
							 .append(form_options.clone(true)
									 				.attr({'name':'form_options_orig'}));
	}
	
	var image = section_preview_etape.find('.preview_vide');
	
	var checkboxes=new Array();
	switch(nom_fonction) {
		case 'Agrafer':
			var agrafe1=form_userfriendly.find('.agrafe.premiere');
			var agrafe2=form_userfriendly.find('.agrafe.deuxieme');
			
			var pos_x_debut=image.position().left+image.width()/2-.25*zoom;
			var largeur=zoom;
			var pos_y_agrafe1=image.position().top +parseFloat(templatedToVal(valeurs['Y1']))*zoom;
			var pos_y_agrafe2=image.position().top +parseFloat(templatedToVal(valeurs['Y2']))*zoom;
			var hauteur= parseFloat(templatedToVal(valeurs['Taille_agrafe']))*zoom;
	
			agrafe1.css({'top':    pos_y_agrafe1+'px'});
			agrafe2.css({'top':    pos_y_agrafe2+'px'});
			$('.agrafe')
				.css({'left':   pos_x_debut	+'px', 
					  'width':  largeur	  	+'px',
					  'height': hauteur	  	+'px'})
			    .removeClass('cache')
			    .draggable({
			    	axis: 'y',
			    	stop:function(event, ui) {
			    		var element=$(event.target);
			    		if (element.hasClass('premiere')) {
			    			tester_option_preview(nom_fonction,'Y1',element);
			    		}
			    		else {
			    			tester_option_preview(nom_fonction,'Y2',element);
			    		}
			    	}
			    })
			    .resizable({
			    	handles:'s',
			    	resize:function(event, ui) {
			    		tester_option_preview(nom_fonction,'Taille_agrafe',ui.element);
			    	}
			    });
	
		break;
		case 'DegradeTrancheAgrafee':
			var agrafe1=form_userfriendly.find('.premiere.agrafe');
			var agrafe2=form_userfriendly.find('.deuxieme.agrafe');
			
			var pos_x_debut=image.position().left+image.width()/2-.25*zoom;
			var largeur=zoom;
			var pos_y_agrafe1=image.position().top +0.2*image.height();
			var pos_y_agrafe2=image.position().top +0.8*image.height();
			var hauteur= image.height()*0.05;
				
			agrafe1.css({'top':    pos_y_agrafe1+'px'});
			agrafe2.css({'top':    pos_y_agrafe2+'px'});
			form_userfriendly.find('.agrafe')
				.css({'left':   pos_x_debut	+'px', 
					  'width':  largeur	  	+'px',
					  'height': hauteur	  	+'px'})
			    .removeClass('cache');

			
			var coef_degrade=1.75;
			classes_farbs['Couleur']='';

			var rectangle1 = form_userfriendly.find('.premier.rectangle_degrade');
			var rectangle2 = form_userfriendly.find('.deuxieme.rectangle_degrade');
			
			var c1=valeurs['Couleur'];
			var c1_rgb=hex2rgb(c1);
			var c2=rgb2hex(parseInt(c1_rgb[0]/coef_degrade),
						   parseInt(c1_rgb[1]/coef_degrade),
						   parseInt(c1_rgb[2]/coef_degrade));

			rectangle1.css({'left':image.position().left+'px'});
			rectangle2.css({'left':parseInt(image.position().left+image.width()/2)+'px'});
			form_userfriendly.find('.rectangle_degrade')
				.css({'top':    image.position().top +'px', 
					  'width':  image.width()/2	  	 +'px',
					  'height': image.height()		 +'px'})
			    .removeClass('cache');
			coloriser_rectangle_degrade(form_userfriendly.find('.premier.rectangle_degrade'), c2, c1);
			coloriser_rectangle_degrade(form_userfriendly.find('.deuxieme.rectangle_degrade'), c1, c2);
	
		break;
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
		case 'Rectangle':
			classes_farbs['Couleur']='';

			var position_texte=form_userfriendly.find('.rectangle_position');

			var pos_x_debut=image.position().left+parseFloat(templatedToVal(valeurs['Pos_x_debut']))*zoom;
			var pos_y_debut=image.position().top +parseFloat(templatedToVal(valeurs['Pos_y_debut']))*zoom;
			var pos_x_fin=image.position().left+parseFloat(templatedToVal(valeurs['Pos_x_fin']))*zoom;
			var pos_y_fin=image.position().top +parseFloat(templatedToVal(valeurs['Pos_y_fin']))*zoom;

			position_texte.css({	'left':			    pos_x_debut+'px', 
								    'top': 			    pos_y_debut+'px',
								    'width': (pos_x_fin-pos_x_debut)+'px',
								    'height':(pos_y_fin-pos_y_debut)+'px'})
						  .removeClass('cache')
						  .draggable({//containment:limites_drag, 
				  		  stop:function(event, ui) {
			   		    	tester_option_preview(nom_fonction,'Pos_x_debut'); 
			   		    	tester_option_preview(nom_fonction,'Pos_y_debut');
			   		    	tester_option_preview(nom_fonction,'Pos_x_fin'); 
			   		    	tester_option_preview(nom_fonction,'Pos_y_fin');
			   		      }
						  })
						  .resizable({
								stop:function(event, ui) {
					   		    	tester_option_preview(nom_fonction,'Pos_x_fin'); 
					   		    	tester_option_preview(nom_fonction,'Pos_y_fin');
					   		    }
						  });
			
			checkboxes.push('Rempli');
			form_userfriendly.valeur('Rempli')
							 .val(valeurs['Rempli'] == 'Oui')
							 .change(function() {
								 var nom_option=$(this).attr('name').replace(/option\-([A-Za-z0-9]+)/g,'$1');
								 tester_option_preview(nom_fonction,nom_option);
								 coloriser_rectangle_preview(valeurs['Couleur'],$(this).prop('checked'));
							 });
			
			coloriser_rectangle_preview(valeurs['Couleur'],valeurs['Rempli'] == 'Oui');

		break;
		case 'Image':
			
			$.each($(['Source']),function(i,option_nom) {
				form_userfriendly.valeur(option_nom).val(valeurs[option_nom]);				
			});
		
			var apercu_image=form_userfriendly.find('.apercu_image');
						
			if (apercu_image.attr('src') == undefined)
				definir_et_positionner_image(valeurs['Source']);
			else
				positionner_image(apercu_image);

			form_userfriendly.find('[name="parcourir"],[name="option-Source"]').click(function(event) {
				$('#wizard-gallery').dialog({
					width: 475,
					modal: true,
					height: $('#body').height(),
					draggable: true,
					buttons: {
						'Annuler':function() {
							$(this).dialog( "close" );
						},
						'Valider':function() {
							if ($(this).find('.gallery img.selected').length == 0) {
								jqueryui_alert('Vous n\'avez s&eacute;lectionn&eacute; aucune image. Cliquez sur l\'une d\'elles ou cliquez sur le bouton "Annuler"',
											   'Aucune image s&eacute;lectionn&eacute;e');
							}
							else {
				   		    	tester_option_preview('Image','Source'); 
								$(this).dialog( "close" );
							}
						}						
					},
					open:function(event,ui) {
						$.ajax({
			                url: urls['listerg']+['index','Source',pays,magazine].join('/'),
			                dataType:'json',
			                type: 'post',
			                success:function(data) {
			                	var ul=$('#wizard-gallery').find('ul.gallery');
			                	ul.find('li:not(.template)').remove();
			                	for (var i in data) {
			                		var li=ul.find('li.template').clone(true).removeClass('template');
			                		li.find('em').html(data[i].replace(/[^\.]+\./g,''));
			                		li.find('img').prop({'src':base_url+'../edges/'+pays+'/elements/'+data[i],
			                							 'title':data[i]});
			                		ul.append(li);
			                	}
			                	$('#wizard-gallery').find('ul.gallery li img').click(function() {
			                		$('#wizard-gallery').find('ul.gallery li img').removeClass('selected');
			                		$(this).addClass('selected');
			                	});
			                	$('#wizard-gallery').find('ul.gallery li img[src$="/'+form_userfriendly.valeur('Source').val()+'"]').click();
			                	ul.removeClass('cache');
			                	$('#wizard-gallery').find('.chargement_images').addClass('cache');
			                }
						});
					}
				});
				event.preventDefault();
			});

		break;
		case 'TexteMyFonts':
			classes_farbs['Couleur_texte']='.texte';
			classes_farbs['Couleur_fond']='.fond';
			
			$.each($(['Chaine','URL','Largeur']),function(i,option_nom) {
				form_userfriendly.valeur(option_nom).val(valeurs[option_nom]);				
			});
			
			form_userfriendly.find('input[name="option-Chaine"],input[name="option-URL"],input[name="option-Largeur"]').blur(function() {
				var nom_option=$(this).attr('name').replace(/option\-([A-Za-z0-9]+)/g,'$1');
				tester_option_preview(nom_fonction,nom_option);
				load_myfonts_preview(true,true,true);
			});
			
			checkboxes.push('Demi_hauteur');
			form_userfriendly.valeur('Demi_hauteur')
								.change(function() {
									var nom_option=$(this).attr('name').replace(/option\-([A-Za-z0-9]+)/g,'$1');
									tester_option_preview(nom_fonction,nom_option);
									load_myfonts_preview(true,true,true);
								});

			$(document).mouseup( stopRotate );
			var input_rotation=form_userfriendly.valeur('Rotation');
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
						$(this).find('.ui-accordion-content-active .chargement').toggleClass('cache');
						tester(function() {
							load_myfonts_preview(false,false,true,function() {
								form_userfriendly.d().find('.ui-accordion-content-active .chargement').toggleClass('cache');
								
								var dialogue=form_userfriendly.d();
								var valeurs=dialogue.find('[name="form_options"]').serializeObject();
								var image=dialogue.find('.preview_vide');
								
								var position_texte=form_userfriendly.find('.image_position');
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
											  })
											  .html(image_preview_ajustee.clone(false));
							});
						});
					}
					else
						form_userfriendly.find('.rectangle_position').addClass('cache');
				}
			});
			form_userfriendly.find('.accordion').accordion().change();
			load_myfonts_preview(true,true,true);
			
		break;
	}
	
	for (var nom_option in classes_farbs) {
		var input=form_userfriendly.valeur(nom_option);
		ajouter_farb(section_preview_etape.find('.picker'+classes_farbs[nom_option]), 
					 input, nom_fonction, nom_option, '#'+valeurs[nom_option]);
		input.click(function() {
			var this_picker=$(this).prevAll('.picker');
			$('.picker').not(this_picker).addClass('cache');
			this_picker.toggleClass('cache');
		});
	}
	
	for (var i in checkboxes) {
		form_userfriendly.valeur(checkboxes[i])
						 .attr('checked',valeurs[checkboxes[i]]=='Oui');
	}
}

function positionner_image(preview) {
	var form_userfriendly=modification_etape.find('.options_etape');
	var position_image=form_userfriendly.find('.image_position');	
	var dialogue=preview.d();
	var valeurs=dialogue.find('[name="form_options"]').serializeObject();
	var image=dialogue.find('.preview_vide');
	
	var ratio_image=preview.prop('width')/preview.prop('height');
	
	var largeur=toFloat2Decimals(image.width() * parseFloat(valeurs['Compression_x']));
	var hauteur=toFloat2Decimals(image.width() * parseFloat(valeurs['Compression_y']) / ratio_image);
	
	var pos_x=image.position().left+parseFloat(valeurs['Decalage_x'])*zoom;
	var pos_y;
	if (valeurs['Position'] == 'bas') {
		pos_y=image.position().top + image.height() - hauteur - parseFloat(valeurs['Decalage_y'])*zoom;
	}
	else {
		pos_y=image.position().top +parseFloat(valeurs['Decalage_y'])*zoom;
		if (valeurs['Mesure_depuis_haut'] == 'Non') { // Le pos_y est mesuré entre le haut de la tranche et le bas du texte
			pos_y-=parseFloat(hauteur);
		}
	}
	
	var limites_drag=[(image.offset().left-parseFloat(largeur)),
	                  (image.offset().top -parseFloat(hauteur)),
	                  (image.offset().left+image.width()),
	                  (image.offset().top +image.height())];
	
	position_image.addClass('outlined')
				  .css({'outline-color':'#000000',
					    'background-image':'',
					    'background-color':'white',
					    'left':pos_x+'px', 
						'top': pos_y+'px',
						'width':largeur+'px',
						'height':hauteur+'px'})
				  .removeClass('cache')
				  .html($('<img>',{'src':preview.attr('src')}))
				  .draggable({//containment:limites_drag, 
			  		  stop:function(event, ui) {
		   		    	tester_option_preview('Image','Decalage_x'); 
		   		    	tester_option_preview('Image','Decalage_y');
		   		      }
				  })
				  .resizable('destroy')
				  .resizable({
						stop:function(event, ui) {
			   		    	tester_option_preview('Image','Compression_x'); 
			   		    	tester_option_preview('Image','Compression_y');
			   		    }
				  });
}

function definir_et_positionner_image(source) {
	var form_userfriendly=modification_etape.find('.options_etape');
	var apercu_image=form_userfriendly.find('.apercu_image');
	apercu_image
		.attr({'src':base_url+'../edges/'+pays+'/elements/'+source})
		.load(function() {
			positionner_image($(this));
		})
		.error(function() {
		});
}
function coloriser_rectangle_preview(couleur,est_rempli) {
	var position_texte=$('.modif .rectangle_position');
	if (est_rempli) {
		position_texte.css({'background-image': '-webkit-repeating-linear-gradient(135deg, white, white 5px, '
																				 +couleur+' 5px, '+couleur+' 10px)',})
					  .removeClass('outlined');
	}
	else {
		position_texte.addClass('outlined')
					  .css({'outline-color':couleur,'background-image':'','background-color':'white'});
	}
}

function coloriser_rectangle_degrade(element,couleur1,couleur2) {
	element.css({'background': '-webkit-gradient(linear, left top, right top, from(#'+couleur1+'), to(#'+couleur2+'))'});
}

function ajouter_farb(picker, input, nom_fonction, nom_option, valeur) {
	farbs[nom_option]=$.farbtastic(picker)
					  .linkTo(function() {callback_change_picked_color($(this),input);},
							  function() {callback_test_picked_color  ($(this),input,nom_fonction,nom_option);})
					  .setColor(valeur);
	
}

function verifier_changements_etapes_sauves(dialogue, id_dialogue_proposition_sauvegarde, callback) {
	callback=callback || function() {};
	if (dialogue.find('[name="form_options"]').serialize() 
	 != dialogue.find('[name="form_options_orig"]').serialize()) {
		$("#"+id_dialogue_proposition_sauvegarde).dialog({
			resizable: false,
			height:140,
			modal: true,
			buttons: {
				"Sauvegarder les changements": function() {
					fermer_dialogue_preview($('.modif'));
					$( this ).dialog( "close" );	
					valider();
					callback();										
				},
				"Fermer l'etape sans sauvegarder": function() {
					fermer_dialogue_preview($('.modif'));
					$( this ).dialog( "close" );
					callback();
				},
				"Revenir a l'edition d'etape": function() {
					$( this ).dialog( "close" );
					callback();
				}
			}
		});
	}
	else {
		fermer_dialogue_preview($('.modif'));
		$( this ).dialog( "close" );
		callback();
	}
}

function tester(callback, modif_dimensions) {
	var dialogue=$('.wizard.preview_etape.modif').d();

	var form_options=dialogue.find('[name="form_options"]');
	chargements=new Array();
	chargements.push((modif_dimensions ? -1 : num_etape_courante)+'');
	chargement_courant=0;
	var parametrage=form_options.serialize();
    charger_preview_etape(chargements[0],true,parametrage,function() { // Test de l'étape finale
    	chargements=['all']; // Etape finale
		
    	chargement_courant=0;
    	charger_preview_etape(chargements[0],true,num_etape_courante+"."+form_options.serialize(),callback);
    		
    });
}

function valider() {
	var form_options=$('.modif').find('[name="form_options"]');
	var parametrage=form_options.serialize();
	
	$.ajax({
	    url: urls['update_wizard']+['index',pays,magazine,numero,num_etape_courante,parametrage].join('/'),
	    type: 'post',
	    success:function(data) {
			reload_current_and_final_previews();
	    }
	});
}

function tester_option_preview(nom_fonction,nom_option,element) {
	var dialogue=$('.wizard.preview_etape.modif').d();
	var form_options=dialogue.find('[name="form_options"]');
	var form_userfriendly=dialogue.find('.options_etape');
	var nom_fonction=dialogue.data('nom_fonction');
	var image=dialogue.find('.preview_vide');
	
	var val=null;
	switch(nom_fonction) {
		case 'Agrafer':
			switch(nom_option) {
				case 'Taille_agrafe':
					form_userfriendly.find('.agrafe').not(element).height(element.height());
					val = element.height()/zoom;
				break;
				case 'Y1': case 'Y2':
					val = (element.offset().top-image.offset().top)/zoom;
				break;
			}
		break;
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
		case 'Rectangle':
			var positionnement=dialogue.find('.rectangle_position');
			switch(nom_option) {
				case 'Couleur':
					var farb=farbs[nom_option];
					val=farb.color.replace(/#/g,'');
				break;
				case 'Pos_x_debut':
					val = toFloat2Decimals(parseFloat((positionnement.offset().left - image.offset().left)/zoom));
				break;
				case 'Pos_y_debut':
					val = toFloat2Decimals(parseFloat((positionnement.offset().top  - image.offset().top )/zoom));
				break;
				case 'Pos_x_fin':
					val = toFloat2Decimals(parseFloat((positionnement.offset().left + positionnement.width() - image.offset().left)/zoom));
				break;
				case 'Pos_y_fin':
					val = toFloat2Decimals(parseFloat((positionnement.offset().top  + positionnement.height()- image.offset().top )/zoom));
				break;
				case 'Rempli':
					val=form_userfriendly.valeur(nom_option).prop('checked') ? 'Oui' : 'Non';					
				break;
			}
		break;
		case 'Image':
			var positionnement=dialogue.find('.image_position');
			switch(nom_option) {
				case 'Decalage_x':
					val = toFloat2Decimals(parseFloat((positionnement.offset().left - image.offset().left)/zoom));
				break;
				case 'Decalage_y':
					var pos_y_image=positionnement.offset().top - image.offset().top;
					val = toFloat2Decimals(parseFloat((pos_y_image)/zoom));
					form_options.valeur('Mesure_depuis_haut').val('Oui');
				break;
				case 'Compression_x':
					val = toFloat2Decimals(positionnement.width()/image.width());
				break;
				case 'Compression_y':
					var compression_x=parseFloat(form_options.valeur('Compression_x').val());
					
					var image_preview=dialogue.find('.apercu_image');
					var ratio_image=image_preview.prop('width')/image_preview.prop('height');
					var ratio_positionnement=positionnement.width()/positionnement.height();
					val = toFloat2Decimals(compression_x*(ratio_image/ratio_positionnement));
				break;
				case 'Source':
					val=$('.gallery img.selected').attr('src').replace(/.*\/([^\/]+)/,'$1');
					form_userfriendly.valeur(nom_option).val(val);
					
					definir_et_positionner_image(val);
			}
		break;
		case 'TexteMyFonts':
			var positionnement=dialogue.find('.image_position');
			switch(nom_option) {
				case 'Pos_x':
					val = toFloat2Decimals(parseFloat((positionnement.offset().left - image.offset().left)/zoom));
				break;
				case 'Pos_y':
					var pos_y_rectangle=positionnement.offset().top - image.offset().top;
					val = toFloat2Decimals(parseFloat((pos_y_rectangle)/zoom));
					form_options.valeur('Mesure_depuis_haut').val('Oui');
				break;
				case 'Compression_x':
					val = toFloat2Decimals(positionnement.width()/image.width());
				break;
				case 'Compression_y':
					var compression_x=parseFloat(form_options.valeur('Compression_x').val());
					
					var image_preview_ajustee=dialogue.find('.positionnement .apercu_myfonts img');
					var ratio_image_preview_ajustee=image_preview_ajustee.prop('width')/image_preview_ajustee.prop('height');
					var ratio_positionnement=positionnement.width()/positionnement.height();
					val = toFloat2Decimals(compression_x*(ratio_image_preview_ajustee/ratio_positionnement));
				break;
				case 'Couleur_fond': case 'Couleur_texte':
					val=farbs[nom_option].color.replace(/#/g,'');
				break;
				case 'Chaine': case 'URL':
					val=form_userfriendly.valeur(nom_option).val();
				break;
				case 'Largeur':
					var largeur_courante=form_options.valeur('Largeur').val();
					var largeur_physique_preview=dialogue.find('div.extension_largeur').offset().left
												-dialogue.find('.finition_texte_genere .apercu_myfonts img').offset().left;
					val=parseFloat(largeur_courante)* (largeur_physique_preview/largeur_physique_preview_initiale);
				break;
				case 'Demi_hauteur':
					val=form_userfriendly.valeur(nom_option).prop('checked') ? 'Oui' : 'Non';					
				break;
				case 'Rotation':
					val=-1*radToDeg(form_userfriendly.valeur(nom_option).data('currentRotation'));
				break;
			}
		break;
	}
	form_options.valeur(nom_option).val(val);
}

function reload_current_and_final_previews(callback) {
	chargements=[modification_etape.data('etape')];
    charger_preview_etape(chargements[0],true, undefined, function() {
    	chargements=['all'];
        charger_preview_etape(chargements[0],true, undefined, callback);
    });
}

function reload_all_previews() {
	if (etapes_valides.length > 1) {
		selecteur_cellules_preview='.wizard.preview_etape div.image_etape';
    	chargements=new Array();
		for (var i=0;i<etapes_valides.length;i++) {
			if (etapes_valides[i].Ordre != -1)
				chargements.push(etapes_valides[i].Ordre);
		}
		chargements.push('all'); // Etape finale
		
		chargement_courant=0;
		charger_preview_etape(chargements[0],true,undefined /*<-- Parametrage */,function(image) {
			var dialogue=image.d();
			var num_etape=dialogue.data('etape');

			if (modification_etape != null) {
				if (dialogue.data('etape') == modification_etape.data('etape'))
					recuperer_et_alimenter_options_preview(num_etape);
			}
        });
	}
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
	switch (nom_fonction) {
		case 'TexteMyFonts':
			load_myfonts_preview(true,true,true);
		break;
		case 'Rectangle':
			coloriser_rectangle_preview(farb[0].color,
										input_couleur.d().find('[name="form_options"]').valeur('Rempli').val()=='Oui');
		break;
	}
}

function load_myfonts_preview(preview1, preview2, preview3, callback) {
	var dialogue=$('.wizard.preview_etape.modif').d();
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
			url_appel+="/"+form_options.valeur(nom_option).val();
		});
		if ($(this).closest('.positionnement').length != 0)
			url_appel+="/"+form_options.valeur('Rotation').val();
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
	var dialogue=$('.wizard.preview_etape.modif').d();
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
				case 'photo':
					if ($(this).hasClass('active')) {
						$('#photo_tranche').html('');						
					}
					else {
						$.ajax({
			                url: urls['listerg']+['index','Source_photo',pays,magazine].join('/'),
			                dataType:'json',
			                type: 'post',
			                success:function(data) {
			                	var photo_trouvee=null;
			                	for (var i in data) {
			                		if (data[i].match(new RegExp('^'+magazine+'\.'+numero+'\.photo\.(png|jpg)$','g'))) {
			                			photo_trouvee=data[i];
			                		}
			                	}
			                	if (photo_trouvee) {
			    					$('#action_bar .action[name="photo"]').toggleClass('active');
									afficher_photo_tranche();
			                	}
			                	else {
			                		$.ajax({
										url: urls['upload_wizard']+['index',pays,magazine,numero].join('/'),
										type: 'post',
										dataType: 'json',
										success:function(data) {
											if (data === false) {
												$('#wizard-upload').dialog({
													modal: true,
													width: 330
												});
											}
											else {
												afficher_photo_tranche();
											}								
										}
									});
			                	}
			                }
						});
					}
				break;
			}
			
		});
	});
}

function afficher_photo_tranche() {
	$('#wizard-upload').css({'display':''});
	$('#photo_tranche').html($('<img>',{'src':base_url+'../edges/'+pays+'/photos/'+magazine+'.'+numero+'.jpg'})
							  .height(parseInt($('#dimension_y').val()) * zoom));
}

function templatedToVal(templatedString) {
	$.each(TEMPLATES,function(nom, regex) {
		var matches;
		if ((matches = (templatedString+'').match(regex)) != null) {
			templatedString+='';
			switch(nom) {
				case 'numero':
					templatedString=templatedString.replace(regex, numero);
				break;
				case 'numero[]':
					var spl=(numero+'').split('');
					var matches=templatedString.match(regex);
					for (var i=0;i<matches.length;i++) {
						var caractere=matches[i].replace(regex,'$1');
						if (!isNaN(caractere) && parseInt(caractere) >= 0 && parseInt(caractere) < spl.length)
							templatedString=templatedString.replace(matches[i],spl[parseInt(caractere)]);
					}
				break;
				case 'largeur':
					if (matches[2] || matches[3]) {
						var operation = matches[2] || matches[3];
						var autre_nombre= matches[1] || matches[4];
						switch(operation) {
							case '*':
								templatedString= $('#Dimension_x').val()*autre_nombre;
							break;
						}
					}
					else
						templatedString=templatedString.replace(regex, $('#Dimension_x').val());
				break;
				case 'hauteur':
					if (matches[2] || matches[3]) {
						var operation = matches[2] || matches[3];
						var autre_nombre= matches[1] || matches[4];
						switch(operation) {
							case '*':
								templatedString= $('#Dimension_y').val()*autre_nombre;
							break;
						}
					}
					else
						templatedString=templatedString.replace(regex, $('#Dimension_y').val());
				break;
				case 'caracteres_speciaux':
					templatedString=templatedString.replace(/Â°/,'°');
				break;

			}
		}
	});
	return templatedString;
}

function hex2rgb(hex) {
	if (hex.length != 6){
		return [0,0,0];
	}
	var rgb=[];
	for (var i=0;i<3;i++){
		rgb[i] = parseInt((hex.substring(2*i,2*(i+1))+'').replace(/[^a-f0-9]/gi, ''),16);
	}
	return rgb;
}

function rgb2hex(r, g, b) {
	var hex = "";
	var rgb = [r, g, b];
	for (var i = 0; i < 3; i++) {
		tmp = parseInt(rgb[i], 10).toString(16);
		if (tmp.length < 2)
			hex += "0" + tmp;
		else
			hex += tmp;
	}
	return hex.toUpperCase();
}