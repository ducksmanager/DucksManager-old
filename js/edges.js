var tranche_en_cours;
var tranche_bib;
var current_couv;
var hauteur_image;
var action_en_cours=false;
var couverture_ouverte=false;
var ouvrirApres=false;
var largeur_section;
var couverture;
var ouverture_couverture;
var nb_etageres;
var extraits;
var extrait_courant;
var chargement_extrait=false;
var noms_magazines = [];
var textures;
var popularite_numeros = [];
var user_points = 0;
var niveau_actuel = 0;
var niveaux_medailles = {};
var est_contexte_bibliotheque = false;

var l10n_recherche = [
    'recherche_magazine_aucun_resultat', 'recherche_magazine_histoire_non_possedee',
    'recherche_magazine_resultats_nombreux_1','recherche_magazine_resultats_nombreux_2',
    'recherche_magazine_selectionnez_une_histoire'
];

function ouvrir_tranche() {
    if (action_en_cours || extrait_courant>0)
        return;
    jQuery('.popover').popover('destroy');
    jQuery('.fleche_position').remove();

    extraits=[];
    extrait_courant=-1;
    ouverture_couverture=true;
    if (couverture_ouverte && tranche_bib !== tranche_en_cours) {
        ouvrirApres=true;
        fermer();
        return;
    }
    jQuery('#infobulle').remove();
    action_en_cours=true;
    var infos=getInfosNumero(tranche_bib.attr('id'));
    hauteur_image=tranche_bib.height();
    couverture=jQuery('<img>');
    tranche_en_cours=tranche_bib.clone(true);
    tranche_en_cours.css({
        zIndex:500,
        position:absolute,
        left: getScreenCenterX()+'px',
        top: (getScreenCenterY()-hauteur_image/2)+'px',
        opacity: 0
    });
    jQuery('bibliotheque').append(tranche_en_cours);
    new Effect.Parallel([
        new Effect.Opacity(tranche_bib[0],{from: 1, to: 0, sync: true}),
        new Effect.Opacity(tranche_en_cours[0], {from: 0, to: 1, sync:true})
    ], {
        duration: 0.5,
        afterFinish:function() {
            jQuery('#bibliotheque').append(
	            jQuery('<div>',{id:'animation'})
		            .css({
			            position: absolute,
			            left:getScreenCenterX()+'px',
			            top:(getScreenCenterY()-hauteur_image/2)+'px',
			            zIndex:600
		            })
		            .append(
		                jQuery('<img>',{src:'loading.gif'})
                    )
            );
        }});

    jQuery.post('Inducks.class.php', {
        get_cover: 'true',
        debug: debug,
        pays: infos.Pays,
        magazine: infos.Magazine,
        numero: infos.Numero
    })
        .done(function (data) {
            jQuery('#infobulle').remove();
            if (data) {
                couverture_ouverte = true;
                var i = 0;
                while (data[i]) {
                    extraits[i] = data[i];
                    i++;
                }
                couverture.attr({src: data.cover});
                current_couv = jQuery('<div>', {id: 'page_droite_avant'})
                    .css({
                        position: 'absolute',
                        height: hauteur_image + 'px',
                        width: (couverture.width() * (hauteur_image / couverture.height())) + 'px',
                        display: 'none',
                        left: (getScreenCenterX() + tranche_en_cours.width()) + 'px',
                        top: (getScreenCenterY() - hauteur_image / 2) + 'px'
                    })
                    .addClass('page_avant');
                var current_couv_im = jQuery('<img>', {
                    id: 'page_droite_avant_im',
                    src: couverture.attr('src'),
                    height: '100%',
                    width: (couverture.width() * (hauteur_image / couverture.height())) + 'px'
                })
                    .on('click', fermer_tranche)
                    .on('load', function () {
                        current_couv.css({width: (couverture.width() * (hauteur_image / couverture.height())) + 'px'});
                        if (ouverture_couverture) {
	                        tranche_en_cours.css({
		                        width: tranche_en_cours.width()+ 'px',
		                        height: tranche_en_cours.height()+ 'px'
	                        });
	                        new Effect.Parallel([
		                        new Effect.Morph(current_couv[0], {
			                        width: (couverture.width() / (hauteur_image / couverture.height())) + 'px',
			                        sync: true
		                        }),
		                        new Effect.BlindRight(current_couv[0], {sync: true}),
		                        new Effect.Move(current_couv[0], {
			                        mode: 'absolute',
			                        x: getScreenCenterX(),
			                        y: getScreenCenterY() - hauteur_image / 2,
			                        sync: true
		                        }),
		                        new Effect.BlindLeft(tranche_en_cours[0], {sync: true})
	                        ], {
		                        duration: 1,
		                        afterFinish: function () {
			                        ouverture_couverture = false;
			                        jQuery('#animation').remove();

			                        if (extraits.length > 0 && !jQuery('#lien_apercus').length) {
				                        creer_div_apercus();
			                        }
		                        }
	                        });
	                        jQuery('#animation').remove();
	                        action_en_cours = false;
                        }
                    });
	            current_couv.html(current_couv_im.html());
	            jQuery('#body').append(current_couv);
            }
        });
}

function fermer_tranche() {
    ouvrirApres=false;
    fermer();
}

function creer_div_apercus() {
    var page_suivante=jQuery('<div>',{id: 'page_suivante'})
        .css({
            left: (getScreenCenterX()+jQuery('#page_droite_avant_im').width())+'px',
            top: getScreenCenterY()+'px'})
        .addClass('lien_apercus');

    var page_gauche_arriere=jQuery('<div>', {id: 'page_gauche_arriere'})
        .css({
            position: 'absolute',
            display: 'block',
            width: getLargeur(),
	        height: hauteur_image+'px',
	        right: getScreenCenterX()+'px',
	        top: (getScreenCenterY()-hauteur_image/2)+'px'})
	    .addClass('page_arriere');

    var page_gauche_avant=jQuery('<div>', {id: 'page_gauche_avant'})
                    .css({position: 'absolute',display: 'block',width: '0px',height: hauteur_image+'px',
                               right: getScreenCenterX()+'px',top: (getScreenCenterY()-hauteur_image/2)+'px'})
                    .addClass('page_avant');

    var page_droite_arriere=jQuery('<div>', {id: 'page_droite_arriere'})
	    .css({
		    position: 'absolute',
		    display: 'block',
		    width: getLargeur(),
		    height: hauteur_image+'px',
		    left: getScreenCenterX()+'px',
		    top: (getScreenCenterY()-hauteur_image/2)+'px'})
	    .addClass('page_arriere');

    jQuery('#body')
	    .append(page_suivante)
	    .append(page_gauche_arriere).append(page_gauche_avant)
	    .append(page_droite_arriere);
    page_suivante.on('click',function() {
        if (chargement_extrait)
            return;
        chargement_extrait=true;
        if (extrait_courant>=extraits.length) {
            back_to_cover();
        }
        else {
            if (extraits[extrait_courant].page % 2 === 1) { // Page impaire
                maj_page('page_gauche_arriere','page_invisible');
                jQuery('#page_gauche_arriere').css({width: '0'});
                intervertir_page('gauche');

                maj_page('page_droite_arriere',extraits[extrait_courant].url);
                jQuery('#page_droite_arriere').css({display: 'block'});

                jQuery('#page_droite_arriere_im').on('load',function () {
                    new Effect.BlindLeft('page_droite_avant',{
	                    duration:0.75,
	                    afterFinish:function() {
	                        new Effect.Morph('page_gauche_avant',{
	                            style: 'width:'+getLargeur()
	                        });
	                        intervertir_page('droite');
	                        jQuery('#page_gauche_avant').on('click',back_to_cover);
	                        jQuery('#page_droite_avant_im').on('click',back_to_cover);
	                        extrait_courant++;
	                        maj_div_apercus();
	                    }
                    });
                });
           }
           else { //Page paire
                maj_page('page_gauche_arriere',extraits[extrait_courant].url);
                jQuery('#page_gauche_arriere_im').css({
	                height: hauteur_image+'px',
	                width: '0'
                });
                intervertir_page('gauche');

                jQuery('#page_gauche_avant').css({
	                width: getLargeur()+'px'
                });
                maj_page('page_droite_arriere','page_invisible');

                jQuery('#page_droite_arriere').css({display: 'block'});

                jQuery('#page_gauche_avant_im').on('load',function () {
                    new Effect.Parallel([
                        new Effect.BlindLeft(jQuery('#page_droite_avant'), {sync:true})
                    ], {
                        duration: 0.75,
                        afterFinish:function() {
                            new Effect.Morph('page_gauche_avant_im',{
                            	style: 'width:'+getLargeur()
                            });
                            intervertir_page('droite');

                            jQuery('#page_gauche_avant_im').on('click',back_to_cover);
                            jQuery('#page_droite_avant').on('click',back_to_cover);
                            extrait_courant++;
                            maj_div_apercus();
                        }
                    });
                });
            }
        }
    });
    extrait_courant++;
    maj_div_apercus();
}

function getLargeur() {
    return jQuery('#page_droite_avant').css('width')==='0'
	    ?jQuery('#page_droite_arriere').css('width')
	    :jQuery('#page_droite_avant').css('width');
}
function intervertir_page(direction) {
    jQuery('#page_'+direction+'_avant')
	    .attr({id: 'page_'+direction})
	    .addClass('page_arriere')
	    .removeClass('page_avant');

    jQuery('#page_'+direction+'_avant_im')
	    .attr({id: 'page_'+direction+'_im'});

    jQuery('#page_'+direction+'_arriere')
	    .attr({id: 'page_'+direction+'_avant'})
	    .removeClass('page_arriere')
	    .addClass('page_avant');

    jQuery('#page_'+direction+'_arriere_im')
	    .attr({id: 'page_'+direction+'_avant_im'});

    jQuery('#page_'+direction)
	    .attr({id: 'page_'+direction+'_arriere'});

    jQuery('#page_'+direction+'_im')
	    .attr({id: 'page_'+direction+'_arriere_im'});
}

function maj_page(id_page,maj) {
    if (maj==='page_invisible') {
        jQuery('#' + id_page).html('')
	        .addClass('page_invisible');
    }
    else {
        jQuery('#' + id_page)
	        .html(
        	    jQuery('<img>',{id: id_page+'_im',src: maj}).html()
	        )
	        .removeClass('page_invisible');
        if (id_page.indexOf('gauche')!==-1)
            jQuery('#' + id_page+'_im')
	            .css({
		            float: 'right'
	            });
    }
}

function maj_div_apercus() {
    if (extrait_courant>=extraits.length)
        jQuery('#page_suivante').text('Fermer');
    else
        jQuery('#page_suivante')
            .text(extraits[extrait_courant].page<0?'Suivante':'Page '+extraits[extrait_courant].page);
   chargement_extrait=false;
}

function back_to_cover() {
    jQuery('#page_gauche_arriere, #page_suivante').remove();

    maj_page('page_droite_arriere',couverture.attr('src'));
    jQuery('#page_droite_arriere_im').css({width: '0'});
    jQuery('#page_droite_arriere').css({display: 'block'});
    intervertir_page('droite');
    new Effect.BlindLeft('page_gauche_avant', {
        afterFinish:function() {
            jQuery('#page_gauche_avant').remove();
            new Effect.Morph('page_droite_avant_im',{
            	style:'width:'+getLargeur(),
                afterFinish:function() {
                    jQuery('#page_droite_arriere').remove();
                    jQuery('#page_droite_avant').on('click', fermer_tranche);
                    extrait_courant=-1;
                    creer_div_apercus();
                }
            });
        }
    });
}

function fermer() {
    if (action_en_cours || ouverture_couverture)
        return;
    action_en_cours=true;
    jQuery('#page_suivante, #page_gauche_avant, #page_gauche_arriere, #page_droite_arriere').remove();
    jQuery('#page_droite_avant_im').css({
	    width: getLargeur()
    });
    new Effect.Parallel([
        new Effect.BlindLeft(jQuery('#page_droite_avant')[0], {sync:true}),
        new Effect.Move(jQuery('#page_droite_avant')[0], {
        	mode: 'absolute',
	        x: (getScreenCenterX()+tranche_bib.width()),
	        y: getScreenCenterY()-hauteur_image/2,
	        sync:true
        }),
        new Effect.BlindRight(tranche_en_cours[0], {sync:true})
    ], {
        duration: 1,
        afterFinish:function() {
            new Effect.Parallel([
                new Effect.Opacity(tranche_en_cours[0], {from: 1, to: 0, sync:true}),
                new Effect.Opacity(tranche_bib[0] ,{from: 0, to: 1, sync: true})
            ], {
                duration: 0.5,
                afterFinish:function() {
                    jQuery('#page_droite_avant').remove();
                    jQuery('.lien_apercus').remove();
                    action_en_cours=false;
                    couverture_ouverte=false;
                    if (ouvrirApres)
                        ouvrir_tranche();
                }
            });
        }
    });
}

var element_conteneur_bibliotheque;

function charger_bibliotheque() {
    est_contexte_bibliotheque = true;

	var conteneur=jQuery('#conteneur_bibliotheque');
	var section=jQuery('#bibliotheque');
    section.on('mousedown', function() {
        jQuery('.popover').popover('destroy');
    });

	largeur_section=section.width();
	jQuery('#pourcentage_collection_visible').addClass('cache');
	l10n_action('remplirSpan','pourcentage_collection_visible');

	jQuery.post('Edge.class.php', {
		data: {
			get_bibliotheque: 'true',
			largeur: largeur_section,
			user_bibliotheque: user_bibliotheque,
			cle_bibliotheque: cle_bibliotheque
		},
		success:function(response) {
			if (!!response.erreur) {
				conteneur.html(response.erreur);
			}
			else {
                jQuery.post('Edge.class.php', {
                    data: {get_popularite_numeros: true},
                    success:function(response_popularite) {
                        popularite_numeros = response_popularite.popularite_numeros;

                        charger_points_utilisateur(function() {
                            afficher_proposition_photos_tranches();
                        });
                    }
                });

                noms_magazines = response.noms_magazines;
				textures = response.textures;

				var element_bibliotheque = jQuery('#bibliotheque');

				element_bibliotheque
                    .append(response.contenu)
                    .css({
                        backgroundImage: 'url(\'edges/textures/' + textures[0].texture + '/' + textures[0].sous_texture + '.jpg\')'
                    });
				jQuery('#titre_bibliotheque').text(response.titre);
                jQuery('#pcent_visible').text(response.nb_numeros_visibles);
				jQuery('#pourcentage_collection_visible').removeClass('cache');
				var premiere_tranche = element_bibliotheque.find('.tranche:eq(0)');
				nb_etageres = jQuery('.etagere').length;
				element_conteneur_bibliotheque = element_bibliotheque;
				charger_tranche(premiere_tranche);
			}
		}
	});
}

function ajouter_etagere(afterElement) {
    var etagere = jQuery('<div>').addClass('etagere').html('&nbsp;').css({
        backgroundImage: 'url(\'edges/textures/' + textures[1].texture + '/' + textures[1].sous_texture + '.jpg\')'
    });
    if (afterElement) {
        afterElement.after(etagere);
    }
    else {
        jQuery('#bibliotheque').append(etagere);
    }
}

function charger_tranche(tranche) {
    tranche.on('load',charger_tranche_suivante);
    tranche.on('error',charger_tranche_suivante);

    var src=tranche.attr('name').replace(new RegExp('([^/]+)/','g'),('$1/gen/'));
    var src_similaires=jQuery.map(element_conteneur_bibliotheque.find('[src*="'+src+'"]'), function(i, src_similaire) {
    	return jQuery(src_similaire).attr('src');
    });
    tranche.attr({src: src_similaires[0] || 'https://edges.ducksmanager.net/edges/'+src+'.png'});
}

function charger_tranche_suivante() {
    var tranche=jQuery(this);
    var precedente=tranche.prev('.tranche');
    var suivante=tranche.next('.tranche');

    if (precedente.length && tranche.offset().left < precedente.offset().left) {
        ajouter_etagere(precedente);
    }

    if (suivante.length) {
        charger_tranche(suivante);
    }
    else {
        if (tranche.closest('#bibliotheque')) { // Contexte bibliothèque
            ajouter_etagere();
            init_observers_tranches();
            charger_recherche();
        }
        else { // Contexte affichage dans les événements récents
            callback_tranches_chargees(tranche.closest('.tooltip_content'));
        }
    }
}

function charger_recherche() {
    localStorage && localStorage.clear();
    l10n_action('fillArray',l10n_recherche,'l10n_recherche', function() {
        var element_recherche_histoire = jQuery('#recherche_histoire');
        var conteneur_bibliotheque = jQuery('#bibliotheque');

        if (element_recherche_histoire.length) {
           if (conteneur_bibliotheque.length) {
               afficher_lien_partage();

               element_recherche_histoire.css({
                   left: (jQuery('#contenu').offset().left
                       +parseInt(conteneur_bibliotheque.css('width').substring(0,conteneur_bibliotheque.css('width').length-2))-330) +'px',
                   display: 'block'});
           }
           element_recherche_histoire.find('>input')
               .on('keyup', function(e) {
                   if (/[\-!?. a-z0-9]/i.test(String.fromCharCode(e.which))) {
                       recherche_histoire();
                       e.stopPropagation();
                   }
               })
               .on('click', function(e) {
                   recherche_histoire();
                   e.stopPropagation();
               });
       }
        jQuery('#contenu').on('click', function() {
            jQuery('.magazine_trouve, .histoire_trouvee, .resultat_recherche').remove();
        });
    });
}

function charger_points_utilisateur(callback) {
    callback = callback || function() {};

    jQuery.post('Database.class.php', {
        data: {database: 'true', get_points: 'true'},
        success: function(response) {
            user_points = response.points;
            niveaux_medailles = response.niveaux_medailles;

            jQuery.each(niveaux_medailles, function(i, niveau_medaille) {
                if (user_points > niveau_medaille) {
                    niveau_actuel = parseInt(i);
                }
            });

            callback();
        }
    });
}

var zone_proposition_photos;

function afficher_lien_partage() {
	zone_proposition_photos = jQuery('#partager_bibliotheque');
	zone_proposition_photos.removeClass('cache');
	jQuery('#partager_bibliotheque_lien').on('click', function() {
		zone_proposition_photos.addClass('cache');
		jQuery.post('Edge.class.php', {
			data: {partager_bibliotheque: 'true'},
			success: function (response) {
				zone_proposition_photos
					.html(response)
					.removeClass('cache');

				var a = document.createElement('script');
				a.type = 'text/javascript';
				a.async = true;
				a.src = '//static.addtoany.com/menu/page.js';
				var s = document.getElementsByTagName('script')[0];
				s.parentNode.insertBefore(a, s);
			}
		});
	});
}

function afficher_proposition_photos_tranches() {
    var nb_tranches_affichees = 5;
    var carouselId = 'myCarouselSubmitEdgePhotos';

    var tranches_non_pretes = jQuery('.tranche[data-edge="0"]');

    if (tranches_non_pretes.length) {
        var carousel = jQuery('.carousel.small.slide')
            .attr({ id: carouselId,  dataRide: 'carousel' });

        carousel
            .find('.carousel-control')
                .attr({ href: '#' + carouselId});
        carousel
            .find('.indicator')
                .attr({'data-target': '#' + carouselId});

        carousel.afficher_medailles(niveau_actuel);

        var carouselIndicatorTemplate = carousel.find('ol.carousel-indicators>.indicator.template');
        var carouselItemTemplate = carousel.find('.carousel-inner>.item.template');

        var tranches_non_pretes_infos = jQuery.map(
            tranches_non_pretes
                .map(function() {
                    return this.id;
                }).get()
            , function(id_tranche) {
                return [ getInfosNumero(id_tranche) ];
            });
        tranches_non_pretes_infos = jQuery.grep(tranches_non_pretes_infos, function(infosTranche) {
                return getPopulariteNumero(infosTranche) > 0;
            })
            .sort(function(infosTranche1, infosTranche2) {
                var populariteNumero1 = getPopulariteNumero(infosTranche1);
                var populariteNumero2 = getPopulariteNumero(infosTranche2);

                return populariteNumero1 < populariteNumero2
                    ? 1
                    : (populariteNumero1 === populariteNumero2 ? 0 : -1);
            });
        tranches_non_pretes_infos = jQuery.grep(tranches_non_pretes_infos, function(infosNumero, index) {
                return index < nb_tranches_affichees;
            });

        jQuery.each(tranches_non_pretes_infos, function(i, infosNumero) {
            if (i === 0) {
                jQuery('.max-points-to-earn').text(getPopulariteNumero(infosNumero));
            }

            carousel.find('ol.carousel-indicators').append(carouselIndicatorTemplate.clone(true).removeClass('template')
                .attr({'data-slide-to': i})
                .toggleClass('active', i === 0));

            var newItem = carouselItemTemplate.clone(true).removeClass('template')
                .toggleClass('active', i === 0);
            newItem
                .ajouterPropositionPhoto(jQuery('.progress-wrapper.template'), infosNumero)
                .prepend(
                    jQuery('.issue_title.template').clone(true).removeClass('template')
                        .remplirTitreNumero(infosNumero)
                );
            carousel.find('.carousel-inner').append(newItem);
        });

        carousel.find('.template').remove();
        carousel.carousel({
            interval: 300000
        });

        jQuery('#proposition_photo').removeClass('cache');
    }
}

var derniere_action_recherche=null;
var recherches_reportees = [];
var recherche_en_cours = false;

function traiter_resultats_recherche_histoire(resultat, element_recherche_histoire, element_recherche_input) {
    jQuery('.fleche_position').remove();
    var conteneur_resultats_recherche = jQuery('<div>')
        .attr({id: 'conteneur_resultat_recherche'})
        .addClass('list-group');

    if (resultat['liste_numeros'].length) {
        if (!resultat.direct) {
            conteneur_resultats_recherche
                .append(jQuery('<div>')
                    .addClass('resultat_recherche list-group-item')
                    .text(l10n_recherche['recherche_magazine_selectionnez_une_histoire'])
                );
        }
        jQuery.each(resultat['liste_numeros'], function(i, magazine) {
            if (resultat.direct) {
                magazine.magazine_numero = magazine.pays
                    + '/' + magazine.magazine_numero
                        .replace(/[+]+/g, '.')
                        .replace(/[ ]+/g, '');

                var numero = magazine.magazine_numero.split(new RegExp('\\.', 'g'))[1];

                conteneur_resultats_recherche
                    .append(jQuery('<div>')
                        .addClass('magazine_trouve list-group-item')
                        .attr({id: 'magazine_' + magazine.magazine_numero})
                        .append(jQuery('<div>')
                            .addClass(magazine.etat ? ' details_numero gauche num_' + magazine.etat : ''))
                        .append(jQuery('<img>', {
                            src: 'images/flags/' + magazine.pays + '.png',
                            alt: magazine.pays
                        }))
                        .append(magazine.titre + ' ' + numero));
            }
            else {
                var histoire = resultat['liste_numeros'][i];

                conteneur_resultats_recherche
                    .append(jQuery('<div>')
                        .addClass('histoire_trouvee list-group-item')
                        .attr({id: 'histoire_' + histoire.code})
                        .append(histoire.titre));
            }
            i++;
        });

        if (resultat.limite) {
            conteneur_resultats_recherche
                .append(jQuery('<div>').addClass('resultat_recherche list-group-item')
                    .append(l10n_recherche['recherche_magazine_resultats_nombreux_1']));
            conteneur_resultats_recherche
                .append(jQuery('<div>').addClass('resultat_recherche list-group-item')
                    .append(l10n_recherche['recherche_magazine_resultats_nombreux_2']));
        }
    }
    else {
        conteneur_resultats_recherche
            .append(jQuery('<div>')
                .addClass('resultat_recherche list-group-item')
                .append(resultat.direct && est_contexte_bibliotheque
                    ? l10n_recherche['recherche_magazine_histoire_non_possedee']
                    : l10n_recherche['recherche_magazine_aucun_resultat']));
    }

    jQuery('#conteneur_resultat_recherche').remove();
    element_recherche_histoire.append(conteneur_resultats_recherche);

    jQuery('.magazine_trouve').on('click', function () {
        var element = jQuery(this);
        var pays_magazine = element.attr('id').substring('magazine_'.length, element.attr('id').length);
        if (est_contexte_bibliotheque) {
            jQuery('.fleche_position').remove();
            var tranche_trouvee = jQuery('#' + pays_magazine);
            indiquer_numero(tranche_trouvee, ['haut', 'bas']);
        }
        else {
            var publicationcode = pays_magazine.replace('.', '/').split('/');
            afficher_numeros(publicationcode[0], publicationcode[1], publicationcode[2]);
        }
    });

    jQuery('.histoire_trouvee').on('click', function (e) {
	    var element = jQuery(this);
        var storycode = element.attr('id').substring('histoire_'.length, element.attr('id').length);
        element_recherche_input
            .attr({'data-code': 'code=' + storycode, disabled: 'disabled'})
            .before(
                jQuery('<span>')
                    .addClass('conteneur_label_histoire label label-default')
                    .append(jQuery('<span>').addClass('label_histoire').text(element.innerText))
                    .append(jQuery('<a>')
                        .append(jQuery('<i>').addClass('remove glyphicon glyphicon-remove-sign glyphicon-white'))
                        .on('click', function (e) {
                            element_recherche_histoire.find('>.conteneur_label_histoire').remove();
                            element_recherche_histoire.find('>#conteneur_resultat_recherche').remove();
                            element_recherche_input.attr({
                                disabled: false,
                                'data-code': false
                            }).focus();
                            e.stopPropagation();
                        }))
                    .on('click', function (e) {
                        recherche_histoire();
                        e.stopPropagation();
                    })
            )
            .val('');
        jQuery('.histoire_trouvee, .resultat_recherche').remove();
        recherche_histoire();
        e.stopPropagation();
    });

    derniere_action_recherche = moment();
    recherche_en_cours = false;
}

function indiquer_numero(element, positions_fleches) {
	var body = jQuery('#body');
    jQuery('.fleche_position').remove();

    var offset = element.offset();
    var haut = offset.top;
    var gauche = offset.left;

    var css, src;
    var cote_fleche= 16;

	for (var i=0; i<positions_fleches.length; i++) {
        var position_fleche = positions_fleches[i];

        switch(position_fleche) {
            case 'gauche':
                css = {top: (haut + element.height()/2 - cote_fleche/2 ) + 'px', left: (gauche - cote_fleche) + 'px'};
                src = 'images/icones/arrow_right.png';
            break;

            case 'haut':
                css = {top: (haut - cote_fleche) + 'px', left: (gauche + element.width()/2 - cote_fleche/2) + 'px'};
                src = 'images/icones/arrow_down.png';
            break;

            case 'bas':
                css = {top: (haut + element.height()) + 'px', left: (gauche + element.width()/2 - cote_fleche/2) + 'px'};
                src = 'images/icones/arrow_up.png';
            break;
        }

        body
            .append(
                jQuery('<img>', {src: src})
                    .css(css)
                    .addClass('fleche_position')
            )
    }
    window.scrollTo(
    	gauche - body.width()  / 2 + element.width() /2,
	    haut   - body.height() / 2 + element.height()/2
    );
}

function recherche_histoire(val_recherche) {
    var element_recherche_histoire = jQuery('#recherche_histoire');
    var element_recherche_input = element_recherche_histoire.find('>input');
    var recherche_forcee = true;

    if (!val_recherche) {
        recherche_forcee = false;
        val_recherche=element_recherche_input.val() || element_recherche_input.attr('data-code');
    }

    if (val_recherche && val_recherche.length >= 3 ) {
        var resultat = localStorage && JSON.parse(localStorage.getItem('get_magazines_histoire.'+val_recherche));
        if (resultat) {
            traiter_resultats_recherche_histoire(resultat, element_recherche_histoire, element_recherche_input);
        }
        else {
            if (!recherche_en_cours && (recherche_forcee || !derniere_action_recherche || moment().diff(derniere_action_recherche, 'milliseconds') > 200)) {
                recherche_en_cours = true;

                jQuery.post('Inducks.class.php', {
                    data: {
	                    get_magazines_histoire: true,
	                    histoire: val_recherche,
	                    recherche_bibliotheque: (est_contexte_bibliotheque ? 'true' : 'false')
                    },
                    success:function(resultat) {
                        localStorage && localStorage.setItem('get_magazines_histoire.'+val_recherche, JSON.stringify(resultat));

                        traiter_resultats_recherche_histoire(resultat, element_recherche_histoire, element_recherche_input);
                    }
                });
            }
            else {
                recherches_reportees.push(val_recherche);
                setTimeout(function() {
                    if (recherches_reportees.length) {
                        var derniere_recherche_reportee = recherches_reportees[recherches_reportees.length-1];
                        recherches_reportees = [];
                        recherche_histoire(derniere_recherche_reportee);
                    }
                }, 250);
            }
        }
    }
    derniere_action_recherche = moment();
}

function init_observers_tranches() {
    jQuery('.tranche')
	    .on('mousedown', function() {
			tranche_bib=jQuery(this);
			ouvrir_tranche();
		})
	    .on('mouseover', function() {
            if (!action_en_cours && !couverture_ouverte) {
	            ouvrirInfoBulleEffectif(jQuery(this));
            }
        }
    );

    jQuery('#body').on('hidden.bs.tooltip', function() {
        jQuery('.tooltip:not(.in)').remove();
    });
}

var isOutOfEdgesAndPopover = true;

function ouvrirInfoBulleEffectif(tranche) {
    jQuery('.popover').popover('destroy');
    isOutOfEdgesAndPopover=false;

    var numero_bulle=getInfosNumero(tranche.id);

    var titre_bulle = jQuery('.issue_title.template').clone(true).removeClass('template');
    titre_bulle.remplirTitreNumero(numero_bulle);

    var contenu_bulle = jQuery('.tooltip_edge_content.template').clone(true).removeClass('template');
    contenu_bulle
        .find('.has-no-edge')
            .toggle(jQuery(tranche).data('edge') === 0)
            .find('.is-not-bookcase-share')
                .toggle(!est_partage_bibliotheque);

    if (!est_partage_bibliotheque) {
        var progressWrapperTemplate = contenu_bulle.find('.progress-wrapper.template');
        progressWrapperTemplate.ajouterPropositionPhoto(progressWrapperTemplate, numero_bulle, true);
    }

    jQuery(tranche)
        .popover({
            container: 'body',
            content: contenu_bulle.html(),
            title: titre_bulle.html(),
            placement: 'top',
            position: 'in right',
            animation: false,
            html: true
        })
        .popover('show')
        .mouseout(function() {
            hidePopoverIfStillOutOfFocusAfterTimeout(500);
        });

    jQuery('.popover')
        .mouseover(function() {
            isOutOfEdgesAndPopover=false;
        })
        .mouseout(function() {
            hidePopoverIfStillOutOfFocusAfterTimeout(500);
        });
}

function hidePopoverIfStillOutOfFocusAfterTimeout(timeout) {
    isOutOfEdgesAndPopover = true;
    setTimeout(function() {
        if (isOutOfEdgesAndPopover) {
            jQuery('.popover').popover('destroy');
        }
    }, timeout);
}

function getInfosNumero (edgeId) {
    var pays__magazine_numero=edgeId.split('/');
    var magazine_numero=pays__magazine_numero[1].split('.');
	return {
		Pays: pays__magazine_numero[0],
		Magazine: magazine_numero[0].toLowerCase(),
		Nom_magazine: noms_magazines[infos.Pays + '/' + infos.Magazine.toUpperCase()] || '',
		Numero: magazine_numero[1]
	};
}

function getScreenCenterY() {
    return jQuery('#body').offset().top + jQuery(window).height()/2;
}

function getScreenCenterX() {
    return jQuery('#body').offset().left + jQuery(window).height()/2;
}

function getPopulariteNumero(data) {
    return (
        popularite_numeros.filter(function(numero) {
            return numero.Pays === data.Pays
                && numero.Magazine.toLowerCase() === data.Magazine
                && numero.Numero === data.Numero
        })[0]
        || { Popularite: 0 }
    )
    .Popularite;
}

jQuery.fn.afficher_medailles = function(niveau_actuel) {
    if (niveau_actuel > 0) {
        jQuery(this)
            .find('.medaille_objectif.gauche')
                .attr({src: "images/medailles/Photographe_" + niveau_actuel + "_fond.png"});
    }

    var niveau_objectif = niveau_actuel + 1;

    if (niveau_objectif < 3) {
        jQuery(this)
            .find('.medaille_objectif.droite')
                .attr({src: "images/medailles/Photographe_" + niveau_objectif + "_fond.png"});
    }
    return this;
};

jQuery.fn.ajouterPropositionPhoto = function(progressWrapperTemplate, data, after) {
    var element = jQuery(this);

    var points_extra = getPopulariteNumero(data);
    var progressWrapper = progressWrapperTemplate
        .clone(true)
        .removeClass('template');

    if (after) {
        element.after(progressWrapper);
    }
    else {
        element.append(progressWrapper);
    }

    var points_niveau_actuel=niveaux_medailles[niveau_actuel] || 0;
    var points_niveau_objectif=niveaux_medailles[niveau_actuel+1];

    progressWrapper
        .afficher_medailles(niveau_actuel);

    progressWrapper
        .siblings('.progress-info')
            .find('.progress-extra-points')
                .text(points_extra);

    progressWrapper
        .find('.progress-current')
            .css({width: (100*(user_points-points_niveau_actuel)/(points_niveau_objectif-points_niveau_actuel)) + '%'});

    progressWrapper
        .find('.progress-extra')
        .css({width: (points_niveau_objectif ? (100*points_extra/(points_niveau_objectif-points_niveau_actuel)):0) + '%'})
            .text('+ ' + points_extra + ' points');

    return progressWrapper;
};

jQuery.fn.remplirTitreNumero = function(numero_bulle) {
    var element = jQuery(this);
    element
        .find('img.flag')
            .attr({src: 'images/flags/'+numero_bulle.Pays+'.png'});
    element
        .find('.country')
            .text(numero_bulle.Pays);
    element
        .find('.publication_name')
            .text(numero_bulle.Nom_magazine);
    element
        .find('.issuenumber')
            .text(numero_bulle.Numero);

    return this;
};
