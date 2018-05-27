var chargements=[];
var infos_chargements=[];
function initProgressBar(id,url, parameters, callback) {
    infos_chargements[id]=[];

    parameters = jQuery.extend({}, parameters || {}, {init_chargement: 'true', id: id});
    jQuery.post(url, {
        dataType: 'json',
        data: parameters,
        success: function(response) {
            var id=transport.request.parameters.id;
            chargements[id]=response;
            jQuery.each(chargements, function(id, chargement) {
	            chargements[id]['element_courant']=chargement;
	            return false;
            });
            traitement_suivant(id, url, parameters, callback);
        }
    });
}

function MAJProgressBar(id) {
    var pct=getPctCourant(id);
    jQuery('#message_'+id).text(' : '+chargements[id]['element_courant']);
    jQuery('#pct_'+id).css({width: pct+'%'});
    return pct === 100;

}

function traitement_suivant(id,url, parameters, callback) {
	parameters = jQuery.extend({}, parameters || {}, {element: chargements[id]['element_courant'], id: id});
	jQuery.post(url, {
		dataType: 'json',
		data : parameters,
        success : function(response) {
            infos_chargements[id][getIndexCourant(id)]=response;
            var est_termine=MAJProgressBar(id);
            chargements[id]['element_courant']=getElementSuivant(id);
            if (est_termine && callback) {
                callback({chargements: chargements[id], infos: infos_chargements[id]});
            }
            else
                traitement_suivant(id, url, parameters, callback);
        }
    });
}

function getIndexCourant(id_chargement) {
    var index_courant = 0;
    jQuery.each(chargements[id_chargement], function(i, chargement) {
	    if (chargement===chargements[id_chargement]['element_courant'])
		    index_courant = i;
    });
    return index_courant;
}

function getElementSuivant(id_chargement) {
    var element_courant_trouve=false;
    var chargement_courant = null;
	jQuery.each(chargements[id_chargement], function(i, chargement) {
        if (element_courant_trouve) {
	        chargement_courant = chargement;
	        return false;
        }
        if (chargement===chargements[id_chargement]['element_courant'])
            element_courant_trouve=true;
    });
    return chargement_courant;
}

function getPctCourant(id_chargement) {
    return parseInt(100*((getIndexCourant()+1)/(chargements[id_chargement].length)));
}
