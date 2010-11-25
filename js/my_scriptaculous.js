var l10n_calculs_auteurs=new Array('calcul_en_cours','calcul_termine');
var l10n_cacher_afficher_aide=new Array('cacher_aide','afficher_aide');
var l10n_divers=new Array('chargement');

var types_listes=new Array();
var parametres=new Array();
var lists_to_update=new Array();
var id_magazine_selectionne=null;
var printMenu;
var magazineMenu;
var statAjax;
var prevent_click=false;
var nom_magazine_draggable;
var nom_magazine_droppable;
var draggable_id;
var droppable_id;
var description_liste_en_cours=null;

var l10n_print=new Array();

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
    l10n_action('fillArray',new Array('erreur_fusion_listes_types_differents','magazines_multiples'),'l10n_print');
    l10n_action('remplirSpanName',l10n_cacher_afficher_aide);
    var draggable_boxes=$$('.draggable_box');
    for(var i=0;i<draggable_boxes.length;i++) {
        implement_draganddrop(draggable_boxes[i]);
    }
    creer_menu_print();
    creer_menu_magazine();

    /*new Resizable($('box_fr_AJM'), {
            minWidth: 50,
            minHeight: 50
    })*/
}

function creer_menu_print() {

    printMenu = [
    {
        separator: true
    },{
        className: 'deplacer_avant',
        groupName: 'deplacer_avant',
        nextSpanName: 'nom_magazine_droppable'
    },{
        className: 'deplacer_apres',
        groupName: 'deplacer_apres',
        nextSpanName: 'nom_magazine_droppable'
    },{
        className: 'fusionner_les_deux',
        groupName: 'fusionner_les_deux'
    }];
    new Proto.Menu({
      selector:  '#body',
      className: 'menu desktop large',
      menuItems: printMenu,
      type:      'print'
    });
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
                    var liste=new Object();
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
    $$('li.active').each(function(element) {$(element).removeClassName('active')});
    element_clic=element_clic.tagName=='LI' ? element_clic : element_clic.parentNode;
    $(element_clic).toggleClassName('active');
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
    $('auteur_nom').value=field.value;
    $('auteur_id').value=item.down().down().next().title;
}

function ajouter_auteur() {
    var nom_auteur=new Element('div').update($('auteur_cherche').value);
    var abbrev_auteur=new Element('div',{
        'class':'abbrev'
    }).update($('auteur_id'));
    $('auteurs_ajoutes').insert(nom_auteur).insert(abbrev_auteur);
}



function stats_auteur(/*id_event,*/id_user) {
    /*$('update_stats').update('<div style="border:1px solid black;width:100px;">'
			+'<div id="progressbar"
			
			 style="background-color:blue;width:0%;">&nbsp;</div>'
			+'</div>');
	initProgressBar(id_event);*/
    $('resultat_stats').update(l10n_calculs_auteurs['calcul_en_cours']);
    var myAjax3 = new Ajax.Request('stats_auteur2.php', {
        method: 'post',
        parameters:'id_user='+id_user,
        onSuccess:function(transport,json) {
            $('resultat_stats').update(l10n_calculs_auteurs['calcul_termine']);
            window.location.reload();
        //statAjax.stop();
        }
    });
}

function modifier_type_liste(box,type_liste) {
    new Ajax.Request('Liste.class.php', {
       method: 'post',
       parameters:'sous_liste=true&type_liste='+type_liste+'&fusions='+box,
       onSuccess:function(transport,json) {
            $(transport.request.parameters.fusions+'_contenu')
                .update(transport.responseText)
                .writeAttribute({'name':transport.request.parameters.type_liste});
       }
    });
}

function toggle_options(element) {
    var box_options=$(element).next('.box_options');
    if (box_options.getStyle('display')=='block') {
        box_options.setStyle({'display':'none'});
        return;
    }
    else {
        box_options.setStyle({'display':'block'});
    }
}

function toggle_aide() {
    if ($('info').getStyle('display')=='block') {
        $('info').setStyle({'display':'none'});
        $('lien_afficher_aide').removeClassName('cache');
        $('lien_cacher_aide').addClassName('cache');
        return;
    }
    else {
        $('info').setStyle({'display':'block'});
        $('lien_afficher_aide').addClassName('cache');
        $('lien_cacher_aide').removeClassName('cache');
    }
}

function toMagazineID(element) {
    return ($(element).hasClassName('draggable_box') ? $(element):$(element).up('draggable_box'))
                .readAttribute('id').substring('box_'.length);
}

function extraire_magazine(id_magazine_selectionne,pays_magazine) {
    pays_magazine=pays_magazine.split('_');
    new Ajax.Request('print.php', {
        method: 'post',
        parameters:'pays='+pays_magazine[0]+'&magazine='+pays_magazine[1],
        onSuccess:function(transport,json) {
            var type_liste=$('box_'+id_magazine_selectionne).down('.contenu_liste').readAttribute('name');
            var fusions=id_magazine_selectionne.replace(pays_magazine[0]+'_'+pays_magazine[1]+'\-?', '', 'g');
            new Ajax.Request('Liste.class.php', {
                method: 'post',
                parameters:'sous_liste=true'+'&type_liste='+type_liste+'&fusions='+fusions,
                onSuccess:function(transport,json) {
                    $('box_'+id_magazine_selectionne).down('.contenu_liste').update(transport.responseText);
                }
            });
            $('body').insert(transport.responseText);
            implement_draganddrop($('box_'+transport.request.parameters.pays+'_'+transport.request.parameters.magazine));
            $('menu_contextuel_magazine').remove();
            creer_menu_magazine(); // Réinitialise les observers des boites de magazines
        }
    });
}