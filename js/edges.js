var tranche_en_cours;
var tranche_bib;
var current_couv;
var current_animation;
var largeur_image;
var hauteur_image;
var largeur_couverture;
var action_en_cours=false;
var couverture_ouverte=false;
var ouvrirApres=false;
var largeur_section;
var hauteur_section;
var couverture;
var ouverture_couverture;
var hauteur_etage;
var grossissement;
var nb_etageres;
var nb_etageres_terminees;
var bulle=null;
var numero_bulle=null;
var extraits;
var extrait_courant;
var chargement_extrait=false;

function ouvrir_tranche() {
    if (action_en_cours || extrait_courant>0)
        return;
    extraits=new Array();
    extrait_courant=-1;
    ouverture_couverture=true;
    if (couverture_ouverte && tranche_bib != tranche_en_cours) {
        ouvrirApres=true;
        fermer();
        return;
    }
    if ($('infobulle'))
        $('infobulle').remove();
    bulle=null;
    action_en_cours=true;
    var infos=getInfosNumero(tranche_bib.src);
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
    new Ajax.Request('Inducks.class.php', {
        method: 'post',
        parameters:'get_cover=true&debug='+debug+'&pays='+infos['pays']+'&magazine='+infos['magazine']+'&numero='+infos['numero'],
        onSuccess:function(transport) {
            if (transport.headerJSON==null)
                return;
            couverture_ouverte=true;
            var i=0;
            while (transport.headerJSON[i]) {
                extraits[i]=transport.headerJSON[i];
                i++;
            }
            couverture.src=transport.headerJSON['cover'];
            current_couv=new Element('div', {'id':'page_droite_avant'})
                    .setStyle({'position':'absolute','height':hauteur_image+'px','width':parseInt(couverture.width*(hauteur_image/couverture.height))+'px','display':'none',
                               'left':(getScreenCenterX()+tranche_en_cours.width)+'px','top':(getScreenCenterY()-hauteur_image/2)+'px'})
                    .addClassName('page_avant');
            var current_couv_im=new Element('img',{'id':'page_droite_avant_im','src':couverture.src,'height':'100%','width':parseInt(couverture.width*(hauteur_image/couverture.height))+'px'});
            current_couv.update(current_couv_im);
            $('body').insert(current_couv);
            //current_couv.setStyle({'display':'none'});
            current_couv_im.observe('click', fermer_tranche);
                
            current_couv_im.observe('load',function() {
                current_couv.setStyle({'width':parseInt(couverture.width*(hauteur_image/couverture.height))+'px'});
                if (!ouverture_couverture)
                    return;
                
                new Effect.Parallel([
                    new Effect.Morph(current_couv, {'width':parseInt(couverture.width/(hauteur_image/couverture.height))+'px', sync:true}),
                    new Effect.BlindRight(current_couv, {sync:true}),    
                    new Effect.Move(current_couv, {'mode':'absolute', 'x':getScreenCenterX(), 'y':getScreenCenterY()-hauteur_image/2, sync:true}),
                    new Effect.BlindLeft(tranche_en_cours, {sync:true})
                     ], {
                    duration: 1,
                    afterFinish:function() {
                        if ($('animation'))
                            $('animation').remove();
                        
                        if (extraits.length>0 && !$('lien_apercus')) {
                            creer_div_apercus();
                        }
                    }
                });
                if ($('animation'))
                    $('animation').remove();
                action_en_cours=false;
                ouverture_couverture=false;
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
                    
    $('body')./*insert(page_precedente).*/insert(page_suivante)
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
            if (extraits[extrait_courant].page % 2 == 1) { // Page impaire
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
                        //$('page_droite_avant').remove();
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
                            $('page_gauche_avant_im').observe('click',back_to_cover);
                            new Effect.Parallel([
                                //new Effect.Morph('page_gauche_avant',{'style':'width:'+getLargeur()+'px'}),
                                new Effect.Morph('page_gauche_avant_im',{'style':'width:'+getLargeur()})
                            ]);
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
    return $('page_droite_avant').getStyle('width')=='0px'
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
    /*
    if (extrait_courant==0)
        $('page_precedente').setStyle({'display':'none'});
    else if (extrait_courant==1)
        $('page_precedente').update('Fermer');
    else
        $('page_precedente')
            .update('Page '+extraits[extrait_courant-2].page);
    */
   chargement_extrait=false;
}

function back_to_cover() {
    if ($('page_gauche_arriere'))
        $('page_gauche_arriere').remove();
    //$('page_precedente').remove();
    $('page_suivante').remove();            

    maj_page('page_droite_arriere',couverture.src);
    $('page_droite_arriere_im').setStyle({'width':'0px'});
    $('page_droite_arriere').setStyle({'display':'block'});
    intervertir_page('droite');
    new Effect.BlindLeft('page_gauche_avant', {
        afterFinish:function() {
            $('page_gauche_avant').remove();
            new Effect.Morph('page_droite_avant_im',{style:'width:'+getLargeur(),
                afterFinish:function() {
                    $('page_droite_arriere').remove();
                    $('page_droite_avant').observe('click', fermer_tranche);
                    extrait_courant=-1;
                    creer_div_apercus();
                }
            });
        }
    });
}

function fermer() {
    if (action_en_cours)
        return;
    action_en_cours=true;
    //if ($('page_precedente'))
    //    $('page_precedente').remove();
    if ($('page_suivante'))
        $('page_suivante').remove();
    if ($('page_gauche_avant'))
        $('page_gauche_avant').remove();
    if ($('page_gauche_arriere'))
        $('page_gauche_arriere').remove();
    if ($('page_droite_arriere'))
        $('page_droite_arriere').remove();
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
                    action_en_cours=false;
                    couverture_ouverte=false;
                    if (ouvrirApres==true)
                        ouvrir_tranche();
                }
            });
        }
    });
}

function charger_bibliotheque(texture1, sous_texture1, texture2, sous_texture2, new_grossissement, regen) {
    var section=$('bibliotheque');
    grossissement=new_grossissement;
    largeur_section=section.clientWidth;
    hauteur_section=section.clientHeight;
    l10n_action('remplirSpan','pourcentage_collection_visible');
    new Ajax.Request('edgetest.php', {
        method: 'post',
        parameters:'largeur='+largeur_section+'&hauteur='+hauteur_section+'&texture1='+texture1+'&sous_texture1='+sous_texture1
                  +'&texture2='+texture2+'&sous_texture2='+sous_texture2+'&grossissement='+grossissement+'&regen='+regen,
        onSuccess:function(transport) {
            $('bibliotheque').update(transport.responseText);
            $('bibliotheque').setStyle({'width':$('largeur_etagere').readAttribute('name')+'px',
                                        'backgroundImage':'url(\'edges/textures/'+texture1+'/'+sous_texture1+'.jpg\')'});
            $('pcent_visible').update($('nb_numeros_visibles').readAttribute('name'));
            var premiere_tranche=$('bibliotheque').down(2);
            hauteur_etage=$('hauteur_etage').readAttribute('name');
            nb_etageres=$$('.etagere').length;
            nb_etageres_terminees=1;
            charger_tranche(premiere_tranche);
        }
	});
}

function charger_tranche(tranche) {
    tranche.observe('load',charger_tranche_suivante); 
        
    tranche.observe('error',charger_tranche_suivante);
    
    var src=tranche.name;
    tranche.src=tranche.name;
    tranche.name=src.substring(src.indexOf('/')+1,src.indexOf('/',src.indexOf('/')+1))+'/'
                +src.substring(src.lastIndexOf('/')+1,src.lastIndexOf('.')).replace(new RegExp('\\.','g'),'');
}

function charger_tranche_suivante(element) {
    var tranche2=Event.element(element);
    var suivant=tranche2.next();
    if (suivant.className.indexOf('tranche')==-1) {
        nb_etageres_terminees++;
        $('pct_bibliotheque').setStyle({'width':parseInt(100*nb_etageres_terminees/nb_etageres)+'%'});
        var tranche_suivante=suivant.next().next();
        if (tranche_suivante.className.indexOf('tranche')==-1) {
           init_observers_tranches();
           l10n_action('remplirSpan','chargement_bibliotheque_termine');
           $('barre_pct_bibliotheque').remove();
           charger_recherche();
        }
        else
            charger_tranche(tranche_suivante);
    }
    else {
        charger_tranche(suivant);
    }
}

function charger_recherche() {
   if ($('recherche_bibliotheque')) {
       if ($('bibliotheque')) {
           $('recherche_bibliotheque').setStyle({'left':($('contenu').cumulativeOffset()['left']
                                                         +parseInt($('bibliotheque').getStyle('width').substring(0,$('bibliotheque').getStyle('width').length-2))
                                                         -330)+'px',
                                                 'display':'block'});
       }

       $('recherche_bibliotheque').down('button').observe('click',recherche);
   }
    
}

function recherche() {
    $$('.magazine_trouve, .histoire_trouvee, .fleche_position, .resultat_recherche').invoke('remove');
    $$('.magazine_trouve, .histoire_trouvee').invoke('stopObserving','click');
    var val_recherche=$('recherche_bibliotheque').down('input').value;
    $('recherche_bibliotheque').down('button').update(new Element('img',{'src':'loading.gif'}));
    var recherche_bibliotheque=($('bibliotheque') == null) ? 'false':'true';
    new Ajax.Request('Inducks.class.php', {
        method: 'post',
        parameters:'get_magazines_histoire=true&histoire='+val_recherche+'&recherche_bibliotheque='+recherche_bibliotheque,
        onSuccess:function(transport) {
            $('recherche_bibliotheque').down('button').update('OK');
            var resultat=transport.headerJSON;
            if (!(resultat.direct)) {
                $('recherche_bibliotheque')
                    .insert(new Element('div').addClassName('resultat_recherche')
                                              .insert(resultat[0]?'S&eacute;lectionnez un titre d\'histoire dans la liste.':'Aucun r&eacute;sultat !'));
            }
            var i=0;
            while (resultat[i]) {
                if (resultat.direct) {
                    var magazine=resultat[i];
                    magazine.magazine_numero=magazine.magazine_numero.replace(new RegExp('\\+','g'),'');
                    
                    if ($$('[name="'+magazine.pays+'/'+magazine.magazine_numero+'"]').length > 0 || !(resultat.direct) || recherche_bibliotheque=='false') {
                        $('recherche_bibliotheque')
                        .insert(new Element('div').writeAttribute({'id':'magazine_'+magazine.pays+'/'+magazine.magazine_numero})
                                                  .addClassName('magazine_trouve')
                                                  .insert(new Element('img', {'src':'images/flags/'+magazine.pays+'.png','alt':magazine.pays}))
                                                  .insert(magazine.titre));
                    }
                }
                else {
                    var histoire=resultat[i];
                    
                    $('recherche_bibliotheque')
                        .insert(new Element('div').addClassName('histoire_trouvee')
                                                  .writeAttribute({'id':'histoire_'+histoire.code})
                                                  .insert(histoire.titre));
                }
                i++;
            }
            if (resultat.direct) {
                if (typeof ($('recherche_bibliotheque').down('.magazine_trouve')) == 'undefined')
                    $('recherche_bibliotheque')
                        .insert(new Element('div').addClassName('resultat_recherche')
                                                  .insert('Vous ne poss&eacute;dez pas cette histoire.'));
            }
            if (resultat.limite) {
                $('recherche_bibliotheque')
                    .insert(new Element('div').addClassName('resultat_recherche')
                                              .insert('Le nombre de r&eacute;sultats est > 10.'));
                $('recherche_bibliotheque')
                    .insert(new Element('div').addClassName('resultat_recherche')
                                              .insert('Pr&eacute;cisez votre recherche'));
                
            }
            $$('.magazine_trouve').invoke('observe','click',function(event) {
                var element=Event.element(event);
                var pays_magazine=element.readAttribute('id').substring('magazine_'.length, element.readAttribute('id').length);
                if (recherche_bibliotheque=='true') {
                    var tranche_trouvee=$$('[name="'+pays_magazine+'"]')[0];
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
                    pays_magazine=pays_magazine.split('/');
                    $$('#liste_pays option').each(function (option) {
                        if (option.readAttribute('id') == pays_magazine[0]) {
                            $('liste_pays').selectedIndex=option.index;
                            select_magazine(pays_magazine[1]);
                            return;
                        }
                    });
                    
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
            ouvrirInfoBulle(Event.element(event));
        }
    );
}

function ouvrirInfoBulle(tranche) {
    numero_bulle=getInfosNumero(tranche.src);
    var pos_left=tranche.offsetLeft+300 >= $('body').offsetWidth ? $('body').offsetWidth - 310 : tranche.offsetLeft;
    if (bulle == null) {
        bulle=new Element('div',{'id':'infobulle'})
            .addClassName('bulle')
            .setStyle({'top':tranche.offsetTop+'px', 'left':pos_left+'px'});
        $('body').insert(bulle);
    }
    else {
        $(bulle).setStyle({'top':(tranche.offsetTop-50)+'px', 'left':pos_left+'px'})
                .update();
    }
    new Ajax.Request('Edge.class.php', {
        method: 'post',
        parameters:'get_visible=true&debug='+debug+'&pays='+numero_bulle['pays']+'&magazine='+numero_bulle['magazine']+'&numero='+numero_bulle['numero'],
        onSuccess:function(transport) {
            if (numerosIdentiques(numero_bulle, getInfosNumero(transport.request.body)))
                if ($(bulle))
                    $(bulle).update(transport.responseText);
        }
    });

}

function getInfosNumero (texte) {
    var infos=new Array();
    var infos_source;
    if (texte.indexOf('/gen/')==-1) {
        if (texte.indexOf('?') == -1)
            infos_source=texte.substring(texte.indexOf('&')+1,texte.length);
        else
            infos_source=texte.substring(texte.indexOf('?')+1,texte.length);
        var reg=new RegExp("&", "g");
        var tab_infos_source=infos_source.split(reg);
        reg=new RegExp("=", "g");
        for (i in tab_infos_source) {
            if (isNaN(i))
                break;
            var info_courante=tab_infos_source[i].split(reg);
            if (info_courante[0] != '_')
                infos[info_courante[0]]=info_courante[1];
        }
    }
    else {
        infos_source=texte.substring(texte.indexOf('/edges/')+'/edges/'.length,texte.length);
        infos['pays']=infos_source.substring(0,infos_source.indexOf('/'));
        var magazine_et_numero=infos_source.substring(infos_source.lastIndexOf('/')+1,infos_source.length);
        infos['magazine']=magazine_et_numero.substring(0,magazine_et_numero.indexOf('.'));
        infos['numero']=magazine_et_numero.substring(magazine_et_numero.indexOf('.')+1,magazine_et_numero.lastIndexOf('.'));
    }
    infos['magazine']=infos['magazine'].toLowerCase();
    return infos;
}

function numerosIdentiques(numero1, numero2) {
    return numero1['pays'] == numero2['pays']
        && numero1['magazine'] == numero2['magazine']
        && numero1['numero'] == numero2['numero'];
}

function getScreenCenterY() {
    return document.viewport.getScrollOffsets().top + document.viewport.getHeight()/2;
}

function getScreenCenterX() {
    return document.body.clientWidth/2;
}