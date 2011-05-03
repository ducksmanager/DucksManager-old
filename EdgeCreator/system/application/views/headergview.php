<html>
<head>
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>../csstabs.css" />
    <style type="text/css">
    html, body, #chargement, #chargement span {
        height:100%;
        padding:0;
        margin:0;
        font-size:11px;
        line-height:25px;
    }

    html, body, span, td {
        font-family: Charcoal,arial,sans-serif;
    }

    span, td {
        font-size:16px;
    }

    #viewer h1 {
        margin-top:-10px;
    }

    a {
        color:blue;
        cursor:pointer;
        text-decoration:none;
    }

    .cache {
        display:none;
    }
    
    #infos {
        position:fixed;
        right:0;
        top:0;
        background-color:white;
        z-index:500;
        width:375px;
        overflow-y: auto;
        height: 100%;
    }
    
    #toggle_helpers{
        position:fixed;
        right:0;
        top:0;
        z-index:1000;
        background-color:white;
    }

    #chargement {
        position:fixed;
        top:0px;
        right:0px;
        margin-top:20px;
        z-index:1000;
    }
    
    #erreurs {
        position:fixed;
        right:0;
        bottom:0;
        color:red;
    }
    
    #viewer {
        position:fixed;
        left:0px;
        padding-right:15px;
        width:200px;
        height:100%;
        border-right:1px solid black;
        background-color: white;
        z-index: 500;
    }

    #viewer_inner {
        height:100%;
        overflow-x:auto;
        overflow-y:auto;
        width:100%;
    }
    
    #corps {
        position:absolute;
        left:225px;
        padding-left:10px;
    }

    table.bordered {
        border-color: black;
        border-collapse: collapse;
        border-spacing: 0px;
    }
    
    .intitule_numero {
        white-space:nowrap;
    }

    td .preview {
        cursor:pointer;
        vertical-align:middle;
    }
     
    .valeur_reelle {
        display:none;
    }

    #modifier_valeur {
        margin-top:5px;
        border-top : 1px solid black;
    }

    .cloner {
        cursor:pointer;
    }
    
    .non_concerne {
        background-color:gray;
    }
    
    .erreur_valeurs_multiples {
        border:1px solid red;
    }
        
    .num_checked {
            background:url('<?=base_url()?>system/application/views/images/checkedbox.png') no-repeat center center;
    }
    .centre {
        text-align:center;
    }
    .lien_etape,.num_etape_preview {
        text-align:center;
    }
    
    .lien_etape>span, .lien_etape>.logo_option, .lien_option>span, .num_etape_preview, .ajouter_etape, .supprimer_etape {
        cursor:pointer;
    }
    
    .previews td {
        border-spacing: 0;
        padding: 0;
        border-collapse: collapse;
    }

    .ligne_previews .image_etape,.fond_noir_active,.fond_noir_inactive {
        text-align:center;
    }

    .ligne_previews td {
        vertical-align: top;
    }
    
    .cellule_nom_fonction {
        text-align:center;
    }
    
    .pointer {
        cursor:pointer;
    }

    tr.tranche_prete {
        background-color: #98EBB2;
    }

    #table_numeros tr:not(.ligne_entete)>td:not(.intitule_numero):not(.cloner) {
        white-space: nowrap;
        min-width:25px;
        max-width: 200px;
        overflow-x: auto;
        cursor:default;
    }

    td.selected {
        border-style:dotted;
    }

    td.reload {
        text-align:center;
    }

    .ajouter_etape {
        margin-right:-10px;
        float:right;
    }

    .supprimer_etape {
        margin-top: -9px;
        position: absolute;
        margin-left: -3px;
    }

    .fond_noir_active {
        border-bottom:1px solid blue;
    }

    .regle {
        z-index: 500;
        float: right;
        cursor:e-resize;
    }

    .zone_regle {
        overflow:hidden;
        position:absolute;
    }
    
    .repere {
        z-index:600;
    }
    
    #montrer_details {
        display:none;
    }
    
    .view_preview {
        float:right;
    }
    
    .option_previews {
        font-size:11px;
        white-space: nowrap;
    }

    div.slider { width:150px; margin:10px 0; background-color:#ccc; height:10px; position: relative; white-space: nowrap; }

    div.slider div.handle { width:10px; height:15px; background-color:#f00; cursor:move; position: absolute; }
    </style>
    <script type="text/javascript" src="<?=base_url()?>system/application/views/js/prototype.js" ></script>
    <script type="text/javascript" src="<?=base_url()?>system/application/views/js/scriptaculous/src/scriptaculous.js" ></script>
    <script type="text/javascript" src="<?=base_url()?>system/application/views/js/json2.js" ></script>
    <script type="text/javascript" src="<?=base_url()?>system/application/views/js/jscolor.js" ></script>
    <script type="text/javascript">
        var first_cell=null;
        var zoom=1.5;
        var pays='<?=$pays?>';
        var magazine='<?=$magazine?>';
        var numeros_dispos;
            var selecteur_cellules='#table_numeros tr:not(.ligne_entete)>td:not(.intitule_numero):not(.cloner)';
        var colonne_ouverte=false;

        function element_to_numero(elements) {
            if (! Object.isArray(elements))
                elements=new Array(elements);
            var numeros=new Array();
            $A(elements).each(function(element) {
                var id=element.readAttribute('id');
                numeros.push(id.substring(id.lastIndexOf('_')+1,id.length));
            });
            return numeros;
        }

        function disableselect(e){
            return false
        }

        function reEnable(){
            return true
        }
        function reload_observers_cells() {
            $$(selecteur_cellules).invoke('stopObserving','mousedown')
                                  .invoke('stopObserving','mouseup')
                                  .invoke('stopObserving','mousemove')
                                  .invoke('stopObserving','click')
                                  .invoke('observe','mousedown',function(event) {
                                        $('table_numeros').onselectstart=new Function ("return false")

                                        //if NS6
                                        if (window.sidebar){
                                            $('table_numeros').onmousedown=disableselect
                                            $('table_numeros').onclick=reEnable
                                        }
                                        if (Event.element(event).hasClassName('cloner') || Event.element(event).up().hasClassName('cloner'))
                                            return;
                                        first_cell=Event.element(event);
                                        if (first_cell.tagName=='DIV')
                                            first_cell=first_cell.up();
                                        marquer_cellules(first_cell,first_cell);
                                  })

                                  .invoke('observe','mousemove',function(event) {

                                        if (first_cell != null) {
                                            var element=Event.element(event);
                                            var this_cell=element;
                                            if (this_cell.tagName=='DIV')
                                                this_cell=this_cell.up();
                                            if (first_cell.previousSiblings().length == this_cell.previousSiblings().length) { // M? colonne
                                                marquer_cellules(first_cell,this_cell);
                                            }
                                        }
                                    });

            new Event.observe(window, 'mouseup',function() {
                $$('.tmp').invoke('removeClassName','tmp');
                first_cell=null;
            });
        }
        
        function reload_observers_options() {
            $$('.lien_option').invoke('stopObserving','click');
        }

        function reload_observers_etapes() {

            $$('.lien_etape>span').invoke('stopObserving','click')
                                  .invoke('observe','click',function (event) {
                var element=Event.element(event);
                if (element.tagName!='TD')
                    element=element.up('td');
                var num_etape=$('table_numeros').down('tr').down('td',element.previousSiblings().length).retrieve('etape');
                charger_etape(num_etape,null);
                var num_colonne_etape=element.previousSiblings().length;
                etapes_utilisees[num_etape]=new Array();
                $$('.ligne_dispo').each(function(ligne_dispo) {
                    var numero=element_to_numero(ligne_dispo);
                    etapes_utilisees[num_etape][numero]=ligne_dispo.down('td',num_colonne_etape).hasClassName('num_checked');
                });
            });
        }
        
        function est_dans_intervalle(numero,intervalle) {
            if (numero==null || intervalle.indexOf('Tous') != -1 || numero==intervalle)
                return true;
            if (intervalle.indexOf('~')!=-1) {
                var numeros_debut_fin=intervalle.split('~');
                var numeros_debut=numeros_debut_fin[0].split(';');
                var numeros_fin=numeros_debut_fin[1].split(';');
            }
            else {
                var numeros_debut=intervalle.split(';');
                var numeros_fin=intervalle.split(';');
            }
            var trouve=false;
            Object.keys(numeros_debut).each(function(i) {
                var numero_debut=numeros_debut[i];
                var numero_fin=numeros_fin[i];
                if (numero_debut === numero_fin) {
                    if (numero_debut == numero) {
                        trouve=true;
                        return;
                    }
                }
                else {
                    numero_debut_trouve=false;
                    for(numero_dispo in numeros_dispos) {
                        if (numero_dispo==numero_debut)
                            numero_debut_trouve=true;
                        if (numero_dispo==numero && numero_debut_trouve) {
                            trouve=true;
                            return;
                        }
                        if (numero_dispo==numero_fin) 
                            return;
                    }
                }
            });
            return trouve;
        }

        var chargements;
        var chargement_courant;
        var numero_chargement;

        function preview_numero(element) {
            $$('.regle').invoke('setStyle',{'display':'none'});
            $('save_png').setStyle({'display':'block'});
            var numero=element.up('tr').readAttribute('id').substring('ligne_'.length,element.up('tr').readAttribute('id').length);
            
           var table=new Element('table');
            switch(onglet_sel) {
               case 'Builder':
                   $('numero_preview').store('numero',numero)
                                      .update('N&deg; '+numero);
                for (var i=0;i<=4;i++) {
                    var tr=new Element('tr');
                    var td=new Element('td');
                    if (i==3) {
                        tr.addClassName('ligne_previews');
                        var image_regle_v=new Element('img',{'id':'regle_verticale','src':'<?= base_url() ?>../images/regle.png'}).addClassName('regle').addClassName('cache');
                        td.update(image_regle_v);
                    }
                    tr.insert(td);
                    element.up('tr').select('.num_checked').each(function(td_etape) {
                        var num_etape=td_etape.retrieve('etape');
                        if (num_etape != -1) {
                            var td=new Element('td').store('etape',num_etape);
                            switch(i) {
                                case 0:
                                    td.addClassName('reload');
                                    var image_reload=new Element('img',{'src':'<?= base_url() ?>../images/reload.png'}).addClassName('pointer');
                                    td.update(image_reload);
                                    image_reload.observe('click',function(event) {
                                        var element=Event.element(event).up('td');
                                        var num_etape=element.retrieve('etape');
                                        reload_etape(num_etape);
                                    });
                                    break;
                                case 1:
                                    td.update(num_etape)
                                    .addClassName('num_etape_preview');
                                    break;
                                case 2:
                                    
                                    break;
                                case 3:
                                    td.addClassName('image_etape');
                                    break;
                                case 4:
                                    var image_fond_noir=new Element('img',{'src':'<?= base_url() ?>../images/fond_noir.png','title':'Voir sous fond noir'}).addClassName('pointer');
                                    td.update(image_fond_noir).addClassName('fond_noir_inactive')
                                      .setStyle({'verticalAlign':'top'})
                                      .writeAttribute({'id':'fond_noir_'+num_etape});
                                    image_fond_noir.observe('click',function(event) {
                                        var element=Event.element(event).up('td');
                                        var num_etape=element.retrieve('etape');
                                        element.toggleClassName('fond_noir_active').toggleClassName('fond_noir_inactive');
                                        reload_etape(num_etape);
                                    });
                                    break;
                                }
                                tr.insert(td);
                            }
                        });
                        var tranche_finale=new Element('td').store('etape','final').addClassName('image_etape');
                        if (i==1)
                            tranche_finale.update('Tranche')
                        .addClassName('num_etape_preview final');
                        tr.insert(tranche_finale);
                        table.insert(tr);
                    }
                    $('contenu_'+onglet_sel.toLowerCase()).down('.previews').update(table);
                    chargements=new Array();
                    numero_chargement=numero;
                    $$('.num_etape_preview').each(function(td_num_etape) {
                        if (td_num_etape.hasClassName('final')) {
                            var num_etape=$$('.num_etape_preview:not(.final)').invoke('retrieve','etape');
                        }
                        else {
                            var num_etape=td_num_etape.retrieve('etape');
                        }
                        chargements.push(num_etape);
                    });
                    chargement_courant=0;
                    charger_preview_etape(chargements[chargement_courant],true);
               break;
               case 'Previews':
                   if (typeof($('numero_preview_debut').retrieve('numero')) == 'undefined') {
                       changer_titres_images_view('Selectionner le dernier numero a previsualiser');
                       
                       alert('Vous allez previsualiser les tranches a partir du numero '+numero+'\n'
                            +'Cliquez sur le lien "Preview" du dernier numero a previsualiser');
                       $('numero_preview_debut').store('numero',numero)
                                                .update('N&deg; '+numero);
                   }
                   else {
                       changer_titres_images_view('Selectionner le premier numero a previsualiser');
                       
                       $('montrer_details').setStyle({'display':'inline'});
                       $('numero_preview_fin').store('numero',numero)
                                                .update('N&deg; '+numero);
                                                
                       var numero_debut = $('numero_preview_debut').retrieve('numero');
                       var numero_fin = $('numero_preview_fin').retrieve('numero'); 
                       for (var ligne=0;ligne<=2;ligne++) {
                        var tr=new Element('tr');
                        var td=new Element('td');
                        if (ligne==2) {
                            tr.addClassName('ligne_previews');
                            var image_regle_v=new Element('img',{'id':'regle_verticale','src':'<?= base_url() ?>../images/regle.png'}).addClassName('regle').addClassName('cache');
                            td.update(image_regle_v);
                        }
                        tr.insert(td);
                        var numero=numero_debut;
                        do {
                            var numero_fin_depasse = numero==numero_fin;
                            var td=new Element('td').store('numero',numero);
                            switch(ligne) {
                                case 0:
                                    td.addClassName('reload');
                                    var image_reload=new Element('img',{'src':'<?= base_url() ?>../images/reload.png'}).addClassName('pointer');
                                    td.update(image_reload);
                                    image_reload.observe('click',function(event) {
                                        var element=Event.element(event).up('td');
                                        var numero=element.retrieve('numero');
                                        reload_numero(numero);
                                    });
                                break;
                                case 1:
                                    td.update(numero).addClassName('numero_preview').setStyle({'textAlign':'center'});
                                break;
                                case 2:
                                    td.addClassName('image_numero');
                                break;
                            }
                            tr.insert(td);
                            if (typeof ($('ligne_'+numero).next()) == 'undefined' || numero_debut == numero_fin)
                                break;
                            numero = $('ligne_'+numero).next().retrieve('numero');
                        } while (!numero_fin_depasse);
                        
                        table.insert(tr);
                    }
                    $('contenu_'+onglet_sel.toLowerCase()).down('.previews').update(table);
                    
                    numero_chargement=null;
                    chargements=new Array();
                    $$('.numero_preview').each(function(td_numero) {
                        var numero=td_numero.retrieve('numero');
                        chargements.push(numero);
                    });
                    chargement_courant=0;
                    charger_previews_numeros(chargements[chargement_courant],true);
                }
                   
               break;
            }
            
        }

        function fixer_regles(image) {
            $('regle_verticale').setStyle({'height':(300*zoom)});
            
            var image_regle_h=new Element('img',{'id':'regle_horizontale','src':'<?=base_url()?>../images/regle_h.png'}).addClassName('regle');

            var div_regle_h=new Element('div',{'id':'zone_regle_horizontale'}).addClassName('zone_regle');

            div_regle_h.update(image_regle_h);
            //$('viewer_inner').insert(div_regle_h);

            $$('.regle').invoke('removeClassName','cache').invoke('setStyle',{'display':'block'})
            if (image == null) {
                $$('.image_preview').each(function(image_preview) {
                    if (image_preview.retrieve('etape') == $$('.regle')[0].retrieve('etape'))
                        image=image_preview;
                });
                if (image == null)
                    image=$('contenu_'+onglet_sel.toLowerCase()).down('.ligne_previews').down('.image_preview');
            }
            $('regle_verticale').writeAttribute({'height':image.height*1.1+'px','width':(3*zoom)});
            //$('regle_horizontale').writeAttribute({'width':(300*zoom)});
            $$('.regle').each(function(regle) {
                var offset_left=image.cumulativeOffset().left;
                var offset_top=image.cumulativeOffset().top;
                
                regle.store('etape',image.retrieve('etape'));
                var zone_regle=regle.up('.zone_regle');
                var left,top;
                if (regle.readAttribute('id') == 'regle_horizontale') {
                    left=offset_left;
                    top=offset_top-regle.height;
                    zone_regle.setStyle({'height':regle.height+'px'});
                    zone_regle.setStyle({'width':image.width*1.1+'px'});
                }
                if (left < 0) {
                    $('viewer_inner').setStyle({'paddingLeft':(-1*left)+'px'});
                    left=0;
                }
                else
                    $('viewer_inner').setStyle({'paddingLeft':'0px'});
                new Draggable(regle,{'constraint':'horizontal',
                                     'starteffect':function(){}
                                    });
                //zone_regle.setStyle({'left':left+'px'});
                //zone_regle.setStyle({'top':top+'px'});
            });
            $$('.regle').invoke('observe','dblclick',function(ev) {
                var regle=Event.element(ev);
                regle.setStyle({'left':''});
            });
        }

        function reload_etape(num_etape) {
            var num_etapes_final=$$('.num_etape_preview:not(.final)').invoke('retrieve','etape');
            chargements=new Array();
            chargements.push(num_etape, num_etapes_final); // Toujours recharger la tranche compl?
            chargement_courant=0;
            charger_preview_etape(chargements[chargement_courant],true);
        }

        function reload_numero(numero) {
            chargements=new Array();
            chargements.push(numero);
            chargement_courant=0;
            charger_previews_numeros(chargements[chargement_courant],true);
        }
        
        function charger_previews_numeros(numero,est_visu) {
            var parametrage=new Object();
            var zoom_utilise= est_visu ? zoom : 1.5;
                
            $('chargement').update('Chargement de la preview de la tranche');
            charger_image('numero','<?=site_url('viewer/index/'.$pays.'/'.$magazine.'/')?>/'+[numero,zoom_utilise,'all',JSON.stringify(parametrage),(est_visu?'false':'true'),'false'].join('/'),numero);
        }
        
        function charger_preview_etape(etapes_preview,est_visu) {
            var parametrage=new Object();
            var zoom_utilise= est_visu ? zoom : 1.5;
                
            if (typeof(etapes_preview) == 'string' || typeof(etapes_preview) == 'number') {
                $('chargement').update('Chargement de la preview de l\'&eacute;tape '+etapes_preview);
                var fond_noir=$('fond_noir_'+etapes_preview).hasClassName('fond_noir_active');
                etapes_preview=new Array(etapes_preview);
            }
            else {
                $('chargement').update('Chargement de la preview de la tranche');
                var fond_noir=false;
            }
            charger_image('etape','<?=site_url('viewer/index/'.$pays.'/'.$magazine.'/')?>/'+[numero_chargement,zoom_utilise,etapes_preview.join("-"),JSON.stringify(parametrage),(est_visu?'false':'save'),fond_noir].join('/'),etapes_preview.join("-"));
        }
        
        function charger_image(type_chargement,src,num) {
            var random=Math.random();
            src+='/'+random;
            var image=new Element('img').addClassName('image_preview').store(type_chargement,num);
            var est_visu=true;
            var cellules_previews=$('contenu_'+onglet_sel.toLowerCase()).select('.ligne_previews .image_'+type_chargement);
            if (type_chargement == 'etape') {
                if (cellules_previews.invoke('retrieve','etape').indexOf(num)==-1) // Numéro d'étape non trouvé
                    cellules_previews.last().update(image);
                else
                    cellules_previews.each(function(element) {
                        if (element.retrieve('etape') == num)
                            element.update(image);
                    });
            }
            else {
                cellules_previews.each(function(element) {
                    if (element.retrieve('numero') == num)
                        element.update(image);
                });
            }
            image.writeAttribute({'src':src});
            image.observe('load',function(ev) {
                var image=Event.element(ev);
                image.observe('click',function(ev) {
                    var image=Event.element(ev);
                    fixer_regles(image);
                });
                if (image.readAttribute('src').indexOf('/save') != -1) {
                    alert('Image enregistree');
                    $('ligne_'+numero_chargement).addClassName('tranche_prete');
                }
                if (est_visu) {
                    //$('regle').writeAttribute({'height':(300*val_zoom)});
                    $('chargement').update();
                    $('erreurs').update();
                    chargement_courant++;
                    if (chargement_courant < chargements.length) {
                        if (type_chargement=='etape')
                            charger_preview_etape(chargements[chargement_courant],true);
                        else
                            charger_previews_numeros(chargements[chargement_courant],true);
                    }
                    else {
                        fixer_regles(null);
                        $$('.ligne_previews .image_etape img')
                            .invoke('observe','mouseover', function(ev) {
                           $('chargement').setStyle({'display':'block'});
                           var tranche=Event.element(ev);
                           tranche.setStyle({'cursor':'crosshair'});
                           var nom_option_sel=get_nom_option_sel();
                           if (nom_option_sel != null) {
                               var valeur_modifiee_actuelle=$F($('valeur_modifiee').down('input')) * zoom;
                               if (nom_option_sel.match(new RegExp(/_x$/)) != null) {
                                   $('viewer').insert(new Element('div').addClassName('repere')
                                                        .setStyle({'position':'absolute','top':tranche.cumulativeOffset()['top']+'px','width': '1px', 
                                                                   'borderLeft':'2px solid black', 'height': tranche.height+'px',
                                                                   'left': parseInt(tranche.cumulativeOffset()['left']+valeur_modifiee_actuelle)+'px'}));
                               }
                               else 
                               if (nom_option_sel.match(new RegExp(/_y$/)) != null) {
                                   $('viewer').insert(new Element('div').addClassName('repere')
                                                        .setStyle({'position':'absolute','left':tranche.cumulativeOffset()['left']+'px','height': '1px', 
                                                                   'borderTop':'2px solid black', 'width': tranche.width+'px',
                                                                   'top': parseInt(tranche.cumulativeOffset()['top']+valeur_modifiee_actuelle)+'px'}));
                               }
                               adapter_scroll_reperes();
                           }
                           $('chargement').update('X = ')
                                          .insert(new Element('span',{'id':'X'}))
                                          .insert(new Element('br'))
                                          .insert('Y = ')
                                          .insert(new Element('span',{'id':'Y'}));
                        })
                            .invoke('observe','mouseout',function(ev) {
                           $('chargement').update();
                           var tranche=Event.element(ev);
                           tranche.setStyle({'cursor':''});
                           $$('.repere').invoke('remove');
                        })
                            .invoke('observe','mousemove',function(ev) {
                           var tranche=Event.element(ev);
                           var x =Event.pointerX(ev) + $('viewer_inner').scrollLeft - tranche.cumulativeOffset()['left'];
                           var y =Event.pointerY(ev) + $('viewer_inner').scrollTop- tranche.cumulativeOffset()['top'];
                           
                           var x_valeur = toAlwaysFloat(parseInt(10 * x/zoom)/10);
                           var y_valeur = toAlwaysFloat(parseInt(10 * y/zoom)/10);
                           var x_pct = toAlwaysFloat(parseInt(1000 * x / tranche.width)/10);
                           var y_pct = toAlwaysFloat(parseInt(1000 * y / tranche.height)/10);
                           $('X').update(x_valeur+'mm (Largeur x '+x_pct+'%)');
                           $('Y').update(y_valeur+'mm (Hauteur x '+y_pct+'%)');
                        });
                    }
                }
                    
            });
            image.observe('error',function(event) {
                if (est_visu) {
                    //$('regle').writeAttribute({'height':0});
                    $('chargement').update('Erreur !');
                    $('erreurs').update(new Element('iframe',{'src':Event.element(event).src+'/debug'}));
                    chargement_courant++;
                    if (chargement_courant < chargements.length) {
                        if (type_chargement=='etape')
                            charger_preview_etape(chargements[chargement_courant],true);
                        else
                            charger_previews_numeros(chargements[chargement_courant],true);
                    }
                }
            });
        }
        
        function toAlwaysFloat(val) {
            return parseInt(val) == parseFloat(val) ? val+'.0' : val;
        }

        function marquer_cellules(first_cell,last_cell) {
            if (!colonne_ouverte)
                etape_en_cours=$$('.ligne_etapes')[0].down('td',first_cell.previousSiblings().length).retrieve('etape');
            $$('.selected.tmp').invoke('removeClassName','selected tmp');
            var texte_erreurs=new Array('Erreur : ');
               
            var pos_colonne=first_cell.previousSiblings().length;
            $$('td.selected').each(function(selected) {
                if (selected.previousSiblings().length != pos_colonne) // Une cellule pr?demment s?ctionn?n'est pas dans la m? colonne que celle(s) s?ctionn?s) maintenant
                    selected.removeClassName('selected');
            });
            if (first_cell.up('tr').previousSiblings().length > last_cell.up('tr').previousSiblings().length) { // Echange de la 1ere et derniere cellule
                var temp_cell=first_cell;
                first_cell=last_cell;
                last_cell=temp_cell;
            }
            var current_cell=first_cell;
            while (true) {
                if (!(current_cell.hasClassName('tmp'))) {
                    current_cell.toggleClassName('selected');
                    current_cell.addClassName('tmp');
                }
                if (current_cell == last_cell) // Derni? cellule de la s?ction
                    break;
                if (current_cell.up('tr').nextSiblings().length==0) // Derni? ligne du tableau
                    break;
                current_cell=current_cell.up('tr').next().down('td',pos_colonne);
            }
            if ($$('td.selected').length > 0) {
                var texte=new Element('div').insert(new Element('span').setStyle({'fontWeight':'bold'}).update($$('td.selected').length+' num&eacute;ro(s) s&eacute;lectionn&eacute;(s)'))
                                            .insert(new Element('br'));
                var nom_option=get_nom_option_sel();
                texte.insert('Option : '+nom_option)
                     .insert(new Element('br'))
                     .insert('Valeurs actuelles :');
                var liste_valeurs=new Element('ul');
                $A(sans_doublons($$('td.selected'))).each(function(td_sel) {
                    liste_valeurs.insert(new Element('li').insert(formater_valeur(new Element('div'),nom_option,td_sel.retrieve('valeur_reelle'))));
                    td_sel.classNames().each(function(className) {
                       switch(className) {
                           case 'erreur_valeurs_multiples':
                               texte_erreurs.push('Valeurs multiples : '+td_sel.retrieve('valeur_reelle').split('--').join(' et '));
                       }
                   });
                });
                texte.insert(liste_valeurs);
                var section_modifier_valeur=new Element('div').writeAttribute({'id':'modifier_valeur'})
                                                              .insert('Modifier la valeur : ')
                                                              .insert(new Element('br'))
                                                              .insert(new Element('div').writeAttribute({'id':'valeur_modifiee'}));
                
                $('helpers').update(texte).insert(section_modifier_valeur);
                var succes_formatage=formater_modifier_valeur(nom_option);
                if (succes_formatage) {
                    section_modifier_valeur.insert(new Element('button',{'id':'modifier_valeur_ok'}).update('OK'));
                    section_modifier_valeur.insert(new Element('br'))
                                           .insert(new Element('input',{'type':'checkbox','id':'reload_after_update','checked':'checked'}))
                                           .insert('Recharger apr&egrave;s');
                    $('modifier_valeur_ok').observe('click',function() {
                        $('chargement').update('Enregistrement des param&egrave;tres...');
                        var numeros=element_to_numero($$('td.selected').invoke('up','tr')).join('~');
                        var nouvelle_valeur=escape(get_nouvelle_valeur(nom_option)).replace(/%/g,'!');
                        new Ajax.Request('<?=site_url('modifierg')?>/'+['index',pays,magazine,etape_en_cours,numeros,nom_option,nouvelle_valeur,plage.join('/'),nom_nouvelle_fonction==null?'Dimensions':nom_nouvelle_fonction].join('/'), {
                            method: 'post',
                            onSuccess:function() {
                                $('chargement').update();
                                var recharger_etape = nom_option != 'Actif' && ($('reload_after_update') && $('reload_after_update').checked) ;
                                var est_etape_temporaire = parseInt(etape_en_cours) != etape_en_cours;
                                if (nom_option=='Actif')
                                    $$('td.selected').invoke(nouvelle_valeur=='on' ? 'addClassName':'removeClassName','num_checked');
                                else if (!est_etape_temporaire) {
                                    if (numeros.split('~').indexOf($('numero_preview').retrieve('numero')) != -1 && recharger_etape) {
                                        if (etape_en_cours == -1) {
                                            var num_etapes=$$('.num_etape_preview').invoke('retrieve','etape');
                                            chargements=num_etapes;
                                            chargement_courant=0;
                                            charger_preview_etape(chargements[chargement_courant],true);
                                        }
                                        reload_etape(etape_en_cours);
                                    }
                                }
                                if (est_etape_temporaire) // Etape temporaire
                                    etape_en_cours=etape_temporaire_to_definitive(etape_en_cours);
                                if (recharger_etape)
                                    charger_etape(etape_en_cours,null);
                            }
                        });
                      });
                }
                else {
                    section_modifier_valeur.insert('L\'un au moins des num&eacute;ros s&eacute;lectionn&eacute;s n\'est pas d&eacute;fini pour cette &eacute;tape.')
                                           .insert(new Element('br'))
                                           .insert('Commencez par d&eacute;finir l\'&eacute;tape comme active pour ce num&eacute;ro.');
                }
            }
            else
                $('helpers').update();

            jscolor.init();
            
            if (texte_erreurs.length == 1)
                $('erreurs').update();
            else
                $('erreurs').update(texte_erreurs.join('<br />'));
        }
        
        function get_nom_option_sel() {
            if ($$('td.selected').length==0)
                return null;
            var pos_colonne_sel=$$('td.selected')[0].previousSiblings().length;
            var nom_option=$$('.ligne_noms_options')[0].down('td',pos_colonne_sel).retrieve('nom_option');
            if (typeof(nom_option) == 'undefined' || nom_option == '')
                nom_option='Actif';
            return nom_option;
        }

        function etape_temporaire_to_definitive(etape_temp) {
            var etape=etape_temp;
            if (etape_temp != parseInt(etape_temp)) // Passage d'?pe temporaire ??nitive'
                etape=parseInt(etape_temp)+1;
            $$('[name="entete_etape_'+etape_temp+'"]').invoke('writeAttribute',{'name':'entete_etape_'+etape})
                                                      .invoke('store','etape',etape)
                                                      .invoke('update','Etape '+etape);
            return etape;
        }

        function sans_doublons(tab){
            NvTab= new Array();
            var q=0;
            tab.each(function(x){
                if (NvTab.invoke('retrieve','valeur_reelle').indexOf(x.retrieve('valeur_reelle')) == -1)
                    NvTab[q++]=x;
            });
            return NvTab;
        }

        var types_options=new Array();
        types_options['Actif']='actif';
        
        function formater_valeur(td,nom_option,valeur) {
            if (typeof (valeur) == 'undefined')
                valeur='[Non d&eacute;fini]';

            else if (nom_option.indexOf('Couleur') != -1) {
                if (valeur.indexOf(',') == -1)
                    var rgb=[hexToR(valeur),hexToG(valeur),hexToB(valeur)];
                else
                    var rgb=valeur.split(new RegExp(/,/g));
                var couleur_texte =  0.213 * rgb[0] +
                                     0.715 * rgb[1] +
                                     0.072 * rgb[2]
                                     < 0.5 ? '#FFF' : '#000';
                td.setStyle({'backgroundColor':'rgb('+rgb.join(',')+')',
                             'color':couleur_texte});
            }
            else if (nom_option.indexOf('Dimension') != -1 || nom_option.indexOf('Pos_x') != -1 || nom_option.indexOf('Pos_y') != -1)
                valeur+=' mm';
            else if (nom_option.indexOf('Compression') != -1)
                valeur=valeur*100+'%';
            else if (nom_option.indexOf('Rotation') != -1)
                valeur+='&deg;';
            td.update(valeur);
            return td;
        }
        
        function hexToR(h) {return parseInt((cutHex(h)).substring(0,2),16)}
        function hexToG(h) {return parseInt((cutHex(h)).substring(2,4),16)}
        function hexToB(h) {return parseInt((cutHex(h)).substring(4,6),16)}
        function cutHex(h) {return (h.charAt(0)=="#") ? h.substring(1,7):h}

        function formater_modifier_valeur(nom_option) {
            if (nom_option=='Actif') {
                $('valeur_modifiee').update(new Element('input').writeAttribute({'type':'checkbox','checked':'checked'}))
                                    .insert('&nbsp;Utilis&eacute;');
                return true;
            }
            if ($$('td.selected').invoke('retrieve','valeur_reelle').indexOf(undefined) != -1) { // Au moins un des num?s n'est pas d?ni pour cette ?pe'
                return false;
            }
            var premiere_valeur_sel=$$('td.selected')[0].retrieve('valeur_reelle');
            //var valeur_defaut = typeof(valeurs_defaut_options[nom_option]) == 'undefined' ? '' : valeurs_defaut_options[nom_option];
            
            switch(types_options[nom_option]) {
                case 'couleur':
                    $('valeur_modifiee').update(new Element('input').addClassName('color')
                                                                    .writeAttribute({'type':'text','value':premiere_valeur_sel}));
                break;
                case 'liste':
                    if (nom_option.indexOf('Police') != -1 && !$('liste_polices')) {
                        new Ajax.Request('<?=base_url()?>system/fonts/ttflist.php', {
                            method: 'get',
                            parameters:'select=true',
                            onSuccess:function(transport) {
                                $('valeur_modifiee').update(transport.responseText);
                                $('liste_polices').selectedIndex=$('liste_polices').down('option[value="'+premiere_valeur_sel+'"]').index;
                            }
                        });
                    }
                    else {
                        new Ajax.Request('<?=site_url('listerg')?>/'+['index',nom_option].join('/'), {
                            method: 'post',
                            parameters:'select=true',
                            onSuccess:function(transport) {
                                var select = new Element('select');
                                for(var nom in transport.headerJSON) {
                                    var option=new Element('option',{'value':nom}).update(transport.headerJSON[nom]);
                                    if (option==premiere_valeur_sel)
                                        option.writeAttribute({'selected':'selected'});
                                    select.insert(option);
                                }
                                $('valeur_modifiee').update(select);
                            }
                        });
                    }
                break;
                default:
                    $('valeur_modifiee').update(new Element('input').writeAttribute({'type':'text','value':premiere_valeur_sel}));
                break;
            }

            return true;
            /*
            $('valeur_modifiee').update('Chargement...');
            new Ajax.Request('<?=site_url('parametrageg')?>/index/'+pays+'/'+magazine, {
                method: 'post',
                onSuccess:function(transport) {
                    $('valeur_modifiee').update(transport.responseText);
                }
            });*/
        }

        function get_nouvelle_valeur(nom_option) {
            switch(types_options[nom_option]) {
                default:
                    return $F($('valeur_modifiee').down());
                break;
            }
        }

        var valeurs_defaut_options;
        var etapes_utilisees=new Array();
        var etapes_valides=new Array();
        var etape_en_cours=null;

        var plage=new Array('null','null');

        var nb_lignes=null;
        
        var image_ajouter=new Element('img',{'title':'Ajouter une etape','src':'<?=base_url()?>system/application/views/images/ajouter.png'})
                                     .addClassName('ajouter_etape');
        var image_supprimer=new Element('img',{'title':'Supprimer l\'etape','src':'<?=base_url()?>system/application/views/images/supprimer.png'})
                                     .addClassName('supprimer_etape');

        var num_etape_avant_nouvelle=null;

        var parametres_helper=new Object();

        var onglet_sel=null;
        
        new Event.observe(window, 'load',function() {
            $$('.tabnav a').invoke('observe','click',function(ev) {
                var element=Event.element(ev);
                toggle_item_menu(element);
            });
            new Resizeable($('viewer'));
            toggle_item_menu($('Builder'));
            $('chargement').update('Chargement de la liste INDUCKS...');
            new Ajax.Request('<?=site_url('numerosdispos')?>/index/'+pays+'/'+magazine, {
                method: 'post',
                onSuccess:function(transport) {
                    if (transport.responseText.indexOf('Nombre d\'arguments insuffisant') != -1) {
                        $('nom_magazine').update('Utilisez un nom de magazine valide');
                        return;
                    }
                    var nb_numeros_dispos=transport.headerJSON.nb_numeros_dispos;
                    numeros_dispos=transport.headerJSON.numeros_dispos;
                    var tranches_pretes=transport.headerJSON.tranches_pretes;
                    var nb_etapes=transport.headerJSON.nb_etapes;
                    $('nom_magazine').update(transport.headerJSON.nom_magazine);

                    var table=new Element('table',{'id':'table_numeros'}).addClassName('bordered').writeAttribute({'border':'1'})
                                .insert(new Element('tr').addClassName('ligne_entete ligne_etapes')
                                                         .insert(new Element('td'))
                                                         .insert(new Element('td'))
                                                         .insert(new Element('td')) // Cellule temporaire
                                       )
                                .insert(new Element('tr').addClassName('ligne_entete ligne_noms_options')
                                                         .insert(new Element('td'))
                                                         .insert(new Element('td'))
                                                         .insert(new Element('td'))
                                       );
                    $('corps').insert(table);

                    if (nb_etapes * (nb_numeros_dispos-1) >= 1000) {
                        restriction_plage();
                    }

                    var debut_plage_atteint=false;
                    var fin_plage_atteint=false;
                    for (var numero_dispo in numeros_dispos) {
                        if (plage[0] != 'null') {
                            if (debut_plage_atteint) {
                                if (fin_plage_atteint)
                                    break;
                                if (numero_dispo == plage[1])
                                    fin_plage_atteint=true;
                            }
                            else {
                                if (numero_dispo == plage[0])
                                    debut_plage_atteint=true;
                                else
                                    continue;
                            }
                        }
                        if (numero_dispo == 'Aucun')
                            continue;
                        var td_cloner=new Element('td');
                        var image_cloner = new Element('img').writeAttribute({'src':'<?=base_url()?>system/application/views/images/clone.png','title':'Cloner le numero'})
                                                    .addClassName('cloner');
                        
                        td_cloner.update(image_cloner);
                        var tr=new Element('tr').writeAttribute({'id':'ligne_'+numero_dispo}).addClassName('ligne_dispo').store('numero',numero_dispo)
                                                .insert(td_cloner);
                        if (typeof(tranches_pretes[numero_dispo]) != 'undefined') {
                            tr.addClassName('tranche_prete');
                            tr.writeAttribute({'title':'Cette tranche est deja prete'});
                        }
                        var td=new Element('td').addClassName('intitule_numero')
                                                .insert(numero_dispo).insert('&nbsp;');
                        var span_preview=new Element('span').addClassName('preview')
                            .update(new Element('img',{'src':'<?=base_url()?>system/application/views/images/view.png',
                                                       'title':'Voir la tranche'}).addClassName('view_preview'));                        
                        tr.insert(td.insert(span_preview))
                        table.insert(tr);
                        span_preview.observe('click',function(event) {
                            preview_numero(Event.element(event));
                        });
                        
                        image_cloner.store('numero',numero_dispo)
                                    .observe('click',cloner_numero);
                                    
                        var td_temp=new Element('td');
                        tr.insert(td_temp);
                    }
                    $('chargement').update('Chargement des &eacute;tapes...');
                    table.insert($$('.ligne_noms_options')[0].clone(true))
                         .insert($$('.ligne_etapes')[0].clone(true));
                    new Ajax.Request('<?=site_url('parametrageg')?>/index/'+pays+'/'+magazine+'/null/null', {
                        method: 'post',
                        onSuccess:function(transport) {
                            var etapes=transport.headerJSON;
                            nb_lignes = $('table_numeros').select('tr').length;
                            etapes_valides=new Array();
                            $$('#table_numeros tr:not(.ligne_entete)').each(function(tr) {
                                for (var etape=0;etape<etapes.length;etape++) {
                                    if (etapes[etape].Ordre == -1 || est_dans_intervalle(tr.retrieve('numero'), etapes[etape].Numero_debut+'~'+etapes[etape].Numero_fin)) {
                                        if (etapes_valides.indexOf(etapes[etape]) == -1) {
                                            etapes_valides.push(etapes[etape]);
                                            continue;
                                        }
                                    }
                                }
                            });
                            etapes_valides.sort(function(etape1,etape2) {
                                if (etape1.Ordre<etape2.Ordre)
                                    return -1;
                                if (etape1.Ordre>etape2.Ordre)
                                    return 1;
                                return 0;
                            });
                            $('table_numeros').select('tr').each(function(tr) {
                                for (var i=0;i<etapes_valides.length;i++) {
                                    charger_etape_ligne(etapes_valides[i],tr);
                                }
                            });
                            
                            $$(selecteur_cellules).each(function(td) {
                                td.store('valeur_reelle',td.hasClassName('num_checked') ? 'Utilis&eacute;' : 'Non utilis&eacute;');
                                td.store('etape',$$('.ligne_etapes')[0].down('td',td.previousSiblings().length).retrieve('etape'));
                            });


                            reload_observers_etapes();

                            $$('.supprimer_etape').invoke('observe','click',function (event) {

                                var element=Event.element(event).up('td');
                                var num_etape_a_supprimer=element.retrieve('etape');
                                if (confirm('Etes vous sur(e) de vouloir supprimer l\'etape '+num_etape_a_supprimer+" ?")) {
                                    new Ajax.Request('<?=site_url('supprimerg')?>/index/'+pays+'/'+magazine+'/'+num_etape_a_supprimer, {
                                        method: 'post',
                                        onSuccess:function() {
                                            document.location.reload();
                                        }
                                    });
                                }
                            });

                            $$('.ajouter_etape').invoke('observe','click',function (event) {
                                if ($$('.lien_etape[id$=".5"]').length > 0) {
                                    alert('Une etape est deja en train d\'etre ajoutee');
                                    return;
                                }
                                var element=Event.element(event).up('td');
                                num_etape_avant_nouvelle=element.retrieve('etape');
                                if (confirm('Vous allez ajouter une etape apres l\'etape '+num_etape_avant_nouvelle+'\nContinuer ?')) {
                                    fermer_etapes();
                                    var liste_possibilites=new Element('select',{'id':'liste_possibilites_fonctions'});
                                    'Dimensions','Remplir','Agrafer','TexteTTF','TexteMyFonts','Image','Polygone','Degrade','DegradeTrancheAgrafee','Rectangle','Arc_cercle'
                                    if ($$('[name="entete_etape_-1"]').length >0) {
                                        liste_possibilites.insert(new Element('option',{'title':'Remplir'}).update('Remplir une zone avec une couleur'))
                                                          .insert(new Element('option',{'title':'Degrade'}).update('Remplir une zone avec un d&eacute;grad&eacute;'))
                                                          .insert(new Element('option',{'title':'Agrafer'}).update('Agrafer la tranche'))
                                                          .insert(new Element('option',{'title':'DegradeTrancheAgrafee'}).update('Remplir la tranche avec un d&eacute;grad&eacute; et l\'agrafer'))
                                                          .insert(new Element('option',{'title':'Texte'}).update('Ajouter du texte'))
                                                          .insert(new Element('option',{'title':'Image'}).update('Ins&eacute;rer une image'))
                                                          .insert(new Element('option',{'title':'Rectangle'}).update('Dessiner un rectangle'))
                                                          .insert(new Element('option',{'title':'Polygone'}).update('Dessiner un polygone'))
                                                          .insert(new Element('option',{'title':'Arc_cercle'}).update('Dessiner un arc de cercle'));
                                    }
                                    else
                                        liste_possibilites.update(new Element('option',{'title':'Dimensions'}).update('Sp&eacute;cifier les dimensions d\'une tranche'))
                                    var bouton_ok=new Element('button').update('OK');
                                    $('helpers').update('Si ce n\'est pas encore fait, prenez en photo avec un appareil photo num&eacute;rique la tranche que vous souhaitez recr&eacute;er.')
                                                   .insert(new Element('br'))
                                                   .insert('Stockez cette photo sur votre ordinateur, vous allez en avoir besoin !')
                                                   .insert(new Element('br'))
                                                   .insert('Que voulez-vous faire ? ')
                                                   .insert(new Element('br'))
                                                   .insert(liste_possibilites)
                                                   .insert(bouton_ok);
                                    bouton_ok.observe('click',function() {
                                        var name_sel=$('liste_possibilites_fonctions').down($('liste_possibilites_fonctions').selectedIndex).title;
                                        var nom_helper='';
                                        switch(name_sel) {
                                            case 'Texte':
                                                nom_helper='whatthefont';
                                            break;
                                            default:
                                                nom_helper=name_sel.toLowerCase();
                                            break;
                                        }
                                        charger_helper(nom_helper+'_1','helper_'+nom_helper,name_sel);
                                    });
                                    /*new Ajax.Request('<?=site_url('ajout')?>/index/'+pays+'/'+magazine+'/'+num_etape+'/'+nom_fonction+'/'+numero_debut.join(';')+'/'+numero_fin.join(';'), {
                                        method: 'post',
                                        onSuccess:function(transport) {

                                        }
                                    });*/

                                }
                            });
                            <?php if (!is_null($etape_ouverture)) {
                               /* ?>charger_etape(<?=$etape_ouverture?>,null);
                            <?php */}?>

                            reload_observers_cells();
                            $('chargement').update();

                            Event.observe(window, 'scroll', function() {
                                setupFixedTableHeader();
                            });
                        }
                    });
                }
            });

            var zoom_slider = $('zoom_slider');
            new Control.Slider(zoom_slider.down('.handle'), zoom_slider, {
              values : [1,1.5,2,4,8],
              range: $R(1,8),
              sliderValue: 1.5,
              onChange: function(value) {
                $('zoom_value').update(value);
                zoom=value;
                if (onglet_sel == 'Builder') {
                    if ($('numero_preview').retrieve('numero') != null)
                        preview_numero($('ligne_'+$('numero_preview').retrieve('numero')).down('.intitule_numero'));
                }
                else {
                   var numero_debut = $('numero_preview_debut').retrieve('numero');
                   var numero_fin = $('numero_preview_fin').retrieve('numero'); 
                   
                   var numero=numero_debut;
                   var chargements=new Array();
                   do {
                       var numero_fin_depasse = numero==numero_fin;
                       chargements.push(numero);
                       numero = $('ligne_'+numero).next().retrieve('numero');
                   } while (!numero_fin_depasse);
                   
                   chargement_courant=0;
                   charger_previews_numeros(chargements[chargement_courant],true);
                }
                $$('.regle').invoke('setStyle',{'display':'none'});
              },
              onSlide: function(value) {
                $('zoom_value').update(value);
              }
            });
            $('zoom_value').update(zoom);
            
            
            $$('.option_previews input').invoke('observe','click',function(ev) {
                var element=Event.element(ev);
                switch (element.id) {
                    case 'option_details':
                        $('contenu_previews').select('.numero_preview, .reload')
                            .invoke('setStyle',{'display':element.checked ? 'block' : 'none'});
                    break;
                    case 'option_pretes_seulement':
                        
                    break;
                }
            });
            
            $('save_png').observe('click',function() {
               if (typeof (numero_chargement) != null) {
                   var num_etapes_final=$$('.num_etape_preview:not(.final)').invoke('retrieve','etape');
                    chargements=new Array();
                    chargements.push(num_etapes_final);
                    chargement_courant=0;
                    charger_preview_etape(chargements[chargement_courant],false);
               }
            });
            
            $('toggle_helpers').observe('click',function() {
                $('toggle_helpers').update(
                    ($('infos').hasClassName('cache') ? 'Cacher':'Montrer') 
                   +' l\'assistant');
               $('infos').toggleClassName('cache');
            });
            
            $('viewer_inner').observe( 'scroll', function() {
                adapter_scroll_reperes();
            });
        });

    function adapter_scroll_reperes() {
        $$('.repere').each(function(repere) {
            repere.setStyle({'marginTop':((-1)*$('viewer_inner').scrollTop)+'px',
                             'marginLeft':((-1)*$('viewer_inner').scrollLeft)+'px'});
        });
    }

    function toggle_item_menu (element) {
        onglet_sel = element.id;
        element=element.tagName=='LI' ? element : element.up();
        element.up().select('li.active').invoke('removeClassName','active');
        $(element).toggleClassName('active');
        element.up().select('li a').pluck('name').each(function(nom) {
            $('contenu_'+nom).setStyle({'display':'none'});
        });
        $('contenu_'+element.down().name).setStyle({'display':'block'});
        
        //Sp?fique EdgeCreator
        if (onglet_sel=='Builder')
            var titre_image_view='Voir la composition de cette tranche';
        else
            titre_image_view='Selectionner le premier numero a previsualiser';
        changer_titres_images_view(titre_image_view);
    }
    
    function changer_titres_images_view(titre_image_view){
        $$('.preview img').invoke('writeAttribute',{'title':titre_image_view});
    }

        function removeFixedTableHeader() {
            $$('.header_fixe').invoke('remove');
        }

        function setupFixedTableHeader() {
            var setup=$('body').scrollTop >= $('table_numeros').cumulativeOffset()['top'] ; // Scroll en-dessous du header de la table
                
            if ($$('.header_fixe').length > 0) {
                if (setup)
                    $$('.header_fixe').invoke('removeClassName','cache');
                else
                   $$('.header_fixe').invoke('addClassName','cache');
            }
            if ($$('.header_fixe').length == 0 && setup) {
                var div=new Element('div')
                          .insert($('table_numeros')
                                    .down('tr').clone(true)
                                    .addClassName('header_fixe')
                                )
                          .insert($('table_numeros')
                                    .down('tr',1).clone(true)
                                    .addClassName('header_fixe')
                                )
                          .setStyle({'left':$('table_numeros').cumulativeOffset()['left']+$('table_numeros').offsetLeft,
                                     'position':'fixed','display':'table',
                                     'borderSpacing': '2px 2px',
                                     'backgroundColor':'white'});
                $('body').insert(div);
                $$('.header_fixe').invoke('setStyle',{'width':'','height':''});
                $$('.header_fixe').each(function(header_fixe) {
                    var i=0;
                    header_fixe.select('td').each(function(td) {
                        td.writeAttribute({'width':$('table_numeros').down('tr',header_fixe.hasClassName('ligne_etapes') ? 0 : 1)
                                                                     .down('td',i).offsetWidth})
                          .addClassName('header_fixe_col');
                        i++;
                    });
                });
                reload_observers_etapes();
            }
        }

        function charger_etape_ligne (etape, tr) {
            var num_etape=etape.Ordre;
            if (num_etape==-1) { // td d? existant
                var td=tr.down('td',2);
            }
            else {
                var td=new Element('td');
                if (num_etape != parseInt(num_etape)) {// Nouvelle ?pe
                    tr.down('td',$$('[name="entete_etape_'+parseInt(num_etape-.5)+'"]')[0].previousSiblings().length).insert({'after':td});
                    //num_etape-=.5;
                }
                else
                    tr.insert(td);
            }
            switch(tr.previousSiblings().length) {
                case 0: case nb_lignes-1:// Ligne des ?pes

                    var nom_fonction=etape.Nom_fonction;
                    td.addClassName('lien_etape')
                      .update(image_supprimer.clone(true))
                      .insert(new Element('span')
                        .setStyle({'whiteSpace':'nowrap'})
                        .update(num_etape == -1 ? 'Dimensions' : ('Etape '+num_etape)))
                      .insert(new Element('br'))
                      .insert(new Element('img',{'height':18,'src':'<?=base_url()?>system/application/views/images/'+nom_fonction+'.png',
                                                 'title':nom_fonction,'alt':nom_fonction}).addClassName('logo_option'))
                      .insert(image_ajouter.clone(true))
                      .store('etape',num_etape)
                      .writeAttribute({'name':'entete_etape_'+num_etape});
                break;
                case 1: case nb_lignes-2 :// Ligne des options, vide
                break;
                default:
                    if (est_dans_intervalle(tr.retrieve('numero'), etape.Numero_debut+'~'+etape.Numero_fin))
                        td.update().addClassName('num_checked');
                break;
            }
        }

        var numero_a_cloner=null;

        function cloner_numero (ev) {
            var numero = Event.element(ev).retrieve('numero');
            if (numero_a_cloner == null) {
                numero_a_cloner=numero;
                alert('Vous allez cloner le numero '+numero_a_cloner+'\n'
                     +'Selectionnez le numero vers lequel cloner ses informations');
            }
            else {
                $('chargement').update('Clonage en cours...');
                var nouveau_numero=numero;
                new Ajax.Request('<?=site_url('etendre')?>/index/'+pays+'/'+magazine+'/'+numero_a_cloner+'/'+nouveau_numero, {
                    method: 'post',
                    onSuccess:function() {
                        document.location.reload();
                    },
                    onFailure:function() {
                        numero_a_cloner=null;
                        alert('Erreur');
                    }
                });
            }
        }

        var nom_nouvelle_fonction=null;

        function fermer_etapes() {
            $$('.ligne_noms_options')[0].select('.option_etape').each(function (colonne_entete) {
                var num_colonne=colonne_entete.previousSiblings().length;
                $$('.ligne_dispo,.ligne_noms_options').each(function(ligne) {
                    ligne.down('td',num_colonne).remove();
                });
            });
            $$('.lien_etape').invoke('writeAttribute',{'colspan':1});
        }

        function charger_etape(num_etape) {
            var element=$$('[name="entete_etape_'+num_etape+'"]:not(.header_fixe_col)')[0];
            fermer_etapes();

            var num_colonne=element.previousSiblings().length;
            $('chargement').update('Chargement des param&egrave;tres de l\'&eacute;tape '+num_etape+'...');
            removeFixedTableHeader();
            new Ajax.Request('<?=site_url('parametrageg')?>/'+['index',pays,magazine,num_etape,nom_nouvelle_fonction==null?'null':nom_nouvelle_fonction].join('/'), {
                method: 'post',
                parameters: 'etape='+num_etape,
                onSuccess:function(transport) {
                    colonne_ouverte=true;
                    etape_en_cours=num_etape;
                    var nb_options=Object.values(transport.headerJSON).length;
                    
                    $$('.ligne_etapes').each(function(ligne_etape) {
                        ligne_etape.down('td',num_colonne)
                                   .writeAttribute({'colspan':parseInt(ligne_etape.down('td',num_colonne).readAttribute('colspan'))+nb_options});
                    });
                    var i=0;
                    var contenu;
                    var texte='';
                    types_options=new Array();
                    valeurs_defaut_options=new Array();
                    for (var option_nom in transport.headerJSON) {
                        types_options[option_nom]=transport.headerJSON[option_nom]['type'];
                        //if (typeof(transport.headerJSON[option_nom]['valeur_defaut']) != 'undefined')
                        //    valeurs_defaut_options[option_nom]=transport.headerJSON[option_nom]['valeur_defaut'];

                        $$('.ligne_noms_options').each(function(ligne) {
                            var nouvelle_cellule=new Element('td')
                                                    .addClassName('etape_'+num_etape+'__option')
                                                    .addClassName('option_etape');
                            contenu=option_nom;
                            nouvelle_cellule.insert(contenu)
                                            .store('nom_option',option_nom);
                            ligne.down('td',num_colonne+i).insert({'before':nouvelle_cellule});
                        });
                        i++;
                    }

                    i=0;
                    for (var option_nom in transport.headerJSON) {
                        $$('.ligne_dispo').each(function(ligne) {
                            nouvelle_cellule=new Element('td');
                            texte=new Array();
                            var numero=ligne.retrieve('numero');
                            var etape_utilisee=etapes_utilisees[num_etape][numero];
                            if (etape_utilisee) {
                                if (typeof(transport.headerJSON[option_nom])=='string')
                                    transport.headerJSON[option_nom]=new Array(transport.headerJSON[option_nom]);
                                for (var intervalle in transport.headerJSON[option_nom]) {
                                    if (intervalle != 'type' && intervalle != 'valeur_defaut') {
                                        if (intervalle == 0)
                                            texte.push(transport.headerJSON[option_nom]);
                                        else if (est_dans_intervalle(numero, intervalle)) {
                                            texte.push(transport.headerJSON[option_nom][intervalle]);
                                        }
                                    }
                                }
                                if (texte.length > 1) {
                                    nouvelle_cellule.addClassName('erreur_valeurs_multiples');
                                }
                                texte=texte.join('--');
                                nouvelle_cellule=formater_valeur(nouvelle_cellule,option_nom,texte)
                                                 .store('valeur_reelle',texte);
                            }
                            else
                                nouvelle_cellule.update().addClassName('non_concerne');
                            ligne.down('td',num_colonne+i).insert({'before':nouvelle_cellule});

                        });
                        i++;
                    }
                    $('chargement').update();
                    reload_observers_cells();
                    setupFixedTableHeader();
                }
            });
        }

        function restriction_plage() {
            if (confirm('Le nombre d\'informations sur les tranches de ce magazine semble tres important.\n'
                       +'Pour des raisons de fluidite, il est conseille de restreindre la plage de numeros a afficher.\n'
                       +'Voulez vous indiquer une plage de numeros ?')) {
                 var plage_debut_entre=prompt('Entrez le premier numero de la plage');
                 if (plage_debut_entre == null)
                     alert('Operation annulee');
                 else {
                     var plage_fin_entre=prompt('Entrez le dernier numero de la plage');
                     if (plage_fin_entre == null)
                         alert('Operation annulee');
                     else {
                         if (Object.values(numeros_dispos).indexOf(""+plage_debut_entre) == -1) {
                            alert('Le numero de debut ne fait pas partie de la liste Inducks, abandon.');
                         }
                         else if (Object.values(numeros_dispos).indexOf(""+plage_fin_entre) == -1) {
                            alert('Le numero de fin ne fait pas partie de la liste Inducks, abandon.');
                         }
                         else {
                             plage=new Array(plage_debut_entre,plage_fin_entre);
                         }
                     }
                 }
            }
        }

        function charger_helper(nom_helper, nom_div, nom_fonction) {
            $('liste_possibilites_fonctions').selectedIndex = $('liste_possibilites_fonctions').down('[title="'+nom_fonction+'"]').index;
            //if ($$('')))
            if (!$(nom_div))
                $('helpers').insert(new Element('div',{'id':nom_div}));
            new Ajax.Request('<?=base_url()?>system/application/views/helpers/'+nom_helper+'.html', {
                method: 'post',
                parameters: 'nom_helper='+nom_helper,
                onFailure:function() {
                    alert('Page de helper introuvable : '+nom_helper+'.html');
                },
                
                onSuccess:function(transport) {
                    var suivant_existe=transport.responseText.indexOf('...') != -1;
                    var texte=transport.responseText;
                    var nom_fonction_fin=texte.match(new RegExp('!([^!]+)!','g'));
                    var est_dernier=nom_fonction_fin != null;
                    texte=texte.replace(new RegExp('\\.\\.\\.','g'),'')
                               .replace(new RegExp('!([^!]+)!','g'),'');
                    var numero_helper=nom_helper.substring(nom_helper.length-1,nom_helper.length);
                    if (numero_helper>1) {
                        var lien_precedent=new Element('a').update('&lt;&lt; Pr&eacute;c&eacute;dent');
                        $(nom_div).update(new Element('br'))
                                  .insert(lien_precedent);
                        lien_precedent.observe('click',function() {
                            var nom_helper_suivant= nom_helper.substring(0,nom_helper.length-1)+(parseInt(numero_helper)-1);
                            charger_helper(nom_helper_suivant,nom_div,false,nom_fonction);
                        });
                    }
                    else
                        $(nom_div).update();
                    
                    $(nom_div).insert(texte)
                              .store('numero_helper',numero_helper);
                    if (suivant_existe) {
                        var lien_suivant=new Element('a').update('Suivant &gt;&gt;');
                        $(nom_div).insert(lien_suivant);
                        lien_suivant.observe('click',function() {
                            var nom_helper_suivant= nom_helper.substring(0,nom_helper.length-1)+(parseInt(numero_helper)+1);
                            charger_helper(nom_helper_suivant,nom_div,nom_fonction);
                        });
                    }
                    if (est_dernier) {
                        var nouvelle_etape=new Object();
                        nouvelle_etape['Nom_fonction']=nom_fonction_fin[0].replace(new RegExp('!','g'),'');
                        nouvelle_etape['Numero_debut']='';
                        nouvelle_etape['Numero_fin']='';
                        nouvelle_etape['Ordre']=parseInt(num_etape_avant_nouvelle)+.5;
                        nom_nouvelle_fonction=nouvelle_etape['Nom_fonction'];
                        $('table_numeros').select('tr').each(function(tr) {
                            charger_etape_ligne(nouvelle_etape,tr);
                        });
                        reload_observers_etapes();
                        reload_observers_cells();
                    }

                }
            });
        }

    </script>
<title><?=$title?></title>
</head>
<body id="body" style="margin:0;padding:0">
    <?php /*
    $server='87.106.165.63';
    $user='phpmyadmin';
    $database='coa';
    $password='ddelph8';
    if (!$idbase = mysql_pconnect($server, $user, $password))
        echo 'Erreur connexion serveur';
    if (!mysql_select_db($database))
        echo 'Erreur connexion base';*/
    ?>
    <div id="viewer">
        <div id="viewer_inner">
            <div id="zoom_slider" class="slider">
                <div class="handle"></div>
            </div>&nbsp;Zoom : <span id="zoom_value"></span>
            <?php

            include_once(BASEPATH.('/../../Affichage.class.php'));
            $onglets=array(
                'Builder'=>array('builder','Builder'),
                'Previews'=>array('previews','Previews'));
            Affichage::onglets('parametres',$onglets,'previews','.');
            ?>
            <div id="contenu_builder">
                <h1>Builder</h1>
                <div id="numero_preview">Cliquez sur le lien "Preview" d'un num&eacute;ro pour le pr&eacute;visaliser.</div>
                <a style="display:none" id="save_png" href="javascript:void(0)">Enregistrer comme image PNG</a>
                <div class="previews"></div>
            </div>
            <div id="contenu_previews">
                <table style="margin-bottom: 15px">
                    <tr>
                        <td>
                            <h2 style="-webkit-margin-before: 0px;-webkit-margin-after: 0px;">Previews</h2>
                        </td>
                        <td class="option_previews">
                            <input type="checkbox" checked="checked" id="option_details" />D&eacute;tails<br />
                            <input type="checkbox" checked="checked" id="option_pretes_seulement" />Pr&ecirc;tes seulement
                            
                        </td>
                    </tr>
                </table>
                <div id="numero_preview_debut" style="display:inline">
                    Cliquez sur le lien "Preview" d'un num&eacute;ro 
                    pour le s&eacute;lectionner comme premier num&eacute;ro &agrave; pr&eacute;visualiser.
                </div>
                - 
                <div id="numero_preview_fin" style="display:inline"></div>
                <div id="montrer_details">
                </div>
                <div class="previews"></div>
            </div>
        </div>
    </div>
    <div id="corps">
        <h1>Mod&egrave;le de tranche</h1>
        <div id="nom_magazine"></div>
        <br />
    </div>
    <div id="infos">
        <div id="helpers"></div>
    </div>
    <div id="chargement">
    </div>
    <div id="erreurs"></div>
    <a id="toggle_helpers" href="javascript:void(0)">Cacher l'assistant</a>