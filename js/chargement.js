var chargements=new Array();
var infos_chargements=new Array();
function initProgressBar(id,page) {
    infos_chargements[id]=new Array();
    new Ajax.Request(page, {
        method: 'post',
        parameters : 'init_chargement=true&id='+id,
        onSuccess : function(transport) {
            var id=transport.request.parameters.id;
            chargements[id]=transport.headerJSON;
            for (var i in chargements[id]) {
                chargements[id]['element_courant']=chargements[id][i];
                break;
            }
            traitement_suivant(id,transport.request.url);
        }
    });
}

function MAJProgressBar(id) {
    var pct=getPctCourant(id);
    $('message_'+id).update('pour '+chargements[id]['element_courant']);
    $('pct_'+id).setStyle({'width':pct+'%'});
    if (pct==100)
        return true;
    return false;
}

function traitement_suivant(id,page) {
    new Ajax.Request(page, {
        method: 'post',
        parameters : 'id='+id+'&element='+chargements[id]['element_courant'],
        onSuccess : function(transport) {
            var id=transport.request.parameters.id;
            infos_chargements[id][getIndexCourant(id)]=transport.headerJSON;
            var est_termine=MAJProgressBar(id);
            chargements[id]['element_courant']=getElementSuivant(id);
            if (est_termine)
                new Ajax.Request(transport.request.url, {
                    method: 'post',
                    parameters : 'id='+id+'&fin=true&ids='+JSON.stringify(chargements[id])+'&infos='+JSON.stringify(infos_chargements[id]),
                    onSuccess : function(transport) {
                        window['fin_traitement_'+transport.request.parameters.id](transport.headerJSON);
                        $('chargement_'+transport.request.parameters.id+'_termine').update();
                        $('message_'+transport.request.parameters.id).update('Termin&eacute;');
                    }
                });
            else
                traitement_suivant(id,transport.request.url);
        }
    });
    
}

function getIndexCourant(id_chargement) {
    for (var i in chargements[id_chargement]) {
        if (chargements[id_chargement][i]==chargements[id_chargement]['element_courant'])
            return i;
    }
    return null;
}

function getElementSuivant(id_chargement) {
    var element_courant_trouve=false;
    for (var i in chargements[id_chargement]) {
        if (element_courant_trouve) {
            return chargements[id_chargement][i];
        }
        if (chargements[id_chargement][i]==chargements[id_chargement]['element_courant'])
            element_courant_trouve=true;
    }
    return null;
}

function getPctCourant(id_chargement) {
    for (var i in chargements[id_chargement]) {
        if (chargements[id_chargement][i]==chargements[id_chargement]['element_courant'])
            break;
    }
    return parseInt(100*((parseInt(i)+1)/(chargements[id_chargement].length)));
}

function fin_traitement_classement(headerJSON) {
    data_1=(JSON.parse(headerJSON.data_1));
    data_2=(JSON.parse(headerJSON.data_2));
    $('resultat_classement').update(new Element('div',{'id':'my_chart'}))
                            .insert(new Element('br'))
                            .insert(new Element('a',{'href':'javascript:load_1()'}).update(headerJSON.l10n_valeur_reelles))
                            .insert('&nbsp;&nbsp;-&nbsp;&nbsp;')
                            .insert(new Element('a',{'href':'javascript:load_2()'}).update(headerJSON.l10n_pourcentages));
    swfobject.embedSWF("open-flash-chart.swf", "my_chart", headerJSON.largeur_graphique, "380", "9.0.0");

}