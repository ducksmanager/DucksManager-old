var chargements=[];
var infos_chargements=[];
function initProgressBar(id,page, parameters, callback) {
    infos_chargements[id]=[];
    new Ajax.Request(page, {
        method: 'post',
        parameters : (parameters || '')+'&init_chargement=true&id='+id,
        onSuccess : function(transport) {
            var id=transport.request.parameters.id;
            chargements[id]=transport.responseJSON;
            for (var i in chargements[id]) {
                chargements[id]['element_courant']=chargements[id][i];
                break;
            }
            traitement_suivant(id,transport.request.url, parameters, callback);
        }
    });
}

function MAJProgressBar(id) {
    var pct=getPctCourant(id);
    $('message_'+id).update(' : '+chargements[id]['element_courant']);
    $('pct_'+id).setStyle({'width':pct+'%'});
    return pct === 100;

}

function traitement_suivant(id,page, parameters, callback) {
    new Ajax.Request(page, {
        method: 'post',
        parameters : (parameters || '')+'&id='+id+'&element='+chargements[id]['element_courant'],
        onSuccess : function(transport) {
            var id=transport.request.parameters.id;
            infos_chargements[id][getIndexCourant(id)]=transport.responseJSON;
            var est_termine=MAJProgressBar(id);
            chargements[id]['element_courant']=getElementSuivant(id);
            if (est_termine && callback) {
                callback({chargements: chargements[id], infos: infos_chargements[id]});
            }
            else
                traitement_suivant(id,transport.request.url, parameters, callback);
        }
    });
    
}

function getIndexCourant(id_chargement) {
    for (var i in chargements[id_chargement]) {
        if (chargements[id_chargement][i]===chargements[id_chargement]['element_courant'])
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
        if (chargements[id_chargement][i]===chargements[id_chargement]['element_courant'])
            element_courant_trouve=true;
    }
    return null;
}

function getPctCourant(id_chargement) {
    for (var i in chargements[id_chargement]) {
        if (chargements[id_chargement][i]===chargements[id_chargement]['element_courant'])
            break;
    }
    return parseInt(100*((parseInt(i)+1)/(chargements[id_chargement].length)));
}