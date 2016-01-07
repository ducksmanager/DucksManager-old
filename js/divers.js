var auteurs_valides=Array();

function vider_pouces()
{
	for (var i=0;;i++) {
		if (!$('pouce'+i+'_1'))
			break;
		if (!auteurs_valides[i]) {
			for (var j=1;j<=10;j++) {
				var src=$('pouce'+i+'_'+j).readAttribute('src');
				if (src.indexOf('blanc')==-1)
					$('pouce'+i+'_'+j).writeAttribute({'src':src.substring(0,src.length-4)+'_blanc.png'});
			}
		}
	}
} 

function init_notations() {
	var num_auteur=0;
	while ($('pouce'+num_auteur+'_1')) {
		if ($('aucune_note'+num_auteur).checked)
			set_aucunenote(num_auteur);
		num_auteur++;
	}
	var myAjax = new Ajax.Request('Database.class.php', {
	   method: 'post',
	   parameters:'database=true&liste_notations=true',
	   onSuccess:function(transport,json) {
	    	var reg=new RegExp("_", "g");
	    	var notations=transport.responseText.split(reg);
	    	for (var i=0;i<notations.length;i++) {
	    		auteurs_valides[i] = notations[i] == 1;
	    	}
		}
	});
}

function hover (num_auteur,num_image) {
	if ($('pouces'+num_auteur).hasClassName('desactive')) return;
	if (auteurs_valides[num_auteur]==1) return;
	for (var i=1;i<=num_image;i++) {
		var src=$('pouce'+num_auteur+'_'+i).readAttribute('src');
		if (src.indexOf('blanc')!==-1)
			$('pouce'+num_auteur+'_'+i).writeAttribute({'src':src.substring(0,src.length-10)+'.png'});
	}
	for (i=num_image+1;;i++) {
		if (!$('pouce'+num_auteur+'_'+i))
			break;
		var src=$('pouce'+num_auteur+'_'+i).readAttribute('src');
		if (src.indexOf('blanc')==-1)
			$('pouce'+num_auteur+'_'+i).writeAttribute({'src':src.substring(0,src.length-4)+'_blanc.png'});
	}
}

function valider_note(num_auteur) {
	if ($('pouces'+num_auteur).hasClassName('desactive')) return;
	if (auteurs_valides[num_auteur]==1)
		auteurs_valides[num_auteur]=0;
	else {
		$('aucune_note'+num_auteur).checked=false;
		auteurs_valides[num_auteur]=1;
		var i=1;
		while ($('pouce'+num_auteur+'_'+i)) {
			var src=$('pouce'+num_auteur+'_'+i).readAttribute('src');
			if (src.indexOf('blanc')!==-1) {
				$('notation'+num_auteur).value=i-1;
				return;
			}
			i++;
		}
		$('notation'+num_auteur).value=10;
	}
}

function set_aucunenote(num_auteur) {
	if ($('pouces'+num_auteur).hasClassName('desactive')) {
		$('pouces'+num_auteur).removeClassName('desactive');
		return;
	}
	$('pouces'+num_auteur).addClassName('desactive');
	auteurs_valides[num_auteur]=0;
	var i=1;
	while ($('pouce'+num_auteur+'_'+i)) {
		var src=$('pouce'+num_auteur+'_'+i).readAttribute('src');
		if (src.indexOf('blanc')==-1)
			$('pouce'+num_auteur+'_'+i).writeAttribute({'src':src.substring(0,src.length-4)+'_blanc.png'});
		i++;
	}
}

function supprimer_auteur (nom_auteur) {
	var myAjax = new Ajax.Request('Database.class.php', {
	   method: 'post',
	   parameters:'database=true&supprimer_auteur=true&nom_auteur='+nom_auteur,
	   onSuccess:function(transport,json) {
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