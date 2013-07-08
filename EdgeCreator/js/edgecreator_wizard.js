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

$(window).scroll(function(a,b) {
	if (modification_etape != null 
	 && modification_etape.find('#options-etape--Polygone').length != 0) {
		var options=modification_etape.find('[name="form_options"]');
		positionner_points_polygone(options);
	}
});

var INTERVAL_CHECK_LOGGED_IN=5;
(function check_logged_in() {
	$.ajax({
		url: urls['check_logged_in'],
		type: 'post',
		success:function(data) {
			if (data === '1') {
				setTimeout(check_logged_in, 1000*60*INTERVAL_CHECK_LOGGED_IN);
			}
			else {
				if ($('#wizard-conception').is(":visible")) {
					jqueryui_alert_from_d($('#wizard-session-expiree'),function() { location.reload(); });
				}
			}
		}
	});
	$.get('check_logged_in', function(data) {
	    $('.result').html(data);
	  });
})();


var wizard_options={};
var id_wizard_courant=null;
var id_wizard_precedent=null;
var num_etape_courante=null;
var nom_photo_principale=null;

var etape_ajout;
var etape_ajout_pos;

var pos_x_courante=null,
	pos_y_courante=null;

zoom=1.5;
var url_viewer='viewer_wizard';
var NB_MAX_TRANCHES_SIMILAIRES_PROPOSEES=10/2;
var LARGEUR_DIALOG_TRANCHE_FINALE=65;
var LARGEUR_INTER_ETAPES=40;
var SEPARATION_CONCEPTION_ETAPES=40;
var MARGE_DROITE_TRANCHE_FINALE=10;

var COTE_CARRE_DEPLACEMENT=10;

var PADDING_PARAMETRAGE_ETAPE=10;

var TEMPLATES ={'numero':/\[Numero\]/,
	            'numero[]':/\[Numero\[([0-9]+)\]\]/ig,
	            'largeur':/(?:([0-9.]+)(\*))?\[Largeur\](?:(\*)([0-9.]+))?/i,
	            'hauteur':/(?:([0-9.]+)(\*))?\[Hauteur\](?:(\*)([0-9.]+))?/i,
	            'caracteres_speciaux':/\Â°/i};

var REGEX_FICHIER_PHOTO=/\/([^\/]+\.([^.]+)\.photo_([^.]+?)\.[a-z]+)$/;
var REGEX_NUMERO=/tranche_([^_]+)_([^_]+)_([^_]+)/;
var REGEX_TO_WIZARD=/to\-(wizard\-[0-9]*)/g;
var REGEX_DO_IN_WIZARD=/do\-in\-wizard\-(.*)/g;

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

function launch_wizard(id, p) {
	id_wizard_courant=id;
	
	p = p || {}; // Paramètres de surcharge
	var buttons={},
		dialogue = $('#'+id),
		first 	 = dialogue.hasClass('first') 	 || (p.first 	 !== undefined	&& p.first),
		deadend = dialogue.hasClass('deadend') 	 || (p.deadend 	 !== undefined	&& p.deadend),
		modal	 = dialogue.hasClass('modal')	 || (p.modal 	 !== undefined	&& p.modal);
		closeable= dialogue.hasClass('closeable')|| (p.closeable !== undefined	&& p.closeable);
	
	$('#'+id+' .buttonset').buttonset();
	$('#'+id+' .button').button();
	$('#wizard-1 .buttonset .disabled').button("option", "disabled", true);

	if (!first) {
		buttons["Precedent"]=function() {
			$( this ).dialog().dialog( "close" );
			launch_wizard(id_wizard_precedent);
		};
	}
	
	switch(id) {
		case 'wizard-ajout-etape':
			dialogue.find('form input[name="etape"]').val(etape_ajout);
			dialogue.find('form input[name="pos"]').val(etape_ajout_pos);
			buttons={
				'OK': function() {
					var formData=$(this).find('form').serializeObject();
					$.ajax({
						url: urls['insert_wizard']+['index',pays,magazine,numero,formData.pos,formData.etape,formData.nom_fonction].join('/'),
						type: 'post',
						dataType:'json',
						success:function(data) {
							$('#wizard-ajout-etape').dialog().dialog( "close" );
							for (var i in data.infos_insertion.decalages) {
								$('*').getElementsWithData('etape',data.decalages[i]['old']).data('etape',data.decalages[i]['new']);
							}
							ajouter_preview_etape(data.infos_insertion.numero_etape, formData.nom_fonction);
							charger_previews(true);
						}
					});
				},
				'Annuler':function() {
					$( this ).dialog().dialog( "close" );
				}
			};
		break;
		case 'wizard-images':
			buttons["OK"]=function() {				
				var action_suivante=wizard_check($(this).attr('id'));
				if (action_suivante != null) {
					var type_gallerie = $('#'+id).hasClass('photo_principale') ? 'Photos' : 'Source';
					if (type_gallerie === 'Photos') {
						nom_photo_principale=$(this).find('.gallery li img.selected').attr('src')
							.match(REGEX_FICHIER_PHOTO)[1];
						if ($('#wizard-conception').is(':visible')) {
							maj_photo_principale();
							$( this ).dialog().dialog( "close" );
						}
						else {
							wizard_do($(this),action_suivante);
						}
					}
					else {
		   		    	tester_option_preview('Image','Source'); 
						$(this).dialog().dialog( "close" );
					}
				}
			};
		break;
		case 'wizard-confirmation-validation-modele-contributeurs':
			buttons["OK"]=function() {
				if (wizard_check(id)) {
				   	var form=$('#'+id+' form').serializeObject();
				   	var photographes=typeof(form.photographes) === "string" ? form.photographes : form.photographes.join(',') .replace(/ /g, "+");
				   	var designers=	 typeof(form.designers)    === "string" ? form.designers 	: form.designers.join(',') .replace(/ /g, "+");
					var nom_image=$('.image_etape.finale .image_preview').attr('src').match(/[.0-9]+$/g)[0];
					$.ajax({
		                url: urls['valider_modele']+['index',pays,magazine,numero,nom_image,designers,photographes].join('/'),
		                type: 'post',
		                success:function(data) {
		        			jqueryui_alert_from_d($('#wizard-confirmation-validation-modele-ok'), function() {
		        				location.reload();
		        			});
		                },
		                error:function(data) {
		                	jqueryui_alert("Une erreur est survenue pendant la validation de la tranche.<br />"
		                				  +"Contactez le webmaster", 
		                				  "Erreur");
		                }
					});
				}
			};
		break;
		default:
			if (!deadend) {
				buttons["Suivant"]=function() {
					var action_suivante=wizard_check($(this).attr('id'));
					if (action_suivante != null) {
						wizard_do($(this),action_suivante);
					}
				};
			}
		break;
	}
	dialogue.dialog({
		width: p.width || 475,
		position: 'top',
		modal: modal,
		autoResize: true,
		resizable: dialogue.hasClass('resizable'),
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

			if (!closeable) {
				dialog.find(".ui-dialog-titlebar-close").hide();
			}
			
			wizard_init($(this).attr('id'));
		},
		close: function(event,ui) {
			if (closeable) {
				var hasOnClose = $('#'+id+' form').serializeObject().onClose;
				if (hasOnClose) {
					wizard_goto($('#id'), $('#'+id+' form').serializeObject().onClose.replace(REGEX_TO_WIZARD,'$1'));
				}
			}
		}
	});
}

function wizard_goto(wizard_courant, id_wizard_suivant) {
	if (can_launch_wizard(id_wizard_suivant)) {
		wizard_options[wizard_courant.attr('id')]=wizard_courant.find('form').serializeObject();
		id_wizard_precedent=wizard_courant.attr('id');
		wizard_courant.dialog().dialog( "close" );
		launch_wizard(id_wizard_suivant);
	}
}

function wizard_do(wizard_courant, action) {
	if (action.indexOf("goto_") !== -1) {
		wizard_goto(wizard_courant,action.substring("goto_".length));
	}
	else {
		switch(wizard_courant.attr('id')) {
			case 'wizard-resize':
				switch(action) {
					case 'enregistrer':
						if (wizard_check('wizard-resize') !== undefined) {
							var image = wizard_courant.find('.jrac_viewport img');
							var decoupage_nom = image.attr('src').match(REGEX_FICHIER_PHOTO);
							if (!decoupage_nom) {
								jqueryui_alert("Le nom de l'image est invalide : " + image.attr('src'), "Nom invalide");
								return;
							}

							var numero_image = decoupage_nom[2];
							var nom = decoupage_nom[3];
							var x1 = parseInt(100 * ($('.jrac_crop').position().left / image.width())),
								x2 = parseInt(100 * ($('.jrac_crop').position().left + $('.jrac_crop').width()) / image.width()),
								y1 = parseInt(100 * ($('.jrac_crop').position().top  / image.height())),
								y2 = parseInt(100 * ($('.jrac_crop').position().top  + $('.jrac_crop').height()) / image.height());
							$.ajax({
								url: urls['rogner_image']+['index',pays,magazine,numero_image,numero,nom,x1,x2,y1,y2].join('/'),
								type: 'post',
								dataType:'json'
							});
						}
					break;
				}
			break;
		}
		wizard_courant.dialog().dialog("close");
		wizard_goto(wizard_courant,'wizard-images');
	}
}

function wizard_check(wizard_id) {
	var erreur=null;
	var choix = $('#'+wizard_id+' form [name="choix"]');
	var valeur_choix = $('#'+wizard_id+' form').serializeObject().choix;
	if (choix.length != 0 && valeur_choix === undefined) {
		erreur='Le formulaire n\'est pas correctement rempli';
	}
	else {
		var is_to_wizard = valeur_choix !== undefined && valeur_choix.match(REGEX_TO_WIZARD);
		var is_do_in_wizard = valeur_choix !== undefined && valeur_choix.match(REGEX_DO_IN_WIZARD);
		if (is_to_wizard || is_do_in_wizard) {
			switch(wizard_id) {
				case 'wizard-1':
					if (valeur_choix == 'to-wizard-conception'
					 && $('#'+wizard_id+' form').serializeObject().choix_tranche_en_cours == 0) {
						erreur='Si vous souhaitez poursuivre une cr&eacute;ation de tranche, cliquez dessus pour la s&eacute;lectionner.<br />'
							  +'Sinon, cliquez sur "Cr&eacute;er une tranche de magazine" ou "Modifier une tranche de magazine".';
					}
				break;
				case 'wizard-creer-collection':
					if (chargement_listes)
						erreur='Veuillez attendre que la liste des num&eacute;ros soit charg&eacute;e';
					else if ($('#'+wizard_id+' form').serializeObject().choix_tranche == 0) {
						erreur='Veuillez s&eacute;lectionner un num&eacute;ro.';
					}
				break;
				case 'wizard-creer-hors-collection':
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
						  && !$('#'+wizard_id+' [name="wizard_numero"]').find('option:selected').is('.tranche_prete, .cree_par_moi')) {
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
				
				case 'wizard-images':
					if ($('#'+wizard_id).find('form ul.gallery li img.selected').length == 0) {
						erreur='Veuillez s&eacute;lectionner une photo de tranche.';
					}
				break;
				
				case 'wizard-resize':
					if ($('#'+wizard_id).find('.error:not(.cache)').length > 0) {
						erreur='Veuillez corriger les erreurs avant de continuer.';
					}
				break;
				case 'wizard-confirmation-validation-modele-contributeurs':
					if (! $('#'+wizard_id+' form').serializeObject().photographes
					 || ! $('#'+wizard_id+' form').serializeObject().designers) {
						erreur='Au moins un photographe et un designer doivent &ecirc;tre sp&eacute;cifi&eacute;s.';						
					}
				break;
			}
		}
	}
	if (erreur != null) {
		jqueryui_alert(erreur);
	}
	else {
		if (valeur_choix === undefined) 
			return true;
		if (is_to_wizard)
			return 'goto_'+valeur_choix.replace(REGEX_TO_WIZARD,'$1');
		if (is_do_in_wizard)
			return valeur_choix.replace(REGEX_DO_IN_WIZARD,'$1');
	}
}

var chargement_listes=false;
modification_etape=null;
function wizard_init(wizard_id) {
	// Transfert vers un autre assistant
	$('#'+wizard_id+' button[value^="to-wizard-"]').click(function() {
		wizard_do($('#'+id_wizard_courant),'goto_'+$(this).val().replace(REGEX_TO_WIZARD,'$1'));
    	event.preventDefault();
	});
	
	// Action en restant dans l'assistant
	$('#'+wizard_id+' button[value^="do-in-wizard-"]').click(function() {
		var action = $(this).val().replace(REGEX_DO_IN_WIZARD,'$1');
		wizard_do($('#'+id_wizard_courant),action);
    	event.preventDefault();
	});
	
	// Actions à l'initialisation de l'assistant
	switch(wizard_id) {
		case 'wizard-1':
			$('#selectionner_tranche_en_cours')
		        .button({
		            text: false,
		            icons: {
		              primary: "ui-icon-triangle-1-s"
		            }
		          })
		          .click(function() {
		            $( this ).parent().next().show().position({
		              my: "right",
		              at: "right bottom",
		              of: this
		            });
		            return false;
		          })
		          .parent()
		            .buttonset()
		            .next()
		              .hide();
			$.ajax({
				url: urls['tranchesencours']+['load'].join('/'),
				dataType:'json',
				type: 'post',
				success:function(data) {
					afficher_liste_magazines(wizard_id,'tranches_en_cours',data);
				}
			});
			
		break;
		
		case 'wizard-creer-collection':
			chargement_listes=true;
			$.ajax({
				url: urls['numerosdispos']+['index','null','null','true'].join('/'),
				dataType:'json',
				type: 'post',
				success:function(data) {
					if (typeof(data.erreur) !='undefined')
						jqueryui_alert(data);
					else {
						afficher_liste_magazines(wizard_id, 'tranches_non_pretes', data.tranches_non_pretes);
					}
					chargement_listes=false;
				},
				error:function(data) {
					jqueryui_alert('Erreur : '+data);
				}
			});
		break;
		
		case 'wizard-creer-hors-collection': case 'wizard-modifier':
			if (get_option_wizard('wizard-creer-hors-collection', 'wizard_pays') 
			 || get_option_wizard('wizard-creer-collection', 'wizard_pays') != undefined)
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
			$('#'+wizard_id+' .chargement').removeClass('cache');
			$('#tranches_pretes_magazine').addClass('cache');
			if (get_option_wizard('wizard-creer-collection','choix_tranche')!= undefined) {
				var tranche=get_option_wizard('wizard-creer-collection','choix_tranche').split(/_/g);
				pays=tranche[1];
				magazine=tranche[2];
				numero=tranche[3];
			}
			else {
				pays=get_option_wizard('wizard-creer-hors-collection', 	   'wizard_pays');
				magazine=get_option_wizard('wizard-creer-hors-collection', 'wizard_magazine');
				numero=get_option_wizard('wizard-creer-hors-collection',   'wizard_numero');
			}
			selecteur_cellules_preview='#'+wizard_id+' #tranches_pretes_magazine td';
			var numero_selectionne=numero;
			
			charger_liste_numeros(pays,magazine, function(data) {
				var numeros_existants=data.numeros_dispos;
				
				var tranches_pretes=new Array();
				var numero_selectionne_trouve=false;
				for (var numero_existant in numeros_existants) {
					if (numero_existant != 'Aucun') {
						var est_tranche_prete=data.tranches_pretes[numero_existant] !== undefined;
						if (numero_existant == numero_selectionne) {
							if (tranches_pretes.length > NB_MAX_TRANCHES_SIMILAIRES_PROPOSEES) {// Filtre sur les 5 dernières précédentes
								tranches_pretes=tranches_pretes.slice(tranches_pretes.length-NB_MAX_TRANCHES_SIMILAIRES_PROPOSEES, tranches_pretes.length);
							}
							tranches_pretes.push(numero_selectionne);
							numero_selectionne_trouve=true;
						}
						else if (est_tranche_prete) {
							// On arrête après 5x2 tranche similaires + le nouveau numéro
							if (!numero_selectionne_trouve || tranches_pretes.length < NB_MAX_TRANCHES_SIMILAIRES_PROPOSEES*2 + 1) {
								tranches_pretes.push(numero_existant);
							}
						}
					}
				}
			
				if (!numero_selectionne_trouve) {
					// Entrer ici signifie qu'il n'y a pas de tranches prêtes après le numéro sélectionné
					if (tranches_pretes.length > NB_MAX_TRANCHES_SIMILAIRES_PROPOSEES) {// Filtre sur les 5 dernières précédentes
						tranches_pretes=tranches_pretes.slice(tranches_pretes.length-NB_MAX_TRANCHES_SIMILAIRES_PROPOSEES, tranches_pretes.length);
					}
					tranches_pretes.push(numero_selectionne);
				}
				
				// Pas de proposition de tranche
				if (tranches_pretes.length <= 1) {
					wizard_do($('#'+wizard_id),'goto_wizard-dimensions');
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
				
				$('#'+wizard_id+' .image_preview').click(function() {
					$('#'+wizard_id+' .image_preview').removeClass('selected');
					$(this).addClass('selected');
					$('#'+wizard_id+' input[type="radio"][value="'+$(this).data('numero')+'"]').prop('checked',true);
				});
				$('#'+wizard_id+' .chargement').addClass('cache');
				$('#tranches_pretes_magazine').removeClass('cache');
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
		
		case 'wizard-images':
			$('#'+wizard_id).find('.accordion').accordion({
				active: 1
			});
			var type_gallerie = $('#'+wizard_id).hasClass('photo_principale') ? 'Photos' : 'Source';
			lister_images_gallerie(type_gallerie);
		break;
		
		case 'wizard-conception':
			if (get_option_wizard('wizard-1','choix_tranche_en_cours') != undefined) {
				var tranche_en_cours=get_option_wizard('wizard-1','choix_tranche_en_cours').split(/_/g);
				pays=tranche_en_cours[1];
				magazine=tranche_en_cours[2];
				numero=tranche_en_cours[3];
			}
			else { // Nouvelle tranche => paramétrage des dimensions, etc.
				if (get_option_wizard('wizard-creer-collection','choix_tranche') != undefined
				 || get_option_wizard('wizard-creer-hors-collection','wizard_pays') != undefined) {
					if (get_option_wizard('wizard-creer-collection','choix_tranche')!= undefined) {
						var tranche=get_option_wizard('wizard-creer-collection','choix_tranche').match(REGEX_NUMERO);
						pays=tranche[1];
						magazine=tranche[2];
						numero=tranche[3];
					}
					else {
						pays=get_option_wizard('wizard-creer-hors-collection','wizard_pays');
						magazine=get_option_wizard('wizard-creer-hors-collection','wizard_magazine');
						numero=get_option_wizard('wizard-creer-hors-collection','wizard_numero');
					}
					
					if (get_option_wizard('wizard-clonage','choix')=== undefined) { // S'il n'y a pas eu clonage, on ne connait pas les dimensions de la tranche
						// Ajout du modèle de tranche et de la fonction Dimensions avec les paramètres par défaut
						$.ajax({
							url: urls['insert_wizard']+['index',pays,magazine,numero,'_',-1,'Dimensions'].join('/'),
						    type: 'post',
						    async: false
						});
						// Mise à jour de la fonction Dimensions avec les valeurs entrées
						var parametrage_dimensions =  'Dimension_x='+get_option_wizard('wizard-dimensions','Dimension_x')
													+'&Dimension_y='+get_option_wizard('wizard-dimensions','Dimension_y');
						$.ajax({
							url: urls['update_wizard']+['index',pays,magazine,numero,-1,parametrage_dimensions].join('/'),
						    type: 'post',
						    async: false
						});
					}
					maj_photo_principale();
				}
			}

			$.ajax({
				url: urls['tranchesencours']+['load','null',pays,magazine,numero].join('/'),
			    type: 'post',
				dataType:'json',
			    async: false,
			    success: function(data) {
			    	var tranche=traiter_tranches_en_cours(data)[0];
					pays=tranche.Pays;
					magazine=tranche.Magazine;
					numero=tranche.Numero;

					$('#nom_complet_tranche_en_cours')
						.html($('<img>').attr({src:'../../images/flags/'+pays+'.png'}))
						.append(' '+tranche.str_userfriendly);
			    }
			});

			afficher_photo_tranche();
			
			$('#action_bar').removeClass('cache');
			selecteur_cellules_preview='.wizard.preview_etape div.image_etape';
			$('#'+wizard_id).dialog().dialog('option','position',['right','top']);
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
								if (modification_etape != null) {
									$('.preview_vide')
										.css({'width' :$('#Dimension_x').val()*zoom+'px', 
											  'height':$('#Dimension_y').val()*zoom+'px'});
									
									var valeurs=modification_etape.find('[name="form_options"]').serializeObject();
									var section_preview_etape = modification_etape.find('.preview_etape');
									var nom_fonction=modification_etape.data('nom_fonction');
							    	alimenter_options_preview(valeurs, section_preview_etape, nom_fonction);
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
									ajouter_preview_etape(num_etape, nom_fonction);
								}
							}
							
							var wizard_etape_finale = $('.wizard.preview_etape.initial').clone(true);
							var div_preview=$('<div>').data('etape','final').addClass('image_etape finale');
							wizard_etape_finale.html(div_preview).append($('<span>',{'id':'photo_tranche'}));
							
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
							
							wizard_etape_finale.d().resize(function() {
								placer_dialogues_preview();
								if (modification_etape != null 
								 && modification_etape.find('#options-etape--Polygone').length != 0) {
									var options=modification_etape.find('[name="form_options"]');
									positionner_points_polygone(options);
								}
							});

							charger_previews();
				            
				            $('.ajout_etape').click(function() {
				            	if (modification_etape == null) {
				            		etape_ajout=$(this).data().etape;
				            		etape_ajout_pos=$(this).data().pos;
				            		launch_wizard('wizard-ajout-etape');
				            	}
				            	else {
				            		verifier_changements_etapes_sauves(modification_etape,'wizard-confirmation-annulation', function() { 
				            			launch_wizard('wizard-ajout-etape');
				            		});
				            	}
				            });
				            
							$('.wizard.preview_etape:not(.final)').click(function() {
								var dialogue=$(this).d();
								if (dialog.hasClass('cloneable')) {
									return;
								}
								if (modification_etape != null) {
									if (dialogue.data('etape') == modification_etape.data('etape'))
										return;
									else {
										verifier_changements_etapes_sauves(modification_etape,'wizard-confirmation-annulation', function() { 
											ouvrir_dialogue_preview(dialogue);
										});
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
							afficher_photo_tranche();
						}
					});
					
				}
			});
		break;
		case 'wizard-ajout-etape':
			$('#'+wizard_id).find('.accordion').accordion({
				active: 1
			});
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
        	$('#selectionner_etape_base').click(function() {
				$('#'+wizard_id).dialog().dialog("close");
        		$('.dialog-preview-etape')
        			.addClass('cloneable')
        			.click(function() {
        				$('#section_etape_a_cloner')
    						.removeClass('cache');
        				$('#etape_a_cloner')
        					.text($(this).data('etape'))
        				$('#'+wizard_id).dialog().dialog("open");
        				$('.dialog-preview-etape')
            				.removeClass('cloneable');
        			});
        	});
        break;
		case 'wizard-resize':
			$('#'+wizard_id+' img')
			  .jrac({image_height:480})
				.bind('jrac_events', function(event, $viewport) {
					var crop_inconsistent_element=$(this).d().find('.error.crop_inconsistent');
					if ($viewport.observator.crop_consistent())
						crop_inconsistent_element.addClass('cache');
					else
						crop_inconsistent_element.removeClass('cache');
				});
		break;
		
		case 'wizard-confirmation-validation-modele-contributeurs':
			$.ajax({
		        url: urls['listerg']+['index','Utilisateurs',[pays,magazine,numero_chargement].join('_')].join('/'),
		        dataType:'json',
		        type: 'post',
		        success:function(data) {
		           var utilisateur_courant=$('#utilisateur').html();
		     	   
		     	   $.each($('#'+wizard_id+' span'),function(i,span) {
		     		   var div=$('<div>');
		     		   var type_contribution=$(span).attr('id');
		     		   for (var username in data) {
		     			   var option = $('<input>',{'name':$(span).attr('id'),'type':'checkbox'}).val(username);
		     			   var coche=(type_contribution == 'photographes' &&  data[username].indexOf('p') != -1)
							      || (type_contribution == 'designers' 	  && (data[username].indexOf('d') != -1
							    		  								   || utilisateur_courant===username));
		     			   option.prop({'checked': coche, 'readOnly': coche});
		     			   $(div).append($('<div>').css({'font-weight':coche?'bold':'normal'}).append(option).append(username));
		     		   }
		     		   $(span).append(div);
		     	   });
		        }
			});
		break;
	}
}

function afficher_liste_magazines(wizard_id, id_element_liste, data) {
	$('#'+wizard_id+' .explication').addClass('cache');
	$('#'+wizard_id+' .chargement').removeClass('cache');
	var tranches = traiter_tranches_en_cours(data);
	$('#'+wizard_id+' .chargement').addClass('cache');
	if (tranches.length > 0) {
		$('#'+wizard_id+' #'+id_element_liste).removeClass('cache');
		$('#'+wizard_id+' .explication').removeClass('cache');
		$('#'+wizard_id+' #to-wizard-conception').button('option','disabled',false);
		for (var i_tranche_en_cours in tranches) {
			var tranche_en_cours=tranches[i_tranche_en_cours];
			var bouton_tranche_en_cours=$('#'+id_element_liste+' .init').clone(true).removeClass('init');
			var id_tranche='tranche_'+tranche_en_cours.Pays+'_'+tranche_en_cours.Magazine+'_'+tranche_en_cours.Numero;
			bouton_tranche_en_cours.find('input')
				.attr({'id':id_tranche})
				.val(id_tranche);
			bouton_tranche_en_cours.find('label')
				.attr({'for':id_tranche})
				.css({'background-image': 'url("../../images/flags/'+tranche_en_cours.Pays+'.png")'})
				.html(tranche_en_cours.str_userfriendly)
				.click(function() {
					$('#'+wizard_id+' #to-wizard-conception').click();
				});
			if ($('#'+wizard_id+' #'+id_element_liste+' #'+id_tranche).length == 0) {
				$('#'+wizard_id+' #'+id_element_liste).append(bouton_tranche_en_cours);
			}
		}
		$('#'+id_element_liste+' .init').remove();
		$('#'+wizard_id+' #'+id_element_liste).buttonset().menu();
		$('#'+wizard_id+' #to-wizard-creer, #'+wizard_id+' #to-wizard-modifier').click(function() {
			$('#'+wizard_id+' #'+id_element_liste+' .ui-state-active').removeClass('ui-state-active');
		});
	}
	else {
		$('#'+wizard_id+' .pas_de_numero').removeClass('cache');
	}
}

function traiter_tranches_en_cours(data) {
	var tranches_en_cours_existent=typeof(data) == 'array' && data.length > 0;
	if (tranches_en_cours_existent)
		return [];
	var tranches=[];
	for (var i_tranche_en_cours in data) {
		var tranche_en_cours=data[i_tranche_en_cours];
		tranche_en_cours.str=tranche_en_cours.Pays+'_'+tranche_en_cours.Magazine+'_'+tranche_en_cours.Numero;
		tranche_en_cours.str_userfriendly=tranche_en_cours.Magazine_complet+' n&deg;'+tranche_en_cours.Numero;
		tranches.push(tranche_en_cours);
	}
	return tranches;
}

function ajouter_preview_etape(num_etape, nom_fonction) {
	var wizard_etape = $('.wizard.preview_etape.initial').clone(true);
	var div_preview=$('<div>').data('etape',num_etape+'').addClass('image_etape');
	var div_preview_vide=$('<div>')
		.addClass('preview_vide cache')
		.css({'width' :$('#Dimension_x').val()*zoom+'px', 
			  'height':$('#Dimension_y').val()*zoom+'px'});
	wizard_etape.append(div_preview)
				.append(div_preview_vide);
	
	var posX = $('#wizard-conception').parent().offset().left-(etapes_valides.length);
	wizard_etape.dialog({
		resizable: false,
		draggable: false,
		width: 'auto',
		minWidth: 0,
		minHeight: div_preview_vide.height()+'px',
		position: [posX,0],
	    closeOnEscape: false,
		modal: false,
		open:function(event,ui) {
			$(this).removeClass('initial');
			$(this).data('etape',num_etape);
			$(this).d().addClass('dialog-preview-etape')
					   .data('etape',num_etape)
					   .data('nom_fonction',nom_fonction);
			$(this).d().find('.ui-dialog-titlebar').addClass('logo_option')
												   .css({'padding':'.3em .6em;'})
										 		   .prepend($('<img>',{'height':18,'src':base_url+'images/fonctions/'+nom_fonction+'.png',
 	   											    		     	   'alt':nom_fonction}));
			$(this).d().find('.ui-dialog-title').addClass('cache').html(nom_fonction);
		},
		beforeClose:function(event,ui) {
			$('#num_etape_a_supprimer').html($(this).data('etape'));
			$('#wizard-confirmation-suppression').dialog({
				resizable: false,
				height:140,
				modal: true,
				buttons: {
					"Supprimer": function() {
						var etape=$('#num_etape_a_supprimer').html();
						$.ajax({
							url: urls['supprimer_wizard']+['index',pays,magazine,numero,etape].join('/'),
							type: 'post',
							success:function(data) {
								$('#wizard-confirmation-suppression').dialog().dialog( "close" );
								$('.dialog-preview-etape,.wizard.preview_etape:not(.initial)').getElementsWithData('etape',etape).remove();
								chargements[0]='final';
								charger_previews(true);
							}
						});
					},
					"Annuler":function() {
						$(this).dialog().dialog("close");
					}
				}
			});
			return false;
		}
	});
	wizard_etape.d().resize(function(e) {
		if (!($(e.target).hasClass('wizard') || $(e.target).hasClass('ui-dialog'))) {
			return;
		}
		if (modification_etape != null 
		 && modification_etape.find('#options-etape--Polygone').length != 0) {
			var options=modification_etape.find('[name="form_options"]');
			positionner_points_polygone(options);
		}
		placer_dialogues_preview();
	});
	chargements.push(num_etape+'');
}

function charger_previews(forcer_placement_dialogues) {
	forcer_placement_dialogues = forcer_placement_dialogues || false;
	chargements.push('final'); // On ajoute l'étape finale
	
	numero_chargement=numero;
	chargement_courant=0;
    charger_preview_etape(chargements[0],true,'_',function() {
		if (etapes_valides.length == 1 || forcer_placement_dialogues) {
			placer_dialogues_preview();
		}
    });
}

function largeur_max_preview_etape_ouverte() {
	var largeur_autres=0;
	$.each($('.wizard.preview_etape:not(.initial),#wizard-conception'), function() {
		largeur_autres+=$(this).dialog().dialog('option','width')+LARGEUR_INTER_ETAPES;
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
	section_preview_etape.dialog().dialog('option', 'width', largeur_max_preview_etape_ouverte());
	dialogue.find('.ui-dialog-titlebar .ui-dialog-title').removeClass('cache');
	section_preview_vide.after($('#options-etape--'+nom_fonction)
						.removeClass('cache')
						.css({'margin-left':(section_preview_vide.position().left+largeur_tranche+5*zoom)+'px',
							  'min-height':section_preview_vide.height()+'px'}));

	section_preview_etape.dialog().dialog('option','buttons',{
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
	section_preview_etape.find('.buttonset').buttonset();
	recuperer_et_alimenter_options_preview(num_etape);
}

function fermer_dialogue_preview(dialogue) {
	dialogue.removeClass('modif')
			.css({'width':'auto'});
	dialogue.find('.ui-dialog-buttonpane').remove();
	dialogue.find('.ui-dialog-titlebar .ui-dialog-title').addClass('cache');
	dialogue.find('.options_etape').addClass('cache');
	dialogue.find('.image_etape img, .preview_vide').toggleClass('cache');
	dialogue.find('[name="form_options"],[name="form_options_orig"]').remove();
	dialogue.find('.preview_etape').removeClass('modif');
	dialogue.find('.ui-draggable').draggable('destroy');
	dialogue.find('.ui-resizable').resizable('destroy');
	modification_etape=null;
}

function placer_dialogues_preview() {
	var dialogues=$('.dialog-preview-etape').add($('#wizard-conception').d());
	dialogues.sort(function(dialogue1,dialogue2) { // Triés par numéro d'étape, de droite à gauche
		return $(dialogue2).data('etape') == 'finale' ? 1 : $(dialogue2).data('etape') - $(dialogue1).data('etape');
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

	$('.ajout_etape:not(.template)').remove();
	var dialogues=$('.dialog-preview-etape:not(.finale)').d();
	if (dialogues.length == 0) { // Aucune étape n'existe. On créé avec le dialogue de conception pour référence
		dialogues=$('#wizard-conception').d();
	}
	dialogues.sort(function(dialogue1,dialogue2) { // Triés par offset gauche, de droite à gauche
		return $(dialogue2).offset().left - $(dialogue1).offset().left;
	});
	$.each(dialogues,function(i,dialogue) {
		var elDialogue = $(dialogue);
		var estDialogueConception = elDialogue.data('etape') === undefined;
		if (estDialogueConception) { // Dialogue de conception
			var etape = -1;
			var positions = ['apres']; // L'étape sera positionnée après l'étape -1 (=dimensions de tranche)
		}
		else {
			var etape = parseInt(elDialogue.data('etape'));
			var positions = i==dialogues.length-1 ? ['avant','apres']:['apres'];
		}
		$.each(positions,function(j,pos) {
			var pos_gauche=elDialogue.offset().left
				+ (pos==='avant' || estDialogueConception
					?(-$('.ajout_etape.template').width()-2)
					:(+$('.ajout_etape.template').width()+elDialogue.width())
				);
			var ajout_etape=$('.ajout_etape.template').clone(true).removeClass('template hidden')
			   .css({'left':pos_gauche+'px'})
			   .css({'top':elDialogue.offset().top+'px'})
			   .data('etape',etape)
			   .data('pos',pos);
			$('body').prepend(ajout_etape);
		});
	});
	$('.tip2').tooltip();
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
	var form_options = section_preview_etape.find('[name="form_options"]');
	if (form_options.length == 0) {
		form_options=$('<form>',{'name':'form_options'});
		for(var nom_option in valeurs) {
			form_options.append($('<input>',{'name':nom_option,'type':'hidden'}).val(templatedToVal(valeurs[nom_option])));
		}
		section_preview_etape.append(form_options)
							 .append(form_options.clone(true)
									 				.attr({'name':'form_options_orig'}));
	}
	
	var image = section_preview_etape.find('.preview_vide');
	
	var padding_dialogue = form_userfriendly.d().outerWidth(false)
						 - form_userfriendly.d().innerWidth();
	form_userfriendly.css({'margin-left':(padding_dialogue+image.width()+PADDING_PARAMETRAGE_ETAPE)+'px'});
	
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
		
		case 'Degrade':
			
			var pos_x_debut=image.position().left +parseFloat(templatedToVal(valeurs['Pos_x_debut']))*zoom;
			var pos_x_fin=image.position().left +parseFloat(templatedToVal(valeurs['Pos_x_fin']))*zoom;
			var pos_y_debut=image.position().top +parseFloat(templatedToVal(valeurs['Pos_y_debut']))*zoom;
			var pos_y_fin=image.position().top +parseFloat(templatedToVal(valeurs['Pos_y_fin']))*zoom;
				
			classes_farbs['Couleur_debut']='.couleur_debut';
			classes_farbs['Couleur_fin']='.couleur_fin';

			var rectangle = form_userfriendly.find('.rectangle_degrade');

			rectangle.css({'top':    pos_y_debut 		   +'px', 
						   'left':   pos_x_debut 		   +'px', 
						   'width':  (pos_x_fin-pos_x_debut)+'px',
						   'height': (pos_y_fin-pos_y_debut)+'px'})
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
			coloriser_rectangle_degrade(rectangle,'#'+valeurs['Couleur_debut'],'#'+valeurs['Couleur_fin'],valeurs['Sens']);
			
			var choix = form_userfriendly.find('[name="option-Sens"]');
			choix.click(function() {
   		    	tester_option_preview(nom_fonction,'Sens');
   		    	coloriser_rectangle_degrade(rectangle,null,null,$(this).val());
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

			classes_farbs['Couleur']='';

			var rectangle1 = form_userfriendly.find('.premier.rectangle_degrade');
			var rectangle2 = form_userfriendly.find('.deuxieme.rectangle_degrade');
			
			var c1=valeurs['Couleur'];

			rectangle1.css({'left':image.position().left+'px'});
			rectangle2.css({'left':parseInt(image.position().left+image.width()/2)+'px'});
			form_userfriendly.find('.rectangle_degrade')
				.css({'top':    image.position().top +'px', 
					  'width':  image.width()/2	  	 +'px',
					  'height': image.height()		 +'px'})
			    .removeClass('cache');
			coloriser_rectangles_degrades(c1);
	
		break;
		case 'Remplir':
			classes_farbs['Couleur']='';
			
			coloriser_rectangle_preview(form_userfriendly.d().find('.preview_vide'),valeurs['Couleur'],true);
			
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
		case 'Arc_cercle':
			classes_farbs['Couleur']='';
			
			var arc=form_userfriendly.find('.arc_position');
				
			if (section_preview_etape.find('.preview_vide .arc_position').length == 0) {
				arc = arc.clone(true);
				arc.appendTo(section_preview_etape.find('.preview_vide'));
			}
			else {
				arc = section_preview_etape.find('.preview_vide .arc_position');
			}
			dessiner(arc, 'Arc_cercle', form_options);

			checkboxes.push('Rempli');
			form_userfriendly.valeur('Rempli')
							 .val(valeurs['Rempli'] == 'Oui')
							 .change(function() {
								 var nom_option=$(this).attr('name').replace(/option\-([A-Za-z0-9]+)/g,'$1');
								 tester_option_preview(nom_fonction,nom_option);
								 dessiner(arc, 'Arc_cercle', $('[name="form_options"]'));
							 });
			form_userfriendly.valeur('drag-resize').change(function() {
				var arc = section_preview_etape.find('.preview_vide .arc_position');
				if ($(this).val()=='deplacement') {
					if (arc.is('.ui-resizable')) {
						arc.resizable("destroy");
					}
					arc.draggable({
						stop: function(event,ui) {
						   tester_option_preview(nom_fonction,'Pos_x_centre'); 
						   tester_option_preview(nom_fonction,'Pos_y_centre');
						}
					});
				}
				else {
					if (arc.is('.ui-draggable')) {
						arc.draggable("destroy");
					}
					arc.resizable({
						 stop: function(event,ui) {
						   tester_option_preview(nom_fonction,'Largeur'); 
						   tester_option_preview(nom_fonction,'Hauteur');
						   tester_option_preview(nom_fonction,'Pos_x_centre'); 
						   tester_option_preview(nom_fonction,'Pos_y_centre');
						   dessiner(arc, 'Arc_cercle', $('[name="form_options"]'));
						 }
					 });
				}
			});
			form_userfriendly.find('#Arc_deplacement').click();
		
		break;
		
		case 'Polygone':
			classes_farbs['Couleur']='';
			
			var polygone=form_userfriendly.find('.polygone_position');
				
			if (section_preview_etape.find('.preview_vide .polygone_position').length == 0) {
				polygone = polygone.clone(true);
				polygone.appendTo(section_preview_etape.find('.preview_vide'));
			}
			else {
				polygone = section_preview_etape.find('.preview_vide .polygone_position');
			}
			dessiner(polygone, 'Polygone', form_options, function() {
				positionner_points_polygone(form_options);
				
			});

			form_userfriendly.valeur('action').change(function() {
				var action = $(this).val();
				form_userfriendly.find('#descriptions_actions div').addClass('cache');
				form_userfriendly.find('#descriptions_actions div#description_'+action).removeClass('cache');
				positionner_points_polygone(form_options);
			});
			
			form_userfriendly.find('#Point_deplacement').click();
		
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
								 coloriser_rectangle_preview($('.modif .rectangle_position'),valeurs['Couleur'],$(this).prop('checked'));
							 });
			
			coloriser_rectangle_preview($('.modif .rectangle_position'),valeurs['Couleur'],valeurs['Rempli'] == 'Oui');

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
				event.preventDefault();
				
				$('#wizard-images')
					.addClass('autres_photos')
					.removeClass('photo_principale');
				launch_wizard('wizard-images', {modal:true, first: true});
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
				activate:function(event,ui) {
					var section_active_integration=$(this).find('.ui-accordion-content-active').hasClass('finition_texte_genere');
					if (section_active_integration) {
						generer_et_positionner_preview_myfonts(false,true,false);
					}
				}
			});
			generer_et_positionner_preview_myfonts(true,false,true);
			
		break;
	}
	
	for (var nom_option in classes_farbs) {
		if (! section_preview_etape.find('.picker'+classes_farbs[nom_option]).hasClass('farbtastic')) {
			var input=form_userfriendly.valeur(nom_option);
			ajouter_farb(section_preview_etape.find('.picker'+classes_farbs[nom_option]), 
						 input, nom_fonction, nom_option, '#'+valeurs[nom_option]);
			input.keyup(function() {
				farbs[nom_option].setColor('#'+$(this).val());
				callback_test_picked_color($(farbs[nom_option]), $(this), nom_fonction, nom_option);
			});
		}
	}
	
	for (var i in checkboxes) {
		form_userfriendly.valeur(checkboxes[i])
						 .attr('checked',valeurs[checkboxes[i]]=='Oui');
	}
}

function dessiner(element, type, form_options, callback) {
	callback = callback || function() {};
	var url_appel=urls['dessiner']+"index/"+type+"/"+zoom+"/0";
	var options = [];
	switch(type) {
		case 'Arc_cercle':
			options = ['Couleur','Pos_x_centre','Pos_y_centre','Largeur','Hauteur','Angle_debut','Angle_fin','Rempli'];
			
			pos_x_courante = element.parent().position().left;
			pos_y_courante = element.parent().position().top;
			
			element.css({'left':(pos_x_courante + parseFloat(form_options.valeur('Pos_x_centre').val())*zoom
												- parseFloat(form_options.valeur('Largeur').val())	   *zoom/2)+'px',
						 'top' :(pos_y_courante + parseFloat(form_options.valeur('Pos_y_centre').val())*zoom
							 					- parseFloat(form_options.valeur('Hauteur').val())	   *zoom/2)+'px'});
		break;
		case 'Polygone':
			options = ['X','Y','Couleur'];
			
			element.css({'left':(parseFloat(form_options.valeur('Pos_x_centre').val())*zoom
							   - parseFloat(form_options.valeur('Largeur').val())	  *zoom/2)+'px',
						 'top' :(parseFloat(form_options.valeur('Pos_y_centre').val())*zoom
							   - parseFloat(form_options.valeur('Hauteur').val())	  *zoom/2)+'px'});
		break;
	}
	$.each($(options),function(i,nom_option) {
		if (nom_option == 'Pos_x_centre')
			url_appel+="/"+toFloat2Decimals(parseFloat(form_options.valeur('Largeur').val())/2);
		else if (nom_option == 'Pos_y_centre')
			url_appel+="/"+toFloat2Decimals(parseFloat(form_options.valeur('Hauteur').val())/2);
		else
			url_appel+="/"+form_options.valeur(nom_option).val();
	});
	element
		.attr({'src':url_appel})
		.load(function() {
			$(this).removeClass('cache');
			callback();
		});
}

var INTERVALLE_AJOUT_POINT_POLYGONE=2;
var derniere_demande_ajout_point=0;
function positionner_points_polygone(form_options) {
	var dialogue = $('.wizard.preview_etape.modif');
	var preview_vide = dialogue.find('.preview_vide');
	var options_etape = dialogue.find('.options_etape');
	var polygone = preview_vide.find('.polygone_position');
	
	if (polygone.length == 0) {
		return;
	}

	var liste_x=form_options.valeur('X').val().split(',');
	var liste_y=form_options.valeur('Y').val().split(',');
	
	var points_a_placer=[];
	for (var i=0;i<liste_x.length;i++) {
		var x=zoom*parseFloat(liste_x[i]);
		var y=zoom*parseFloat(liste_y[i]);
		points_a_placer.push([i,
		                      x-COTE_CARRE_DEPLACEMENT/2,
		                      y-COTE_CARRE_DEPLACEMENT/2]);
		
	}
	
	preview_vide.find('.point_polygone:not(.modele)').remove();
	for (var i in points_a_placer) {
		var point = points_a_placer[i];
		var nouveau_point= options_etape.find('.point_polygone.modele')
			.clone(true)
				.removeClass('modele cache')
			    .attr({'name':'point'+point[0]})
			    .css({'margin-left':point[1]+'px', 
			 		  'margin-top': point[2]+'px'})
			 	.mouseleave(function() {
			 		$(this).removeClass('focus');
			 		if ($(this).draggable()) {
			 			$(this).draggable("destroy");
					}
			 		$(this).click(function() {});
			 	})
			 	.mouseover(function() {
			 		$(this).addClass('focus');
			 		var action = options_etape.valeur('action').filter(':checked').val();
			 		switch(action) {
					case 'ajout':
						$(this).click(function() {
							var millis=new Date().getTime();
							if (millis - derniere_demande_ajout_point < INTERVALLE_AJOUT_POINT_POLYGONE*1000) {
								return;
							}
							derniere_demande_ajout_point=millis;
							
							var point1=$(this);			
							var nom_point1=point1.attr('name');
							var num_point1=parseInt(nom_point1.substring(5,nom_point1.length));
							var point2=$('.point_polygone[name="point'+(num_point1+1)+'"]');
							if (point2.length == 0) {
								point2=$('.point_polygone[name="point0"]');
							}
							for (var i=$('.point_polygone:not(.modele)').length -1; i>=num_point1+1; i--) {
								$('.point_polygone[name="point'+i+'"]').attr({'name':'point'+(i+1)});
							}
							var nouveau_point={'margin-left':(parseFloat(point1.css('margin-left').replace(/px$/,''))
							                   				 +parseFloat(point2.css('margin-left').replace(/px$/,'')))/2,
							                   'margin-top': (parseFloat(point1.css('margin-top' ).replace(/px$/,''))
							                		   		 +parseFloat(point2.css('margin-top' ).replace(/px$/,'')))/2};
							
							point1.after($('<div>').addClass('point_polygone')
												   .attr({'name':'point'+(num_point1+1)})
												   .css(nouveau_point));
							
				 			tester_option_preview('Polygone','X'); 
				 			tester_option_preview('Polygone','Y');
				 			dessiner(polygone, 'Polygone', form_options, function() {
					 			positionner_points_polygone(form_options);
				 			});
						});
					break;
					case 'deplacement':
						$(this).draggable({
					 		stop: function(event,ui) {
					 			tester_option_preview('Polygone','X'); 
					 			tester_option_preview('Polygone','Y');
					 			
					 			var form_options = $('[name="form_options"]');
					 			dessiner(polygone, 'Polygone', form_options, function() {
						 			positionner_points_polygone(form_options);
					 			});
							}
						});
					break;
					case 'suppression':
						$(this).click(function() {
							var nom_point=$(this).attr('name');
							$('#nom_point_a_supprimer').html(nom_point);
							$('#wizard-confirmation-suppression-point').dialog({
								resizable: false,
								height:250,
								modal: true,
								buttons: {
									"Supprimer": function() {
										$('#wizard-confirmation-suppression-point').dialog().dialog( "close" );
										var nom_point=$('#nom_point_a_supprimer').html();
										$('.point_polygone[name="'+nom_point+'"]:not(.modele)').remove();
										
							 			tester_option_preview('Polygone','X'); 
							 			tester_option_preview('Polygone','Y');
							 			dessiner(polygone, 'Polygone', form_options, function() {
								 			positionner_points_polygone(form_options);
							 			});
									}
								}
							});
						});
						
					break;
				}
			 });
		preview_vide.append(nouveau_point);
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
				  .html($('<img>')
					.attr({'src':preview.attr('src')})
					.error(function() {
						var src=$(this).attr('src');
						var nom_image=src.substring(src.lastIndexOf('/')+1,src.length);
						jqueryui_alert('L\'image '+nom_image+' n\'existe pas');
					})) 
			  	  .draggable({//containment:limites_drag, 
			  		  stop:function(event, ui) {
		   		    	tester_option_preview('Image','Decalage_x'); 
		   		    	tester_option_preview('Image','Decalage_y');
		   		      }
				  })
				  .resizable({
						stop:function(event, ui) {
			   		    	tester_option_preview('Image','Compression_x'); 
			   		    	tester_option_preview('Image','Compression_y');
			   		    }
				  });
}

function definir_et_positionner_image(source) {
	if (source === '') {
		return;
	}
	var form_userfriendly=modification_etape.find('.options_etape');
	var apercu_image=form_userfriendly.find('.apercu_image');
	apercu_image
		.attr({'src':base_url+'../edges/'+pays+'/elements/'+source})
		.load(function() {
			positionner_image($(this));
		})
		.error(function() {
			var src=$(this).attr('src');
			var nom_image=src.substring(src.lastIndexOf('/')+1,src.length);
			jqueryui_alert('L\'image '+nom_image+' n\'existe pas');
		});
}
function coloriser_rectangle_preview(element,couleur,est_rempli) {
	if (est_rempli) {
		element.css({'background-color': couleur})
			   .removeClass('outlined');
	}
	else {
		element.addClass('outlined')
			   .css({'outline-color':couleur,'background-color':''});
	}
}

function coloriser_rectangles_degrades(c1) {
	var coef_degrade=1.75;
	
	var c1_rgb=hex2rgb(c1);
	var c2=rgb2hex(parseInt(c1_rgb[0]/coef_degrade),
				   parseInt(c1_rgb[1]/coef_degrade),
				   parseInt(c1_rgb[2]/coef_degrade));
	coloriser_rectangle_degrade(modification_etape.find('.premier.rectangle_degrade'), c1,c2);
	coloriser_rectangle_degrade(modification_etape.find('.deuxieme.rectangle_degrade'),c2,c1);
}

function coloriser_rectangle_degrade(element,couleur1,couleur2, sens) {
	sens = sens || 'Horizontal';
	if (couleur1 == null) {// On garde la même couleur
		var regex=/, from\(((?:(?!\),).)+)/g;
		couleur1 = element.css('background').match(regex)[0].replace(regex,'$1');
	}
	if (couleur2 == null) {// On garde la même couleur
		var regex = /, to\(((?:(?!\)\) ).)+)/g;
		couleur2 = element.css('background').match(regex)[0].replace(regex,'$1');
	}
	if (sens == 'Horizontal') {
		element.css({'background': '-webkit-gradient(linear, left top, right top, from('+couleur1+'), to('+couleur2+'))'});
	}
	else {
		element.css({'background': '-webkit-gradient(linear, left top, left bottom, from('+couleur1+'), to('+couleur2+'))'});
	}
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
			height:300,
			modal: true,
			buttons: {
				"Sauvegarder les changements": function() {
					$('#wizard-confirmation-annulation').dialog().dialog( "close" );
					valider(function() {
						fermer_dialogue_preview($('.modif'));
						callback();
					});
				},
				"Fermer l'etape sans sauvegarder": function() {
					fermer_dialogue_preview($('.modif'));
					$( this ).dialog().dialog( "close" );
					chargements=['final']; // Etape finale
					chargement_courant=0;
					charger_preview_etape(chargements[0],true,'_',callback);
				},
				"Revenir a l'edition d'etape": function() {
					$( this ).dialog().dialog( "close" );
				}
			}
		});
	}
	else {
		fermer_dialogue_preview($('.modif'));
		callback();
	}
}

function tester(callback, modif_dimensions) {
	var dialogue=modification_etape.d();

	var form_options=dialogue.find('[name="form_options"]');
	
	chargements=['final']; // Etape finale
	chargement_courant=0;
	charger_preview_etape(chargements[0],true,num_etape_courante+"."+form_options.serialize(),callback);
}

function valider(callback) {
	callback = callback || function(){};
	var form_options=$('.modif').find('[name="form_options"]');
	var parametrage=form_options.serialize();
	
	$.ajax({
	    url: urls['update_wizard']+['index',pays,magazine,numero,num_etape_courante,parametrage].join('/'),
	    type: 'post',
	    success:function(data) {
			reload_current_and_final_previews(callback);
	    }
	});
}

function tester_option_preview(nom_fonction,nom_option,element) {
	var dialogue=$('.wizard.preview_etape.modif').d();
	var form_options=dialogue.find('[name="form_options"]');;
	var form_options_orig=dialogue.find('[name="form_options_orig"]');
	var form_userfriendly=dialogue.find('.options_etape');
	var nom_fonction=dialogue.data('nom_fonction');
	var image=dialogue.find('.preview_vide');

	var val=null;
	if (nom_option.indexOf('Couleur') == 0) {
		val=farbs[nom_option].color.replace(/#/g,'');	
	}
	else {
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
			case 'Degrade':
				var zone_degrade=dialogue.find('.rectangle_degrade');
				switch(nom_option) {
					case 'Pos_x_debut':
						val = toFloat2Decimals(parseFloat((zone_degrade.offset().left - image.offset().left)/zoom));
					break;
					case 'Pos_y_debut':
						val = toFloat2Decimals(parseFloat((zone_degrade.offset().top  - image.offset().top )/zoom));
					break;
					case 'Pos_x_fin':
						val = toFloat2Decimals(parseFloat((zone_degrade.offset().left + zone_degrade.width() - image.offset().left)/zoom));
					break;
					case 'Pos_y_fin':
						val = toFloat2Decimals(parseFloat((zone_degrade.offset().top  + zone_degrade.height()- image.offset().top )/zoom));
					break;
					case 'Sens':
						val = form_userfriendly.valeur(nom_option).filter(':checked').val();
					break;
				}
			break;
			case 'Remplir':
				var point_remplissage=dialogue.find('.point_remplissage');
				switch(nom_option) {
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
			case 'Arc_cercle':
				var arc=dialogue.find('.arc_position');
				switch(nom_option) {
					case 'Pos_x_centre':
						val = toFloat2Decimals(parseFloat(form_options.valeur('Largeur').val())/2 
											 + parseFloat(arc.position().left 
													 	+ ($('.ui-wrapper').length > 0 ? $('.ui-wrapper').position().left : 0)
													 	- image.position().left)/zoom);
					break;
					case 'Pos_y_centre':
						val = toFloat2Decimals(parseFloat(form_options.valeur('Hauteur').val())/2 
								 			 + parseFloat(arc.position().top 
								 					    + ($('.ui-wrapper').length > 0 ? $('.ui-wrapper').position().top : 0)
								 					    - image.position().top)/zoom);
					break;
					case 'Largeur':
						val=arc.width()/zoom;
					break;
					case 'Hauteur':
						val=arc.height()/zoom;					
					break;
					case 'Rempli':
						val=form_userfriendly.valeur(nom_option).prop('checked') ? 'Oui' : 'Non';					
					break;
				}
			break;
			case 'Polygone':
				switch(nom_option) {
					case 'X':
						var x = [];
						$.each(dialogue.find('.point_polygone:not(.modele)'),function(i,point) {
							point=$(point);
							x[i] = (point.offset().left + point.scrollLeft() - image.offset().left + COTE_CARRE_DEPLACEMENT/2) / zoom;
							
						});
						val=x.join(',');
					break;
					case 'Y':
						var y = [];
						$.each(dialogue.find('.point_polygone:not(.modele)'),function(i,point) {
							point=$(point);
							y[i] = (point.offset().top + point.scrollTop() - image.offset().top + COTE_CARRE_DEPLACEMENT/2) / zoom;
							
						});
						val=y.join(',');
					break;
				}
			break;
			case 'Rectangle':
				var positionnement=dialogue.find('.rectangle_position');
				switch(nom_option) {
					case 'Pos_x_debut':
						val = toFloat2Decimals(parseFloat(positionnement.offset().left - image.offset().left)/zoom);
					break;
					case 'Pos_y_debut':
						val = toFloat2Decimals(parseFloat(positionnement.offset().top  - image.offset().top )/zoom);
					break;
					case 'Pos_x_fin':
						val = toFloat2Decimals(parseFloat(positionnement.offset().left + positionnement.width() - image.offset().left)/zoom);
					break;
					case 'Pos_y_fin':
						val = toFloat2Decimals(parseFloat(positionnement.offset().top  + positionnement.height()- image.offset().top )/zoom);
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
						var original_width =dialogue.find('[name="original_preview_width"]' ).val();
						var original_height=dialogue.find('[name="original_preview_height"]').val();
						val = toFloat2Decimals(parseFloat(form_options_orig.valeur('Compression_y').val()) 
											  /(original_height/positionnement.height()));
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
	}
	form_options.valeur(nom_option).val(val);

	if (['Chaine','URL','Largeur','Demi_hauteur','Rotation'].indexOf(nom_option) != -1) {
		var generer_preview_proprietes = nom_option == 'Chaine'  || nom_option == 'URL',
			generer_preview_finition = nom_option == 'Largeur' || nom_option == 'Demi_hauteur';
		generer_et_positionner_preview_myfonts(generer_preview_proprietes,
											   generer_preview_finition,
											   true);
	}
}

function generer_et_positionner_preview_myfonts(gen_preview_proprietes, gen_preview_finition,gen_tranche) {
	load_myfonts_preview(gen_preview_proprietes,gen_preview_finition,gen_tranche, !gen_tranche ? function() {} : function() {
		var dialogue=modification_etape.d();
		var form_userfriendly=dialogue.find('.options_etape');
		var valeurs=dialogue.find('[name="form_options"]').serializeObject();
		var image=dialogue.find('.preview_vide');
		
		var position_texte=form_userfriendly.find('.image_position');
		var image_preview_ajustee=$('body>.apercu_myfonts img');
		var ratio_image_preview_ajustee=image_preview_ajustee.prop('width')/image_preview_ajustee.prop('height');
		
		var largeur=toFloat2Decimals(image.width() * parseFloat(valeurs['Compression_x']));
		var hauteur=toFloat2Decimals(image.width() * parseFloat(valeurs['Compression_y']) / ratio_image_preview_ajustee);
		
		var pos_x=image.position().left+parseFloat(valeurs['Pos_x'])*zoom;
		var pos_y=image.position().top +parseFloat(valeurs['Pos_y'])*zoom;
		if (valeurs['Mesure_depuis_haut'] == 'Non') { // Le pos_y est mesuré entre le haut de la tranche et le bas du texte
			pos_y-=parseFloat(hauteur);
		}
		
		var limites_drag=[(image.offset().left-parseFloat(largeur)),
		                  (image.offset().top -parseFloat(hauteur)),
		                  (image.offset().left+image.width()),
		                  (image.offset().top +image.height())];
		position_texte.css({'left':pos_x+'px', 
						    'top': pos_y+'px',
						    'width':largeur+'px',
						    'height':hauteur+'px'})
					  .removeClass('cache')
					  .draggable({//containment:limites_drag, 
				  		  stop:function(event, ui) {
			   		    	tester_option_preview('TexteMyFonts','Pos_x'); 
			   		    	tester_option_preview('TexteMyFonts','Pos_y');
			   		      }
					  })
					  .resizable({
							stop:function(event, ui) {
				   		    	tester_option_preview('TexteMyFonts','Compression_x'); 
				   		    	tester_option_preview('TexteMyFonts','Compression_y');
				   		    }
					  });
		var image_a_positionner = image_preview_ajustee.clone(false);
		if (position_texte.find('img').length == 0) {
			position_texte.append(image_a_positionner);
		}
		else {
			position_texte.find('img').replaceWith(image_a_positionner);
		}

		if (dialogue.find('[name="original_preview_width"]' ).val() === '') {
			dialogue.find('[name="original_preview_width"]' ).val(largeur);
			dialogue.find('[name="original_preview_height"]').val(hauteur);
		}
		
		placer_extension_largeur_preview();
	});
}


function reload_current_and_final_previews(callback) {
	chargements=[modification_etape.data('etape')];
	fermer_dialogue_preview(modification_etape);
    charger_preview_etape(chargements[0],true, undefined, function() {
    	chargements=['final'];
        charger_preview_etape(chargements[0],true, undefined, callback);
    });
}

function reload_all_previews() {
	afficher_photo_tranche();
	$('.ui-draggable').draggable('destroy');
	$('.ui-resizable').resizable('destroy');
	selecteur_cellules_preview='.wizard.preview_etape div.image_etape';
	chargements=new Array();
	$.each($(selecteur_cellules_preview),function(i,element) {
		chargements.push($(element).data('etape'));
	});
	
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
			  'color':couleur_foncee ? '#ffffff' : '#000000'})
		.val(couleur);
	
}


function callback_test_picked_color(farb, input_couleur,nom_fonction,nom_option) {
	tester_option_preview(nom_fonction,nom_option);
	var form_options=input_couleur.d().find('[name="form_options"]');
	var couleur = farb[0].color;
	switch (nom_fonction) {
		case 'Remplir':
			coloriser_rectangle_preview(form_options.d().find('.preview_vide'),couleur,true);
		break;
		case 'Degrade':
			if (input_couleur.attr('name').indexOf('Couleur_debut') != -1)
				coloriser_rectangle_degrade(form_options.d().find('.rectangle_degrade'),couleur,null,form_options.valeur('Sens').val());
			else
				coloriser_rectangle_degrade(form_options.d().find('.rectangle_degrade'),null,couleur,form_options.valeur('Sens').val());
		break;
		case 'DegradeTrancheAgrafee':
			coloriser_rectangles_degrades(couleur.replace(/#/g,''));
		break;
		case 'TexteMyFonts':
			load_myfonts_preview(true,true,true);
		break;
		case 'Rectangle':
			coloriser_rectangle_preview(couleur,
										form_options.valeur('Rempli').val()=='Oui');
		case 'Arc_cercle':
			dessiner($('.preview_vide .arc_position'), 'Arc_cercle', form_options);
		case 'Polygone':
			dessiner($('.preview_vide .polygone_position'), 'Polygone', form_options);
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
		selectors.push('body>.apercu_myfonts');
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
		if ($(this).parent().parent().is('body')) // Preview globale donc avec rotation
			url_appel+="/"+form_options.valeur('Rotation').val();
		else
			url_appel+='/null';

		$(this).attr({'src':url_appel});	
		$(this).load(function() {
			$(this).removeClass('loading').removeClass('cache');
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
	charger_liste_numeros(pays,magazine,function(data) {
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
	});
}
	
function charger_liste_numeros(pays_sel,magazine_sel, callback) {
	$.ajax({
		url: urls['numerosdispos']+['index',pays_sel,magazine_sel].join('/'),
		type: 'post',
		dataType: 'json',
		success: callback
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
					$('#wizard-images')
						.addClass('photo_principale')
						.removeClass('autres_photos');
					launch_wizard('wizard-images', {modal:true, first: true});
				break;
				case 'corbeille':
					jqueryui_alert_from_d($('#wizard-confirmation-desactivation-modele'), function() {
						$.ajax({
			                url: urls['desactiver_modele']+['index',pays,magazine,numero].join('/'),
			                type: 'post',
			                success:function(data) {
			                	location.reload();
			                }
						});
					});
				break;
				case 'valider':
					jqueryui_alert_from_d($('#wizard-confirmation-validation-modele'), function() {
						launch_wizard('wizard-confirmation-validation-modele-contributeurs', {modal:true, first: true, closeable: true});
					});
				break;
			}
			
		});
	});
}

function afficher_photo_tranche() {
	if (nom_photo_principale !== null) {
		var image = $('<img>').height(parseInt($('#Dimension_y').val()) * zoom);
		$('#photo_tranche').html(image);
		image.attr({'src':base_url+'../edges/'+pays+'/photos/'+nom_photo_principale});
		image.load(function() {
			$(this).css({'display':'inline'});
			$('.dialog-preview-etape.finale').width(Math.max(LARGEUR_DIALOG_TRANCHE_FINALE,
															 parseInt($('#Dimension_x').val()) * zoom+$(this).width() + 14));
		});
		image.error(function() {
			$(this).css({'display':'none'});
			launch_wizard('wizard-pas-d-image', {modal:true, first: true, deadend: true, closeable: true});
		});
	}
	else {
		$.ajax({
			url: urls['photo_principale']+['index',pays,magazine,numero].join('/'),
			type: 'post',
			success:function(nom_photo) {
				if (nom_photo !== 'null') {
					nom_photo_principale = nom_photo;
					afficher_photo_tranche();				
				}
			}
		});
	}
}

function maj_photo_principale() {
	if (nom_photo_principale === null) {
		return;
	}
	$.ajax({
		url: urls['update_photo']+['index',pays,magazine,numero,nom_photo_principale].join('/'),
	    type: 'post',
	    success:function(data) {
			if ($('#wizard-conception').is(':visible')) {
				afficher_photo_tranche();
			}
	    }
	});
}

function lister_images_gallerie(type_images) {
	var container;
	if (type_images === 'Source') {
		container=$('#wizard-images');
	}
	else {
		container=$('#wizard-images').find('form');
	}
	
	$.ajax({
        url: urls['listerg']+['index',type_images,pays,magazine].join('/'),
        dataType:'json',
        type: 'post',
        success:function(data) {
        	if (data['erreur']) {
        		jqueryui_alert('Le r&eacute;pertoire d\'images '+data['erreur']+' n\'existe pas',
        					   'Erreur interne');
        	}
        	var ul=container.find('ul.gallery');
        	ul.find('li:not(.template)').remove();
        	if (data.length == 0) {
        		container.find('.pas_d_image').removeClass('cache');
        	}
        	else {
        		var sous_repertoire = null;
        		switch(type_images) {
        			case 'Source':
        				sous_repertoire = 'elements';
        			break;
        			case 'Photos':
        				sous_repertoire = 'photos';
        			break;
        		}
        		container.find('.pas_d_image').addClass('cache');
        		container.find('ul.gallery li:not(.template) img').remove();
            	for (var i in data) {
            		var li=ul.find('li.template').clone(true).removeClass('template');
            		li.find('em').html(data[i].replace(/[^\.]+\./g,''));
            		li.find('img').prop({'src':base_url+'../edges/'+pays+'/'+sous_repertoire+'/'+data[i],
            							 'title':data[i]});
            		li.find('input').val(data[i]);
            		ul.append(li);
            	}
    			container.find('ul.gallery li img').removeClass('selected')
    										  .unbind('click')
            								  .click(function() {
            		if ($(this).hasClass('selected')) {
            			container.find('ul.gallery li img').removeClass('selected');
            			container.find('button[value="to-wizard-resize"]').addClass('cache');
            		}
            		else {
            			container.find('ul.gallery li img').removeClass('selected');
                		$(this).addClass('selected');
                		container.find('button[value="to-wizard-resize"]').removeClass('cache');
                		
                		$('#wizard-resize img').attr({'src':$(this).attr('src')});
                	}
            	});
            	if (type_images === 'Photos' && nom_photo_principale !== null) {
            		container.find('ul.gallery li img[src$="'+nom_photo_principale+'"]').click();
            	}
        	}
        	ul.removeClass('cache');
        	container.find('.chargement_images').addClass('cache');
        }
	});
}

function templatedToVal(templatedString) {
	$.each(TEMPLATES,function(nom, regex) {
		var matches = (templatedString+'').match(regex);
		if (matches != null) {
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