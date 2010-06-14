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
    couverture.src='fr/fr_spg_110a_001.jpg';
    current_element=element;//.absolutize();
    var pos_depart_couverture=new Array(current_element.getStyle('left'),current_element.getStyle('top'));
    pos_depart_couverture[0]=(parseInt(pos_depart_couverture[0].substring(0,pos_depart_couverture[0].length-2))+current_width)+'px';
    if ($('couv')==null) {
        current_couv=new Element('img', {'id':'couv','src':couverture.src})
                        .setStyle({'height':current_height,'width':couverture.width/(couverture.height/current_height), 'position':'absolute','display':'none',
                                   'top':pos_depart_couverture[1], 'left':pos_depart_couverture[0]});
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
    }, 2000);
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
    }, 2000);

}