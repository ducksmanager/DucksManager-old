var tranche_en_cours;
var tranche_bib;
var current_couv;
var current_animation;
var largeur_image;
var hauteur_image;
var action_en_cours=false;
var couverture_ouverte=false;
var ouvrirApres=false;
var largeur_section;
var hauteur_section;
var couverture;
var ouverture_couverture;
var hauteur_etage;
var nb_etageres;
var nb_etageres_terminees;
var bulle=null;
var extraits;
var extrait_courant;
var chargement_extrait=false;
var noms_magazines = [];
var popularite_numeros = [];
var user_points = 0;
var niveau_actuel = 0;
var niveaux_medailles = {};

var l10n_recherche = [
    'recherche_magazine_aucun_resultat', 'recherche_magazine_histoire_non_possedee',
    'recherche_magazine_resultats_nombreux_1','recherche_magazine_resultats_nombreux_2',
    'recherche_magazine_selectionnez_une_histoire'
];

function ouvrir_tranche() {
    if (action_en_cours || extrait_courant>0)
        return;
    jQuery('.popover').popover('destroy');
    $$('.fleche_position').invoke('remove');

    extraits=[];
    extrait_courant=-1;
    ouverture_couverture=true;
    if (couverture_ouverte && tranche_bib !== tranche_en_cours) {
        ouvrirApres=true;
        fermer();
        return;
    }
    $('infobulle') && $('infobulle').remove();
    bulle=null;
    action_en_cours=true;
    var infos=getInfosNumero(tranche_bib.id);
    largeur_image=tranche_bib.width;
    hauteur_image=tranche_bib.height;
    couverture=new Image();
    tranche_en_cours=tranche_bib.cloneNode(true);
    tranche_en_cours.setStyle({'zIndex':500,'position':'absolute',
                              'left':getScreenCenterX()+'px','top':(getScreenCenterY()-hauteur_image/2)+'px'})
                   .setOpacity(0);
    $('bibliotheque').insert(tranche_en_cours);
    new Effect.Parallel([
        new Effect.Opacity(tranche_bib,{'from':1, 'to':0, sync: true}),
        new Effect.Opacity(tranche_en_cours, {'from':0, 'to':1, sync:true})
    ], {
        duration: 0.5,
        afterFinish:function() {
            current_animation=new Element('div',{'id':'animation'})
                .setStyle({'position':'absolute', 'left':getScreenCenterX()+'px','top':(getScreenCenterY()-hauteur_image/2)+'px', 'zIndex':600})
                .update(new Element('img',{'src':'loading.gif'}));
            $('bibliotheque').insert(current_animation);
        }});

    jQuery.post('Inducks.class.php', {
        get_cover: 'true',
        debug: debug,
        pays: infos.Pays,
        magazine: infos['magazine'],
        numero: infos.Numero
    })
        .done(function (data) {
            $('infobulle') && $('infobulle').remove();
            if (data) {
                couverture_ouverte = true;
                var i = 0;
                while (data[i]) {
                    extraits[i] = data[i];
                    i++;
                }
                couverture.src = data.cover;
                current_couv = new Element('div', {'id': 'page_droite_avant'})
                    .setStyle({
                        'position': 'absolute',
                        'height': hauteur_image + 'px',
                        'width': parseInt(couverture.width * (hauteur_image / couverture.height)) + 'px',
                        'display': 'none',
                        'left': (getScreenCenterX() + tranche_en_cours.width) + 'px',
                        'top': (getScreenCenterY() - hauteur_image / 2) + 'px'
                    })
                    .addClassName('page_avant');
                var current_couv_im = new Element('img', {
                    'id': 'page_droite_avant_im',
                    'src': couverture.src,
                    'height': '100%',
                    'width': parseInt(couverture.width * (hauteur_image / couverture.height)) + 'px'
                });
                current_couv.update(current_couv_im);
                $('body').insert(current_couv);
                current_couv_im.observe('click', fermer_tranche);

                current_couv_im.observe('load', function () {
                    current_couv.setStyle({'width': parseInt(couverture.width * (hauteur_image / couverture.height)) + 'px'});
                    if (!ouverture_couverture)
                        return;

                    tranche_en_cours.setStyle({
                        'width': tranche_en_cours.width + 'px',
                        'height': tranche_en_cours.height + 'px'
                    });
                    new Effect.Parallel([
                        new Effect.Morph(current_couv, {
                            'width': parseInt(couverture.width / (hauteur_image / couverture.height)) + 'px',
                            sync: true
                        }),
                        new Effect.BlindRight(current_couv, {sync: true}),
                        new Effect.Move(current_couv, {
                            'mode': 'absolute',
                            'x': getScreenCenterX(),
                            'y': getScreenCenterY() - hauteur_image / 2,
                            sync: true
                        }),
                        new Effect.BlindLeft(tranche_en_cours, {sync: true})
                    ], {
                        duration: 1,
                        afterFinish: function () {
                            ouverture_couverture = false;
                            $('animation') && $('animation').remove();

                            if (extraits.length > 0 && !$('lien_apercus')) {
                                creer_div_apercus();
                            }
                        }
                    });
                    $('animation') && $('animation').remove();
                    action_en_cours = false;
                });
            }
        });
}

function fermer_tranche() {
    ouvrirApres=false;
    fermer();
}

function creer_div_apercus() {
    var page_suivante=new Element('div',{'id':'page_suivante'})
                    .setStyle({'left':(getScreenCenterX()+$('page_droite_avant_im').width)+'px','top':getScreenCenterY()+'px'})
                    .addClassName('lien_apercus');

    var page_precedente=new Element('div',{'id':'page_precedente'})
                    .setStyle({'right':(getScreenCenterX()+$('page_droite_avant_im').width)+'px','top':getScreenCenterY()+'px'})
                    .addClassName('lien_apercus');

    var page_gauche_arriere=new Element('div', {'id':'page_gauche_arriere'})
                    .setStyle({'position':'absolute','display':'block','width':getLargeur(),'height':hauteur_image+'px',
                               'right':getScreenCenterX()+'px','top':(getScreenCenterY()-hauteur_image/2)+'px'})
                    .addClassName('page_arriere');

    var page_gauche_avant=new Element('div', {'id':'page_gauche_avant'})
                    .setStyle({'position':'absolute','display':'block','width':'0px','height':hauteur_image+'px',
                               'right':getScreenCenterX()+'px','top':(getScreenCenterY()-hauteur_image/2)+'px'})
                    .addClassName('page_avant');

    var page_droite_arriere=new Element('div', {'id':'page_droite_arriere'})
                    .setStyle({'position':'absolute','display':'block','width':getLargeur(),'height':hauteur_image+'px',
                               'left':getScreenCenterX()+'px','top':(getScreenCenterY()-hauteur_image/2)+'px'})
                    .addClassName('page_arriere');

    $('body').insert(page_suivante)
             .insert(page_gauche_arriere).insert(page_gauche_avant)
             .insert(page_droite_arriere);
    page_suivante.observe('click',function() {
        if (chargement_extrait)
            return;
        chargement_extrait=true;
        if (extrait_courant>=extraits.length) {
            back_to_cover();
        }
        else {
            if (extraits[extrait_courant].page % 2 === 1) { // Page impaire
                maj_page('page_gauche_arriere','page_invisible');
                $('page_gauche_arriere')
                    .setStyle({'width':'0px'});
                intervertir_page('gauche');

                maj_page('page_droite_arriere',extraits[extrait_courant].url);
                $('page_droite_arriere')
                    .setStyle({'display':'block'});

                $('page_droite_arriere_im').observe('load',function () {
                    new Effect.BlindLeft('page_droite_avant',{
                    duration:0.75,
                    afterFinish:function() {
                        new Effect.Morph('page_gauche_avant',{style:'width:'+getLargeur()
                        });
                        intervertir_page('droite');
                        $('page_gauche_avant').observe('click',back_to_cover);
                        $('page_droite_avant_im').observe('click',back_to_cover);
                        extrait_courant++;
                        maj_div_apercus();
                    }
                    });
                });
           }
           else { //Page paire
                maj_page('page_gauche_arriere',extraits[extrait_courant].url);
                $('page_gauche_arriere_im').setStyle({'height':hauteur_image+'px','width':'0px'});
                intervertir_page('gauche');
                $('page_gauche_avant').setStyle({'width':getLargeur()+'px'});
                maj_page('page_droite_arriere','page_invisible');
                $('page_droite_arriere')
                    .setStyle({'display':'block'});

                $('page_gauche_avant_im').observe('load',function () {
                    new Effect.Parallel([
                        new Effect.BlindLeft($('page_droite_avant'), {sync:true})
                        ], {
                        duration: 0.75,
                        afterFinish:function() {
                            new Effect.Morph('page_gauche_avant_im',{'style':'width:'+getLargeur()});
                            intervertir_page('droite');
                            $('page_gauche_avant_im').observe('click',back_to_cover);
                            $('page_droite_avant').observe('click',back_to_cover);
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
    return $('page_droite_avant').getStyle('width')==='0px'
            ?$('page_droite_arriere').getStyle('width')
            :$('page_droite_avant').getStyle('width');
}
function intervertir_page(direction) {
    $('page_'+direction+'_avant').writeAttribute({'id':'page_'+direction}).addClassName('page_arriere').removeClassName('page_avant');
    if ($('page_'+direction+'_avant_im')) {
        $('page_'+direction+'_avant_im').writeAttribute({'id':'page_'+direction+'_im'});
    }
    $('page_'+direction+'_arriere').writeAttribute({'id':'page_'+direction+'_avant'}).removeClassName('page_arriere').addClassName('page_avant');
    if ($('page_'+direction+'_arriere_im')) {
        $('page_'+direction+'_arriere_im').writeAttribute({'id':'page_'+direction+'_avant_im'})
    }
    $('page_'+direction).writeAttribute({'id':'page_'+direction+'_arriere'});
    if ($('page_'+direction+'_im')) {
        $('page_'+direction+'_im').writeAttribute({'id':'page_'+direction+'_arriere_im'});
    }
}

function maj_page(id_page,maj) {
    if (maj=='page_invisible') {
        $(id_page).update()
                  .addClassName('page_invisible');
    }
    else {
        $(id_page).update(new Element('img',{'id':id_page+'_im','src':maj}))
                  .removeClassName('page_invisible');
        if (id_page.indexOf('gauche')!=-1)
            $(id_page+'_im').setStyle({'float':'right'});
    }
}

function maj_div_apercus() {
    if (extrait_courant>=extraits.length)
        $('page_suivante').update('Fermer');
    else
        $('page_suivante')
            .update(extraits[extrait_courant].page<0?'Suivante':'Page '+extraits[extrait_courant].page);
   chargement_extrait=false;
}

function back_to_cover() {
    $('page_gauche_arriere') && $('page_gauche_arriere').remove();
    $('page_suivante').remove();

    maj_page('page_droite_arriere',couverture.src);
    $('page_droite_arriere_im').setStyle({'width':'0px'});
    $('page_droite_arriere').setStyle({'display':'block'});
    intervertir_page('droite');
    new Effect.BlindLeft('page_gauche_avant', {
        afterFinish:function() {
            $('page_gauche_avant') && $('page_gauche_avant').remove();
            new Effect.Morph('page_droite_avant_im',{style:'width:'+getLargeur(),
                afterFinish:function() {
                    $('page_droite_arriere') && $('page_droite_arriere').remove();
                    $('page_droite_avant').observe('click', fermer_tranche);
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
    var largeur=getLargeur();
    $('page_suivante') && $('page_suivante').remove();
    $('page_gauche_avant') && $('page_gauche_avant').remove();
    $('page_gauche_arriere') && $('page_gauche_arriere').remove();
    $('page_droite_arriere') && $('page_droite_arriere').remove();
    $('page_droite_avant_im').setStyle({'width':largeur});
    new Effect.Parallel([
        new Effect.BlindLeft($('page_droite_avant'), {sync:true}),
        new Effect.Move($('page_droite_avant'), {'mode':'absolute', 'x':(getScreenCenterX()+tranche_bib.width), 'y':getScreenCenterY()-hauteur_image/2, sync:true}),
        new Effect.BlindRight(tranche_en_cours, {sync:true})
    ], {
        duration: 1,
        afterFinish:function() {
            new Effect.Parallel([
                new Effect.Opacity(tranche_en_cours, {'from':1, 'to':0, sync:true}),
                new Effect.Opacity(tranche_bib,{'from':0, 'to':1, sync: true})
            ], {
                duration: 0.5,
                afterFinish:function() {
                    $('page_droite_avant').remove();
                    $$('.lien_apercus').invoke('remove');
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

	var conteneur=$('conteneur_bibliotheque');
	var section=$('bibliotheque');
    section.observe('mousedown', function() {
        jQuery('.popover').popover('destroy');
    });

	largeur_section=section.clientWidth;
	hauteur_section=section.clientHeight;
	$('pourcentage_collection_visible').setStyle({'display':'none'});
	l10n_action('remplirSpan','pourcentage_collection_visible');

	new Ajax.Request('Edge.class.php', {
		method: 'post',
		parameters: 'get_bibliotheque=true&largeur='+largeur_section+'&hauteur='+hauteur_section
				  +'&user_bibliotheque='+user_bibliotheque+'&cle_bibliotheque='+cle_bibliotheque,
		onSuccess:function(transport) {
			if (!!transport.responseJSON.erreur) {
				conteneur.update(transport.responseJSON.erreur);
			}
			else {
                new Ajax.Request('Edge.class.php', {
                    method: 'post',
                    parameters: 'get_popularite_numeros=true',
                    onSuccess:function(transport) {
                        popularite_numeros = transport.responseJSON.popularite_numeros;

                        charger_points_utilisateur(function() {
                            afficher_proposition_photos_tranches();
                        });
                    }
                });

                noms_magazines = transport.responseJSON.noms_magazines;
				var textures = transport.responseJSON.textures;

				var element_bibliotheque = $('bibliotheque');
				element_bibliotheque.update(transport.responseJSON.contenu);
				element_bibliotheque.setStyle({
					'width': $('largeur_etagere').readAttribute('name') + 'px',
					'backgroundImage': 'url(\'edges/textures/' + textures[0].texture + '/' + textures[0].sous_texture + '.jpg\')'
				});
				$('titre_bibliotheque').update(transport.responseJSON.titre);
				$('pourcentage_collection_visible').setStyle({'display': 'inline'});
				$('pcent_visible').update($('nb_numeros_visibles').readAttribute('name'));
				var premiere_tranche = element_bibliotheque.down(2);
				hauteur_etage = $('hauteur_etage').readAttribute('name');
				nb_etageres = $$('.etagere').length;
				nb_etageres_terminees = 1;
				element_conteneur_bibliotheque = element_bibliotheque;
				charger_tranche(premiere_tranche);
			}
		}
	});
}

function charger_tranche(tranche) {
    tranche.observe('load',charger_tranche_suivante);

    tranche.observe('error',charger_tranche_suivante);
    var lettre_rand=String.fromCharCode(65+Math.floor(Math.random() * 25));
    var src=tranche.name.replace(new RegExp('([^/]+)/','g'),('$1/gen/'));
    if (src.indexOf('gen')!=-1) {
        var src_similaires=element_conteneur_bibliotheque.select('[src*="'+src+'"]').pluck('src');
        if (src_similaires.length >0)
            tranche.src=src_similaires[0];
        else
            tranche.src='edges/'+src+'.png?'+lettre_rand;
    }
    else
        tranche.src=src;
}

function charger_tranche_suivante(element) {
    var tranche2=Event.element(element);
    var suivant=tranche2.next();

    if (suivant && suivant.className.indexOf('tranche') === -1) {
        if (tranche2.up('#bibliotheque')) { // Contexte biblioth�que
            nb_etageres_terminees++;
            $('pct_bibliotheque').setStyle({'width': parseInt(100 * nb_etageres_terminees / nb_etageres) + '%'});
            var tranche_suivante = suivant.next().next();
            if (tranche_suivante.className.indexOf('tranche') === -1) {
                init_observers_tranches();
                l10n_action('remplirSpan', 'chargement_bibliotheque_termine');
                $('barre_pct_bibliotheque').remove();
                charger_recherche();
            }
            else
                charger_tranche(tranche_suivante);
        }
        else { // Contexte affichage dans les �v�nements r�cents
            callback_tranches_chargees(tranche2.up('.tooltip_content'));
        }
    }
    else {
        charger_tranche(suivant);
    }
}

function charger_recherche() {
    localStorage && localStorage.clear();
    l10n_action('fillArray',l10n_recherche,'l10n_recherche', function() {
        var element_recherche_histoire = $('recherche_histoire');
        var conteneur_bibliotheque = $('bibliotheque');

        if (element_recherche_histoire) {
           if (conteneur_bibliotheque) {
               afficher_lien_partage();

               element_recherche_histoire.setStyle({
                   left: ($('contenu').cumulativeOffset().left
                       +parseInt(conteneur_bibliotheque.getStyle('width').substring(0,conteneur_bibliotheque.getStyle('width').length-2))-330) +'px',
                   display: 'block'});
           }

           element_recherche_histoire.down('button').observe('click', function(e) {
               recherche_histoire();
               e.stopPropagation();
           });
           element_recherche_histoire.down('input')
               .observe('keyup', function(e) {
                   if (/[\-\!\?\. a-z0-9]/i.test(String.fromCharCode(e.which))) {
                       recherche_histoire();
                       e.stopPropagation();
                   }
               })
               .observe('click', function(e) {
                   recherche_histoire();
                   e.stopPropagation();
               });
       }
        $('contenu').observe('click', function() {
            $$('.magazine_trouve, .histoire_trouvee, .resultat_recherche').invoke('remove');
        });
    });
}

function charger_points_utilisateur(callback) {
    callback = callback || function() {};

    new Ajax.Request('Database.class.php', {
        method: 'post',
        parameters: 'database=true&get_points=true',
        onSuccess:function(transport) {
            user_points = transport.responseJSON.points;
            niveaux_medailles = transport.responseJSON.niveaux_medailles;
            for (var i=0; i<niveaux_medailles.length; i++) {
                if (user_points > niveaux_medailles[i]) {
                    niveau_actuel = i;
                }
            }
            callback();
        }
    });
}

var zone_proposition_photos;

function afficher_lien_partage() {
	zone_proposition_photos = $('partager_bibliotheque');
	zone_proposition_photos.removeClassName('cache');
	$('partager_bibliotheque_lien').observe('click', function() {
		zone_proposition_photos.addClassName('cache');
		new Ajax.Request('Edge.class.php', {
			method: 'post',
			parameters: 'partager_bibliotheque=true',
			onSuccess: function (transport) {
				zone_proposition_photos.update(transport.responseText);
				zone_proposition_photos.removeClassName('cache');

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

        tranches_non_pretes = tranches_non_pretes
            .sort(function(tranche1, tranche2) {
                var populariteNumero1 = getPopulariteNumero(getInfosNumero(jQuery(tranche1).attr('id')));
                var populariteNumero2 = getPopulariteNumero(getInfosNumero(jQuery(tranche2).attr('id')));

                return populariteNumero1 < populariteNumero2
                    ? 1
                    : (populariteNumero1 === populariteNumero2 ? 0 : -1);
            })
            .filter(function(index) {
                return index < nb_tranches_affichees;
            })
            .each(function(i) {
                var infosNumero = getInfosNumero(jQuery(this).attr('id'));

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

function traiter_resultats_recherche_histoire(resultat, element_recherche_histoire, element_recherche_input, recherche_bibliotheque) {
    $$('.fleche_position').invoke('remove');
    var conteneur_resultats_recherche = new Element('div')
        .writeAttribute({id: 'conteneur_resultat_recherche'})
        .addClassName('list-group');

    if (resultat['liste_numeros'].length) {
        if (!resultat.direct) {
            conteneur_resultats_recherche
                .insert(new Element('div')
                    .addClassName('resultat_recherche list-group-item')
                    .insert(l10n_recherche['recherche_magazine_selectionnez_une_histoire'])
                );
        }
        var i = 0;
        while (resultat['liste_numeros'][i]) {
            if (resultat.direct) {
                var magazine = resultat['liste_numeros'][i];

                magazine.magazine_numero = magazine.pays
                    + '/' + magazine.magazine_numero
                        .replace(/[+]+/g, '.')
                        .replace(/[ ]+/g, '');

                var numero = magazine.magazine_numero.split(new RegExp('\\.', 'g'))[1];

                conteneur_resultats_recherche
                    .insert(new Element('div')
                        .addClassName('magazine_trouve list-group-item')
                        .writeAttribute({'id': 'magazine_' + magazine.magazine_numero})
                        .insert(new Element('div')
                            .addClassName(magazine.etat ? ' details_numero gauche num_' + magazine.etat : ''))
                        .insert(new Element('img', {
                            src: 'images/flags/' + magazine.pays + '.png',
                            alt: magazine.pays
                        }))
                        .insert(magazine.titre + ' ' + numero));
            }
            else {
                var histoire = resultat['liste_numeros'][i];

                conteneur_resultats_recherche
                    .insert(new Element('div')
                        .addClassName('histoire_trouvee list-group-item')
                        .writeAttribute({id: 'histoire_' + histoire.code})
                        .insert(histoire.titre));
            }
            i++;
        }

        if (resultat.limite) {
            conteneur_resultats_recherche
                .insert(new Element('div').addClassName('resultat_recherche list-group-item')
                    .insert(l10n_recherche['recherche_magazine_resultats_nombreux_1']));
            conteneur_resultats_recherche
                .insert(new Element('div').addClassName('resultat_recherche list-group-item')
                    .insert(l10n_recherche['recherche_magazine_resultats_nombreux_2']));
        }
    }
    else {
        conteneur_resultats_recherche
            .insert(new Element('div')
                .addClassName('resultat_recherche list-group-item')
                .insert(resultat.direct && recherche_bibliotheque
                    ? l10n_recherche['recherche_magazine_histoire_non_possedee']
                    : l10n_recherche['recherche_magazine_aucun_resultat']));
    }

    $('conteneur_resultat_recherche') && $('conteneur_resultat_recherche').remove();
    element_recherche_histoire.insert(conteneur_resultats_recherche);

    $$('.magazine_trouve').invoke('observe', 'click', function (event) {
        var element = Event.element(event);
        var pays_magazine = element.readAttribute('id').substring('magazine_'.length, element.readAttribute('id').length);
        if (recherche_bibliotheque === 'true') {
            $$('.fleche_position').invoke('remove');
            var tranche_trouvee = $(pays_magazine);
            indiquer_numero(tranche_trouvee, ['haut', 'bas']);
        }
        else {
            var publicationcode = pays_magazine.replace('.', '/').split('/');
            afficher_numeros(publicationcode[0], publicationcode[1], publicationcode[2]);
        }
    });

    $$('.histoire_trouvee').invoke('observe', 'click', function (e) {
        var element = Event.element(e);
        var storycode = element.readAttribute('id').substring('histoire_'.length, element.readAttribute('id').length);
        element_recherche_input
            .writeAttribute({'data-code': 'code=' + storycode, disabled: 'disabled'})
            .insert({
                before:
                    new Element('span')
                        .addClassName('conteneur_label_histoire label label-default')
                        .insert(new Element('span').addClassName('label_histoire').update(element.innerText))
                        .insert(new Element('a')
                            .update(new Element('i').addClassName('remove glyphicon glyphicon-remove-sign glyphicon-white'))
                            .observe('click', function (e) {
                                element_recherche_histoire.down('.conteneur_label_histoire').remove();
                                element_recherche_histoire.down('#conteneur_resultat_recherche').remove();
                                element_recherche_input.writeAttribute({
                                    disabled: false,
                                    'data-code': false
                                }).focus();
                                e.stopPropagation();
                            }))
                        .observe('click', function (e) {
                            recherche_histoire();
                            e.stopPropagation();
                        })
            })
            .value = '';
        $$('.histoire_trouvee, .resultat_recherche').invoke('remove');
        recherche_histoire();
        e.stopPropagation();
    });

    derniere_action_recherche = moment();
    recherche_en_cours = false;
}

function indiquer_numero(element, positions_fleches) {
    $$('.fleche_position').invoke('remove');

    var offset = element.cumulativeOffset();
    var haut = offset.top;
    var gauche = offset.left;

    var css, src;
    var cote_fleche= 16;

    for (var i=0; i<positions_fleches.length; i++) {
        var position_fleche = positions_fleches[i];

        switch(position_fleche) {
            case 'gauche':
                css = {top: (haut + element.getHeight()/2 - cote_fleche/2 ) + 'px', left: (gauche - cote_fleche) + 'px'};
                src = 'images/icones/arrow_right.png';
            break;

            case 'haut':
                css = {top: (haut - cote_fleche) + 'px', left: (gauche + element.getWidth()/2 - cote_fleche/2) + 'px'};
                src = 'images/icones/arrow_down.png';
            break;

            case 'bas':
                css = {top: (haut + element.getHeight()) + 'px', left: (gauche + element.getWidth()/2 - cote_fleche/2) + 'px'};
                src = 'images/icones/arrow_up.png';
            break;
        }

        $('body')
            .insert(
                new Element('img', {'src': src})
                    .setStyle(css)
                    .addClassName('fleche_position')
            )
    }
    window.scrollTo(gauche - $('body').getWidth() / 2 + element.getWidth()/2, haut - $('body').getHeight() / 2 + element.getHeight()/2);
}

function recherche_histoire(val_recherche) {
    var recherche_bibliotheque=$('bibliotheque') === null ? 'false':'true';
    var element_recherche_histoire = $('recherche_histoire');
    var element_recherche_input = element_recherche_histoire.down('input');
    var recherche_forcee = true;

    if (!val_recherche) {
        recherche_forcee = false;
        val_recherche=element_recherche_input.value || element_recherche_input.readAttribute('data-code');
    }

    if (val_recherche && val_recherche.length >= 3 ) {
        var resultat = localStorage && JSON.parse(localStorage.getItem('get_magazines_histoire.'+val_recherche));
        if (resultat) {
            traiter_resultats_recherche_histoire(resultat, element_recherche_histoire, element_recherche_input, recherche_bibliotheque);
        }
        else {
            if (!recherche_en_cours && (recherche_forcee || !derniere_action_recherche || moment().diff(derniere_action_recherche, 'milliseconds') > 200)) {
                element_recherche_histoire.down('button').update(new Element('img',{'src':'loading.gif'}));
                recherche_en_cours = true;

                new Ajax.Request('Inducks.class.php', {
                    method: 'post',
                    parameters:'get_magazines_histoire=true&histoire='+val_recherche+'&recherche_bibliotheque='+recherche_bibliotheque,
                    onSuccess:function(transport) {
                        element_recherche_histoire.down('button').update('OK');

                        var resultat=transport.headerJSON;
                        localStorage && localStorage.setItem('get_magazines_histoire.'+val_recherche, JSON.stringify(resultat));

                        traiter_resultats_recherche_histoire(resultat, element_recherche_histoire, element_recherche_input, recherche_bibliotheque);
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

function init_ordre_magazines() {
    Sortable.create('liste_magazines',{
        elements:$$('.magazine_deplacable'),
        handles:$$('.magazine_deplacable .handle'),
        endeffect:function() {
            $$('#liste_magazines .magazine_deplacable').each(function(div) {
                var nouvelle_position=div.previousSiblings().length;
                div.down('input').setValue(nouvelle_position);

            });
        }
    });
}

function init_observers_tranches() {
    $$('.tranche').invoke(
        'observe',
        'mousedown',
        function(event) {
            tranche_bib=Event.element(event);
            ouvrir_tranche();
          }
    );
    $$('.tranche').invoke(
        'observe',
        'mouseover',
        function(event) {
            if (action_en_cours ||couverture_ouverte)
                return;
            var tranche = Event.element(event);
            ouvrirInfoBulleEffectif(tranche);
        }
    );

    jQuery('body').on('hidden.bs.tooltip', function() {
        var tooltips = jQuery('.tooltip').not('.in');
        if (tooltips) {
            tooltips.remove();
        }
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
    var infos=[];
    var pays__magazine_numero=edgeId.split('/');
    var magazine_numero=pays__magazine_numero[1].split('.');
    infos.Pays=pays__magazine_numero[0];
    infos.Magazine=magazine_numero[0].toLowerCase();
    infos.Nom_magazine=noms_magazines[infos.Pays + '/' + infos.Magazine.toUpperCase()] || '';
    infos.Numero=magazine_numero[1];
    return infos;
}

function getScreenCenterY() {
    return document.viewport.getScrollOffsets().top + document.viewport.getHeight()/2;
}

function getScreenCenterX() {
    return document.body.clientWidth/2;
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
    jQuery(this)
        .find('.medaille_objectif.gauche')
            .toggle(niveau_actuel > 0)
            .attr({src: "images/medailles/Photographe_" + niveau_actuel + "_fond.png"});

    var niveau_objectif = niveau_actuel + 1;
    jQuery(this)
        .find('.medaille_objectif.droite')
            .toggle(niveau_objectif < 3)
            .attr({src: "images/medailles/Photographe_" + niveau_objectif + "_fond.png"});
    return this;
};

jQuery.fn.ajouterPropositionPhoto = function(progressWrapperTemplate, data, after) {
    var element = jQuery(this);

    var points_extra = getPopulariteNumero(data);

    if (points_extra) {
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
            .find('.progress-extra-points')
                .text(points_extra);

        progressWrapper
            .find('.progress .progress-extra-points')
                .text('+ ' + points_extra);

        progressWrapper
            .find('.progress-current')
                .css({width: (100*(user_points-points_niveau_actuel)/(points_niveau_objectif-points_niveau_actuel)) + '%'});

        progressWrapper
            .find('.progress-extra')
                .css({width: (100*points_extra/(points_niveau_objectif-points_niveau_actuel)) + '%'})
                .text(points_extra + ' points')
    }

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