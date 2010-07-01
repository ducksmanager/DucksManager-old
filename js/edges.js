var current_element;
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
var hauteur_etage;
var grossissement;
var nb_etageres;
var nb_etageres_terminees;

function ouvrir(element) {
    if (action_en_cours)
        return;
    if (couverture_ouverte && element != current_element) {
        ouvrirApres=true;
        fermer(element);
        return;
    }
    action_en_cours=true;
    var infos=new Array();
    if (element.src.indexOf('/gen/')==-1) {
        var infos_source=element.src.substring(element.src.indexOf('?'),element.src.length);
        var reg=new RegExp("&", "g");
        var tab_infos_source=infos_source.split(reg);
        reg=new RegExp("=", "g");
        for (i in tab_infos_source) {
            if (isNaN(i))
                break;
            var info_courante=tab_infos_source[i].split(reg);
            if (info_courante[0][0]=='?')
                info_courante[0]=info_courante[0].substring(1,info_courante[0].length);
            infos[info_courante[0]]=info_courante[1];
        }
        infos['magazine']=infos['magazine'].toLowerCase();
    }
    else {
        var infos_source=element.src.substring(element.src.indexOf('/edges/')+'/edges/'.length,element.src.length);
        infos['pays']=infos_source.substring(0,infos_source.indexOf('/'));
        var magazine_et_numero=infos_source.substring(infos_source.lastIndexOf('/')+1,infos_source.length);
        infos['magazine']=magazine_et_numero.substring(0,magazine_et_numero.indexOf('.'));
        infos['numero']=magazine_et_numero.substring(magazine_et_numero.indexOf('.')+1,magazine_et_numero.lastIndexOf('.'));
        
    }
    largeur_image=element.width;
    hauteur_image=element.height;
    couverture=new Image();
    current_element=element.cloneNode(true);
    current_element.setStyle({'zIndex':500,'position':'absolute',
                              'left':getScreenCenterX()+'px','top':(getScreenCenterY()-hauteur_image/2)+'px'})
                   .setOpacity(0);
    $('bibliotheque').insert(current_element);
    new Effect.Parallel([
        new Effect.Opacity(element,{'from':1, 'to':0, sync: true}),
        new Effect.Opacity(current_element, {'from':0, 'to':1, sync:true})
    ], {
        duration: 0.5,
        afterFinish:function() {
            current_animation=new Element('div')
                .setStyle({'position':'absolute', 'left':getScreenCenterX()+'px','top':(getScreenCenterY()-hauteur_image/2)+'px', 'zIndex':600})
                .update(new Element('img',{'src':'loading.gif'}));
            $('bibliotheque').insert(current_animation);
        }});
    new Ajax.Request('Inducks.class.php', {
        method: 'post',
        parameters:'get_cover=true&pays='+infos['pays']+'&magazine='+infos['magazine']+'&numero='+infos['numero'],
        onSuccess:function(transport) {
            couverture.src=transport.responseText;
            current_couv=new Element('img', {'id':'couv','src':couverture.src,'height':hauteur_image})
                    .setStyle({'position':'absolute','display':'none',
                               'left':(getScreenCenterX()+current_element.width)+'px','top':(getScreenCenterY()-hauteur_image/2)+'px', 'zIndex':500});
            $('body').insert(current_couv);
            current_couv.observe('click', function () {
                ouvrirApres=false;
                fermer(element);
            });
            current_couv.observe('load',function() {
                current_animation.remove();
                new Effect.Parallel([
                    new Effect.BlindRight(current_couv, {sync:true}),
                    new Effect.Move(current_couv, {'mode':'absolute', 'x':getScreenCenterX(), 'y':getScreenCenterY()-hauteur_image/2, sync:true}),
                    new Effect.BlindLeft(current_element, {sync:true})
                     ], {
                    duration: 1
                });
                action_en_cours=false;
                couverture_ouverte=true;
            });
        }
    });
}

function fermer(element) {
    if (action_en_cours)
        return;
    action_en_cours=true;
    new Effect.Parallel([
        new Effect.BlindLeft(current_couv, {sync:true}),
        new Effect.Move(current_couv, {'mode':'absolute', 'x':(getScreenCenterX()+element.width), 'y':getScreenCenterY()-hauteur_image/2, sync:true}),
        new Effect.BlindRight(current_element, {sync:true})
    ], {
        duration: 1,
        afterFinish:function() {
            new Effect.Parallel([
                new Effect.Opacity(current_element, {'from':1, 'to':0, sync:true}),
                new Effect.Opacity(element,{'from':0, 'to':1, sync: true})
            ], {
                duration: 0.5,
                afterFinish:function() {
                    $(current_couv).remove();
                    action_en_cours=false;
                    couverture_ouverte=false;
                    if (ouvrirApres==true)
                        ouvrir(element);
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

function charger_tranche(element) {
    element.observe('load',function() {
        var element2=this;
        var suivant=element2.next();
        if (suivant.className.indexOf('tranche')==-1) {
            nb_etageres_terminees++;
            $('pct_bibliotheque').setStyle({'width':parseInt(100*nb_etageres_terminees/nb_etageres)+'%'});
            var tranche_suivante=suivant.next().next();
            if (tranche_suivante.className.indexOf('tranche')==-1) {
               init_observers_tranches();
               l10n_action('remplirSpan','chargement_bibliotheque_termine');
               $('barre_pct_bibliotheque').remove();
            }
            else
                charger_tranche(tranche_suivante);
        }
        else {
            charger_tranche(suivant);
        }
    });
    element.src=element.name;
    element.name='';
}

function init_observers_tranches() {
    $$('.tranche').invoke(
        'observe',
        'mousedown',
        function(event) {
            ouvrir(Event.element(event));
          }
    );
    return;
}

function getScreenCenterY() {
    return document.viewport.getScrollOffsets().top + document.viewport.getHeight()/2;
}

function getScreenCenterX() {
    return document.body.clientWidth/2;
}