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
var l10n_recherche = [
    'recherche_magazine_aucun_resultat', 'recherche_magazine_histoire_non_possedee',
    'recherche_magazine_resultats_nombreux_1','recherche_magazine_resultats_nombreux_2',
    'recherche_magazine_selectionnez_une_histoire'
];

function ouvrir_tranche() {
    if (action_en_cours || extrait_courant>0)
        return;
    jQuery('.popover').popover('destroy');

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
        pays: infos['pays'],
        magazine: infos['magazine'],
        numero: infos['numero']
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
    if (suivant && suivant.className.indexOf('tranche') == -1) {
        if (tranche2.up('#bibliotheque')) { // Contexte biblioth�que
            nb_etageres_terminees++;
            $('pct_bibliotheque').setStyle({'width': parseInt(100 * nb_etageres_terminees / nb_etageres) + '%'});
            var tranche_suivante = suivant.next().next();
            if (tranche_suivante.className.indexOf('tranche') == -1) {
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
    l10n_action('fillArray',l10n_recherche,'l10n_recherche', function() {
        var element_recherche_bibliotheque = $('recherche_bibliotheque');
        var conteneur_bibliotheque = $('bibliotheque');

        if (element_recherche_bibliotheque) {
           if (conteneur_bibliotheque) {
               afficher_lien_partage();
               element_recherche_bibliotheque.setStyle({
                   left: ($('contenu').cumulativeOffset()['left']
                       +parseInt(conteneur_bibliotheque.getStyle('width').substring(0,conteneur_bibliotheque.getStyle('width').length-2))-330) +'px',
                   display: 'block'});
           }

           element_recherche_bibliotheque.down('button').observe('click',recherche);
       }
        $('contenu').observe('click', function() {
            $$('.magazine_trouve, .histoire_trouvee, .resultat_recherche').invoke('remove');
        });

        $$('.toggler_aide_recherche_magazine').invoke(
            'observe',
            'click',
            function() {
                $$('#aide_recherche_magazine, .toggler_aide_recherche_magazine').invoke('toggleClassName','cache');
            }
        );
    });
}

var zone_partager_bibliotheque;

function afficher_lien_partage() {
	zone_partager_bibliotheque = $('partager_bibliotheque');
	zone_partager_bibliotheque.removeClassName('cache');
	$('partager_bibliotheque_lien').observe('click', function() {
		zone_partager_bibliotheque.addClassName('cache');
		new Ajax.Request('Edge.class.php', {
			method: 'post',
			parameters: 'partager_bibliotheque=true',
			onSuccess: function (transport) {
				zone_partager_bibliotheque.update(transport.responseText);
				zone_partager_bibliotheque.removeClassName('cache');

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

function recherche() {
    var element_recherche_bibliotheque = $('recherche_bibliotheque');

    $$('.magazine_trouve, .histoire_trouvee, .fleche_position, .resultat_recherche').invoke('remove');
    $$('.magazine_trouve, .histoire_trouvee').invoke('stopObserving','click');
    var val_recherche=element_recherche_bibliotheque.down('input').value;
    element_recherche_bibliotheque.down('button').update(new Element('img',{'src':'loading.gif'}));
    var recherche_bibliotheque=($('bibliotheque') == null) ? 'false':'true';

    new Ajax.Request('Inducks.class.php', {
        method: 'post',
        parameters:'get_magazines_histoire=true&histoire='+val_recherche+'&recherche_bibliotheque='+recherche_bibliotheque,
        onSuccess:function(transport) {
            element_recherche_bibliotheque.down('button').update('OK');
            var resultat=transport.headerJSON;

            var conteneur_resultats_recherche = new Element('div')
                .writeAttribute({id: 'conteneur_resultat_recherche'});
            element_recherche_bibliotheque.insert(conteneur_resultats_recherche);

            if (resultat['liste_numeros'].length) {
                if (!resultat.direct) {
                    conteneur_resultats_recherche
                        .insert(new Element('div')
                            .addClassName('resultat_recherche')
                            .insert(l10n_recherche['recherche_magazine_selectionnez_une_histoire'])
                        );
                }
                var i=0;
                while (resultat['liste_numeros'][i]) {
                    if (resultat.direct) {
                        var magazine=resultat['liste_numeros'][i];

                        magazine.magazine_numero=magazine.pays
                                            +'/'+magazine.magazine_numero
                                                .replace(/[+]+/g,'.')
                                                .replace(/[ ]+/g,'');

                        var numero=magazine.magazine_numero.split(new RegExp('\\.','g'))[1];

                        conteneur_resultats_recherche
                            .insert(new Element('div')
                                .addClassName('magazine_trouve')
                                .writeAttribute({'id': 'magazine_' + magazine.magazine_numero})
                                .insert(new Element('img', {
                                    src: 'images/flags/' + magazine.pays + '.png',
                                    alt: magazine.pays
                                }))
                                .insert(magazine.titre + ' ' + numero));
                    }
                    else {
                        var histoire=resultat['liste_numeros'][i];

                        conteneur_resultats_recherche
                            .insert(new Element('div')
                                .addClassName('histoire_trouvee')
                                .writeAttribute({id: 'histoire_'+histoire.code})
                                .insert(histoire.titre));
                    }
                    i++;
                }

                if (resultat.limite) {
                    conteneur_resultats_recherche
                        .insert(new Element('div').addClassName('resultat_recherche')
                            .insert(l10n_recherche['recherche_magazine_resultats_nombreux_1']));
                    conteneur_resultats_recherche
                        .insert(new Element('div').addClassName('resultat_recherche')
                            .insert(l10n_recherche['recherche_magazine_resultats_nombreux_2']));
                }

                $$('.magazine_trouve').invoke('observe','click',function(event) {
                    var element=Event.element(event);
                    var pays_magazine=element.readAttribute('id').substring('magazine_'.length, element.readAttribute('id').length);
                    if (recherche_bibliotheque=='true') {
                        $$('.fleche_position').invoke('remove');
                        var tranche_trouvee=$(pays_magazine);
                        var offset=tranche_trouvee.cumulativeOffset();
                        var haut=offset['top']-16;
                        var gauche=offset['left'];
                        $('body').insert(new Element('img',{'src':'images/icones/arrow_down.png'})
                            .setStyle({'top':(haut)+'px','left':(gauche-tranche_trouvee.width/2)+'px'})
                            .addClassName('fleche_position'));

                        $('body').insert(new Element('img',{'src':'images/icones/arrow_up.png'})
                            .setStyle({'top':(haut+16+tranche_trouvee.height)+'px','left':(gauche-tranche_trouvee.width/2)+'px'})
                            .addClassName('fleche_position'));
                        window.scrollTo(gauche,haut);
                    }
                    else {
                        location.replace('?action=gerer&onglet=ajout_suppr&onglet_magazine='+pays_magazine.replace('.','#'));
                    }
                });
                $$('.histoire_trouvee').invoke('observe','click',function(event) {
                    var element=Event.element(event);
                    var index=element.readAttribute('id').substring('histoire_'.length,element.readAttribute('id').length);
                    $('recherche_bibliotheque').down('input').value='code='+index;
                    $$('.histoire_trouvee, .resultat_recherche').invoke('remove');
                    recherche();
                });
            }
            else {
                conteneur_resultats_recherche
                    .insert(new Element('div')
                        .addClassName('resultat_recherche')
                        .insert(resultat.direct && recherche_bibliotheque
                            ? l10n_recherche['recherche_magazine_histoire_non_possedee']
                            : l10n_recherche['recherche_magazine_aucun_resultat']));
            }
        }
    });
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

var bulle_recente = null;

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

            bulle_recente = tranche.id;
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

var timeout_before_popover_hide= 500;
var popover_id_mouseout_timeout = null;

function ouvrirInfoBulleEffectif(tranche) {
    jQuery('.popover').popover('destroy');
    var numero_bulle=getInfosNumero(tranche.id);

    new Ajax.Request('Edge.class.php', {
        method: 'post',
        parameters:'get_visible=true&est_partage_bibliotheque='+est_partage_bibliotheque+'&debug='+debug
				 +'&numero_bulle_courant='+numero_bulle+'&pays='+numero_bulle['pays']+'&magazine='+numero_bulle['magazine']+'&numero='+numero_bulle['numero'],
        onSuccess:function(transport) {
            if (bulle_recente === tranche.id) {
                var data = transport.responseJSON;

                jQuery(tranche)
                    .popover({
                        container: 'body',
                        content: data.content,
                        title: data.title,
                        trigger: 'hover',
                        placement: 'top',
                        position: 'in right',
                        animation: false,
                        html: true
                    })
                    .popover('show')
                    .on('hide.bs.popover', function(e) {
                        if (popover_id_mouseout_timeout !== tranche.id) {
                            popover_id_mouseout_timeout = tranche.id;

                            setTimeout(function() {
                                if (popover_id_mouseout_timeout) {
                                    popover_id_mouseout_timeout = null;
                                    jQuery(tranche).unbind('hide.bs.popover').popover('hide');
                                }
                            }, timeout_before_popover_hide);

                            e.preventDefault();
                        }
                    });
                jQuery('.popover')
                    .mouseenter(function() {
                        popover_id_mouseout_timeout = null;
                    })
                    .mouseleave(function() {
                        jQuery(tranche).unbind('hide.bs.popover').popover('hide');
                    });
            }
        }
    });

}

function getInfosNumero (texte) {
    var infos=[];
    var pays__magazine_numero=texte.split('/');
    var magazine_numero=pays__magazine_numero[1].split('.');
    infos['pays']=pays__magazine_numero[0];
    infos['magazine']=magazine_numero[0].toLowerCase();
    infos['numero']=magazine_numero[1];
    return infos;
}

function getScreenCenterY() {
    return document.viewport.getScrollOffsets().top + document.viewport.getHeight()/2;
}

function getScreenCenterX() {
    return document.body.clientWidth/2;
}