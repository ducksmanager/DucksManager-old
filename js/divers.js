function supprimer_auteur (nom_auteur) {
	new Ajax.Request('Database.class.php', {
	   method: 'post',
	   parameters:'database=true&supprimer_auteur=true&nom_auteur='+nom_auteur,
	   onSuccess:function() {
	    	location.reload();
		}
	});
}	

var pays_selectionne=null;
function montrer_magazines(pays) {
	if (pays === undefined) {
		if ($('onglets_pays') === null)
			pays_selectionne='';
		else
			pays_selectionne = $('onglets_pays').down('li.active').down('a').readAttribute('name');
	}
	else {
		pays_selectionne=pays;
	}
	var largeur_max=0;
    $$('[name="magazine"]')
        .each(function(element) {
            if (element.href) {
                if (element.href.indexOf(pays_selectionne+'/') == -1)
                    element.up().addClassName('cache');
                else {
                    element.up().removeClassName('cache');
            		element.setStyle({'width': ''});
                    var largeur=element.offsetWidth;
                    if (largeur > largeur_max) {
                    	largeur_max=largeur;
                    }
                }
            }
        }
    );
    
    $$('[name="magazine"]')
    	.each(function(element) {
    		element.setStyle({'width': largeur_max+'px'});
    	}
    );
    var element_pays=$('onglets_pays').down('[name="'+pays_selectionne+'"]');
    var marge_gauche=element_pays.offsetLeft-$('contenu').getStyle('paddingLeft').replace(/px/,'');
    if (marge_gauche+largeur_max > $('contenu').offsetWidth-20) {
    	marge_gauche-= $('contenu').offsetWidth-20 - marge_gauche-largeur_max;
    }
    $('onglets_magazines')
    	.setStyle({'marginLeft':marge_gauche+'px'})
    	.stopObserving('mouseleave')
    	.observe('mouseleave',function() {
    		$$('[name="magazine"]')
            .each(function(element) {
                if (element.href) {
                    element.up().addClassName('cache');
                }
            });
    	});
}