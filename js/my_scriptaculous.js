var l10n_calculs_auteurs=['calcul_en_cours','calcul_termine'];
var l10n_cacher_afficher_aide=['cacher_aide','afficher_aide'];
var l10n_divers=new Array('chargement');

var types_listes=[];
var parametres=[];
var id_magazine_selectionne=null;
var magazineMenu;
var prevent_click=false;
var nom_magazine_draggable;
var nom_magazine_droppable;
var draggable_id;
var droppable_id;
var description_liste_en_cours=null;

var l10n_print=[];

function implement_draganddrop(box) {
    new Draggable(box,
    {
        starteffect:function(element) {
            $(element).setStyle({'marginLeft':'10px'});
        },
        endeffect:function(element) {
            $(element).setStyle({'marginLeft':''});
        },

        handle:'titre_magazine',
        revert:'failure',
        constraint:'vertical'
    });
    Droppables.add(box,
    {
        hoverclass: 'hover',
        onDrop:function(draggable,droppable,event) {
            draggable_id=$(draggable).readAttribute('id');
            droppable_id=$(droppable).readAttribute('id');

            nom_magazine_draggable=$(draggable).down('.titre_magazine').readAttribute('name');
            nom_magazine_droppable=$(droppable).down('.titre_magazine').readAttribute('name').split(/-/g);
            nom_magazine_droppable=nom_magazine_droppable[0]+(nom_magazine_droppable[1]?' - ...':'');
            $('menu_contextuel_print').down('.entete').update(nom_magazine_draggable+'<br />&gt;&nbsp;'+nom_magazine_droppable);
            $$('[name="nom_magazine_droppable"]').each(function (element) {
                $(element).update(' '+nom_magazine_droppable)
            });
            prevent_click=true;
            protos['print'].show(event);
        }
    });
}

function implement_dragsanddrops() {
    l10n_action('fillArray',l10n_divers,'l10n_divers');
    l10n_action('fillArray',['erreur_fusion_listes_types_differents','magazines_multiples'],'l10n_print');
    l10n_action('remplirSpanName',l10n_cacher_afficher_aide);
    
    
    new Ajax.Request('Liste.class.php', {
       method: 'post',
       parameters:'types_listes=true',
       onSuccess:function(transport) {
            var listes=transport.headerJSON;
            var i=0;
            for (var liste_abrege in listes) {
                var liste={};
                liste.name=listes[liste_abrege];
                liste.className=liste_abrege;
                types_listes[i]=liste;
                i++;
            }
       }
    });

    Sortable.create('container',{
        elements:$$('.draggable_box'),
        handles:$$('.draggable_box .titre_magazine'),
        hoverClass:'hover',
        constraint: false,
        starteffect:function(element) {
            var type_liste=element.down('.contenu_liste').title;
            afficher_infos_type_liste(type_liste);
            var box=(element.hasClassName('draggable_box')) ? element : element.up('.draggable_box');
            afficher_parametres_box(box);
        },
        endeffect:function(element) {
            var pays_magazine=toMagazineID(element);
            var pays_magazine_precedent=typeof(element.previous()) == 'undefined' ? 'null' : toMagazineID(element.previous());
            var pays_magazine_suivant=typeof(element.next()) == 'undefined' ? 'null' : toMagazineID(element.next());
            var position_liste=element.previousSiblings().length;
            changer_position_liste(pays_magazine,pays_magazine_precedent,pays_magazine_suivant,position_liste)
        }
    });
    $$('.draggable_box').invoke('observe','click',function(event) {
        var element=Event.element(event);
        var box=(element.hasClassName('draggable_box')) ? element : element.up('.draggable_box');
        
        afficher_parametres_box(box);
    });
    
    $('contenu_general').select('.details_parametre').each(function(element) {
        creer_slider(element);
    });
    ajouter_texte_sliders();
    
    toggle_item_menu($$('[name="presentation"]')[0]);
    $('contenu_general').setStyle({'visibility':'visible'});
    
    $$('.draggable_box').each(function(box) {
        var pays_magazine=toMagazineID(box).split('_');
        var type_liste=$(box).down('.contenu_liste').readAttribute('title');
        new Ajax.Request('Liste.class.php', {
            method: 'post',
            parameters:'sous_liste=true&type_liste='+type_liste+'&pays='+pays_magazine[0]+'&magazine='+pays_magazine[1]+'&parametres='+box.down('.parametres_box').innerHTML,
            onSuccess:function(transport) {
                box.down('.contenu_liste').update(transport.responseText);
                box.down('.parametres_box').remove();
            }
        });
    });
}

function creer_slider(element) {
    var slider=element.down('.slider');
    var input_valeur_courante=element.down('.valeur_courante');
    var valeur_defaut=element.down('.valeur_defaut').getValue();
    var valeurs_possibles=element.down('.valeurs').getValue().split(',');
    var min=element.down('.min').getValue();
    var max=element.down('.max').getValue();
    if (min=='') {
        min=valeurs_possibles[0];
        max=valeurs_possibles[valeurs_possibles.length-1];
        for (var i=0;i<valeurs_possibles.length;i++)
            valeurs_possibles[i]=parseInt(valeurs_possibles[i]);
    }
    min=parseInt(min);
    max=parseInt(max);

    new Control.Slider(slider.down('.handle'), slider, {
      values:valeurs_possibles=='' ? null : valeurs_possibles,
      range: $R(min,max),
      sliderValue: parseInt(input_valeur_courante.value),
      increment: 1,
      onSlide: function(value) {
        value=parseInt(value);
        input_valeur_courante.value=value;
        switch(element.readAttribute('id')) {
            case 'espacement_boites':
                $$('.draggable_box').invoke('setStyle',{'marginBottom':value+'px'});
            break;
            case 'bordure_boites_r':
                $$('.draggable_box').invoke('setStyle',{'borderColor':'rgb('+value+','+$('bordure_boites_v').down('.valeur_courante').getValue()+','+$('bordure_boites_b').down('.valeur_courante').getValue()+')'});
            break;
            case 'bordure_boites_v':
                $$('.draggable_box').invoke('setStyle',{'borderColor':'rgb('+$('bordure_boites_r').down('.valeur_courante').getValue()+','+value+','+$('bordure_boites_b').down('.valeur_courante').getValue()+')'});
            break;
            case 'bordure_boites_b':
                $$('.draggable_box').invoke('setStyle',{'borderColor':'rgb('+$('bordure_boites_r').down('.valeur_courante').getValue()+','+$('bordure_boites_v').down('.valeur_courante').getValue()+','+value+')'});
            break;
        }
      },
      onChange: function() { 
        afficher_sv_en_cours();
        var valeurs=element.up('div').select('.valeur_courante').invoke('getValue');
        var noms=element.up('div').select('.details_parametre').invoke('readAttribute','id');
        var parametres_update={};
        valeurs.each(function(valeur,i) {
            parametres_update[noms[i]]=valeur;
        });
        switch(element.up('div').readAttribute('id')) {
            case 'contenu_general':
                afficher_sv_en_cours();
                update_parametres_generaux(JSON.stringify(parametres_update));
            break;
            
            case 'contenu_boite_selectionnee':// Propri�t�s de boite
                afficher_sv_en_cours();
                update_list(id_magazine_selectionne, $(id_magazine_selectionne).down('.contenu_liste').title, JSON.stringify(parametres_update));
            break;
        }
      }
    });
    if (slider.select('.handle').length == 2)
        slider.down('.handle',1).remove();
    var pos_slider_fixe=parseInt(246*((valeur_defaut-min)/(max-min)));
    slider.down('.handle').up()
        .insert(slider.down('.handle').clone(true)
                                      .writeAttribute({'name':input_valeur_courante.value})
                                      .setStyle({'zIndex':1,'backgroundColor':'green','left':pos_slider_fixe+'px'}));
}

function afficher_parametres_box(box,type_liste) {
    afficher_chargement_parametres();
    $$('.draggable_box').invoke('setStyle',{'borderStyle':'solid'});
    box.setStyle({'borderStyle':'dashed'});
    var nom_complet_magazine=box.down('.titre_magazine').readAttribute('name');
    id_magazine_selectionne=toMagazineID(box);
    if (!type_liste)
        type_liste=box.down('.contenu_liste').title;
    var position_liste=box.previousSiblings().length;
    afficher_infos_type_liste(type_liste);
    new Ajax.Request('Liste.class.php', {
        method: 'post',
        parameters:'parametres=true&id_magazine='+id_magazine_selectionne+(type_liste?('&type_liste='+type_liste+'&position_liste='+position_liste):''),
        onSuccess:function(transport) {
            var choix_liste=new Element('ul').addClassName('types_listes');
            types_listes.each(function(liste) {
                var lien=new Element('a',{'href':'javascript:return false;','name':liste.name})
                            .addClassName(liste.className)
                            .update(liste.name);
                if (liste.className == transport.request.parameters.type_liste)
                    lien.addClassName('selected');
                choix_liste.insert(new Element('li').insert(lien));
            });
            choix_liste.select('li a').invoke('observe','click',function(event) {
                
                modifier_type_liste(id_magazine_selectionne,elementToTypeListe(Event.element(event)));
            });
            $('contenu_boite_selectionnee').update(new Element('h3').update(nom_complet_magazine))
                                           .insert(choix_liste);
            
            toggle_item_menu($$('[name="parametres"]')[0]);
            toggle_item_menu($$('[name="boite_selectionnee"]')[0]);
            
            for(var i in transport.headerJSON) {
                var slider=new Element('table').insert($('contenu_general').down('tr').clone(true)).insert($('contenu_general').down('tr',1).clone(true));
                slider.down('td').update(transport.headerJSON[i].texte);
                slider.down('.details_parametre').writeAttribute({'id':i});
                slider.down('.valeur_courante').setValue(parseInt(transport.headerJSON[i].valeur));
                slider.down('.valeur_defaut').setValue(parseInt(transport.headerJSON[i].valeur_defaut));
                var min_existe = typeof(transport.headerJSON[i].min) != 'undefined';
                slider.down('.min').setValue(min_existe?transport.headerJSON[i].min:'');
                slider.down('.max').setValue(min_existe?transport.headerJSON[i].max:'');
                
                var valeurs_existe = typeof(transport.headerJSON[i].valeurs_possibles) != 'undefined';
                slider.down('.valeurs').setValue(valeurs_existe?transport.headerJSON[i].valeurs_possibles:'');
                $('contenu_boite_selectionnee').insert(slider);
                creer_slider(slider);
            }
            fin_update();
            ajouter_texte_sliders();
        }
    });
}

function afficher_chargement_parametres() {
    $('infos_sv').setStyle({'display':'block'});
    $('infos_sv').update('Chargement des param&egrave;tres...');
}

function afficher_sv_en_cours() {
    $('infos_sv').setStyle({'display':'block'});
    $('infos_sv').update('Sauvegarde des param&egrave;tres...');
}

function afficher_termine() {
    $('infos_sv').update('Termin&eacute;');
}

function afficher_vide() {
    $('infos_sv').fade();
}

function ajouter_texte_sliders() {
    var nom_onglet=$('contenu_parametres').down('li.active').down('a').readAttribute('name');
    if ($('contenu_'+nom_onglet).select('.details_parametre').length >0)
    $('contenu_'+nom_onglet).insert('Les rectangles verts correspondent aux valeurs par d&eacute;faut.');
}

function creer_menu_magazine() {
    magazineMenu = [
    {
        separator: true
    },{
        className: 'type_liste',
        groupName: 'type_liste',
	subMenu : true
    },{
        className: 'parametres_liste',
        groupName: 'parametres_liste',
        subMenu : true
    },{
        className: 'extraire',
        groupName: 'extraire',
        subMenu : true
    },{
        separator: true
    },{
        className: 'fusionner_tout',
        groupName: 'fusionner_tout'
    },{
        className: 'type_liste_global',
        groupName: 'type_liste_global',
        subMenu : true
    }];

    new Ajax.Request('Liste.class.php', {
	   method: 'post',
	   parameters:'types_listes=true',
	   onSuccess:function(transport) {
	    	var listes=transport.headerJSON;
                var i=0;
                for (var liste_abrege in listes) {
                    var liste={};
                    liste.name=listes[liste_abrege];
                    liste.className=liste_abrege;
                    types_listes[i]=liste;
                    i++;
	    	}

                new Proto.Menu({
                  selector:  '.draggable_box',
                  className: 'menu desktop',
                  menuItems: magazineMenu,
                  type:      'magazine'
                });
           }
    });
}

function toggle_item_menu(element_clic) {
    element_clic=element_clic.tagName=='LI' ? element_clic : element_clic.parentNode;
    element_clic.up().select('li.active').invoke('removeClassName','active');
    $(element_clic).toggleClassName('active');
    element_clic.up().select('li a').pluck('name').each(function(nom) {
        $('contenu_'+nom).setStyle({'display':'none'});
    });
    $('contenu_'+element_clic.down().name).setStyle({'display':'block'});
}

function init_autocompleter_auteurs() {
    l10n_action('fillArray',l10n_calculs_auteurs,'l10n_calculs_auteurs');
    if (!($('auteur_cherche'))) return;
    new Ajax.Autocompleter ('auteur_cherche',
        'liste_auteurs',
        'auteurs_choix.php',
        {
            method: 'post',
            indicator:'loading_auteurs',
            paramName: 'value',
            afterUpdateElement: ac_return
        });
}


function ac_return(field, item){
	var regex_nettoyage_nom=/(?:^[\t ]*)|(?:[\t ]*$)/g;
	$('auteur_nom').value=field.value.replace(regex_nettoyage_nom,'');
    $('auteur_id').value=item.down('[name="nom_auteur"]').readAttribute('title');
    $('auteur_cherche').value=$('auteur_cherche').value.replace(regex_nettoyage_nom,'');
}

function modifier_type_liste(box,type_liste,confirmer) {
    afficher_chargement_parametres();
    new Ajax.Request('Liste.class.php', {
       method: 'post',
       parameters:'sous_liste=true&type_liste='+type_liste+'&fusions='+box+(confirmer?'&confirmation_remplacement=true':''),
       onSuccess:function(transport) {
            if (transport.headerJSON != null && transport.headerJSON.message) {
                if (confirm(transport.headerJSON.message))
                    modifier_type_liste(transport.request.parameters.fusions,transport.request.parameters.type_liste,true);
            }
            else {
                $(transport.request.parameters.fusions+'_contenu')
                    .update(transport.responseText)
                    .writeAttribute({'title':transport.request.parameters.type_liste});
                afficher_parametres_box($(transport.request.parameters.fusions).up('.draggable_box'),transport.request.parameters.type_liste);
            }
            afficher_termine();
       }
    });
}

function toggle_aide() {
    if ($('info').getStyle('display')=='block') {
        $('info').setStyle({'display':'none'});
        $('lien_afficher_aide').removeClassName('cache');
        $('lien_cacher_aide').addClassName('cache');

    }
    else {
        $('info').setStyle({'display':'block'});
        $('lien_afficher_aide').addClassName('cache');
        $('lien_cacher_aide').removeClassName('cache');
    }
}

function toMagazineID(element) {
    return ($(element).hasClassName('draggable_box') ? $(element):$(element).up('.draggable_box'))
                .readAttribute('id').substring('box_'.length);
}

function extraire_magazine(id_magazine_selectionne,pays_magazine) {
    var type_liste=$(id_magazine_selectionne+'_contenu').readAttribute('name');
    pays_magazine=pays_magazine.split('_');
    new Ajax.Request('print.php', {
        method: 'post',
        parameters:'pays='+pays_magazine[0]+'&magazine='+pays_magazine[1]+'&type_liste='+type_liste,
        onSuccess:function(transport,json) {
            var type_liste=transport.request.parameters.type_liste;
            var fusions=id_magazine_selectionne.replace(new RegExp(pays_magazine[0]+'_'+pays_magazine[1]+'\-?','g'),'');
            new Ajax.Request('Liste.class.php', {
                method: 'post',
                parameters:'sous_liste=true'+'&type_liste='+type_liste+'&fusions='+fusions,
                onSuccess:function(transport) {
                    $('box_'+id_magazine_selectionne).writeAttribute({'id':'box_'+fusions});
                    $(id_magazine_selectionne).writeAttribute({'id':fusions});
                    $('box_'+fusions).down('.contenu_liste').update(transport.responseText);
                    $('box_'+fusions).down('.titre_magazine').update(fusions);
                }
            });
            $('body').insert(transport.responseText);
            implement_draganddrop($('box_'+transport.request.parameters.pays+'_'+transport.request.parameters.magazine));
            $('menu_contextuel_magazine').remove();
            creer_menu_magazine(); // R�initialise les observers des boites de magazines
        }
    });
}

function imprimer() {
    $$('.draggable_box').invoke('setStyle',{'border-style':'solid'});
    $$('#info,#lien_cacher_aide,#section_imprimer').invoke('setStyle',{'display':'none'});
    print();
}