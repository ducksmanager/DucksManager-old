var current_element;
var current_couv;
var current_width;
var current_height;
var action_en_cours=false;
var couverture_ouverte=false;
var ouvrirApres=false;

function ouvrir(element) {
    if (action_en_cours)
        return;
    if (couverture_ouverte && element != current_element) {
        ouvrirApres=true;
        fermer(element);
        return;
    }
    current_width=element.width;
    current_height=element.height;
    var couverture=new Image();
    couverture.src='edges/fr/fr_spg_110a_001.jpg';
    current_element=element;//.absolutize();
    var pos_depart_couverture=new Array(current_element.getStyle('left'),current_element.getStyle('top'));
    pos_depart_couverture[0]=(parseInt(pos_depart_couverture[0].substring(0,pos_depart_couverture[0].length-2))+current_width)+'px';
    if ($('couv')==null) {
        current_couv=new Element('img', {'id':'couv','src':couverture.src})
                        .setStyle({'height':current_height,'width':couverture.width/(couverture.height/current_height), 'position':'absolute','display':'none',
                                   'top':pos_depart_couverture[1], 'left':pos_depart_couverture[0], 'zIndex':100});
        current_couv.observe('click', function () {
            ouvrirApres=false;
            fermer(current_element);
        });
        $('body').insert(current_couv);
    }
    else {
        current_couv=$('couv');
        current_couv.setStyle({'top':pos_depart_couverture[1], 'left':pos_depart_couverture[0]});
    }
    action_en_cours=true;
    current_couv.observe('load',function() {
        new Effect.Parallel([
            new Effect.BlindLeft(current_element, {sync:true}),
            new Effect.BlindRight(current_couv, {sync:true}),
            new Effect.Move(current_couv, {x:-1*current_width, mode:'relative', sync:true})
        ], { 
        duration: 1.5
      });
    });
    couverture_ouverte=true;
    setTimeout(function() {
        action_en_cours=false;
    }, 1700);
}

function fermer(element) {
    if (action_en_cours)
        return;
    action_en_cours=true;
    new Effect.Parallel([
        new Effect.BlindLeft(current_couv, {sync:true}),
        new Effect.BlindRight(current_element, {sync:true}),
        new Effect.Move(current_couv, {x:current_width, mode:'relative', sync:true})
    ], {
        duration: 1.5
      });
    setTimeout(function() {
        $('couv').remove();
        action_en_cours=false;
        couverture_ouverte=false;
        if (ouvrirApres==true)
            ouvrir(element);
    }, 1700);
}

function charger_bibliotheque(texture1, sous_texture1, texture2, sous_texture2) {
    var section=$('bibliotheque');
    var myAjax3 = new Ajax.Request('edgetest.php', {
        method: 'post',
        parameters:'largeur='+section.clientWidth+'&hauteur='+section.clientHeight+'&texture1='+texture1+'&sous_texture1='+sous_texture1
                  +'&texture2='+texture2+'&sous_texture2='+sous_texture2,
        onSuccess:function(transport) {
            $('bibliotheque').update(transport.responseText);
            /*
                <div style="z-index:50;position:inherit;width:100%;top:<?=Etagere::$hauteur*Edge::$grossissement?>;height:<?=Etagere::$epaisseur*Edge::$grossissement?>;
                            background:transparent url('edges/textures/<?=Etagere::$texture2?>/<?=Etagere::$sous_texture2?>.jpg') repeat-x left top">&nbsp;</div>
                <div style="z-index:50;position:inherit;width:100%;top:<?=Etagere::$hauteur*Edge::$grossissement?>;height:<?=Etagere::$epaisseur*Edge::$grossissement?>;
                            background-color:black;opacity:0.5">&nbsp;</div>*/
            $('bibliotheque').setStyle({'zIndex':50,'width':$('largeur_etagere').readAttribute('name')+'px',
                                        'background':'transparent url(\'edges/textures/'+texture1+'/'+sous_texture1+'.jpg\') repeat left top'})
        }
	});
}