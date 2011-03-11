<html>
<head>
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style type="text/css">
    #chargement {
        position:fixed;
        right:0;
        top:0;
        background-color:white;
        z-index:500;
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
        width:200px;
        height:100%;
        overflow-x:auto;
        overflow-y:auto;
        border-right:1px solid black;
        background-color: white;
        z-index: 500;
    }
    
    #corps {
        position:absolute;
        left:200px;
        padding-left:10px;
    }

    td .preview {
        cursor:pointer;
        color:blue;
    }
     
    .valeur_reelle {
        display:none;
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
    .lien_etape,.lien_option {
        cursor:pointer;
    }
    
    .num_etape_preview {
        text-align:center;
    }
    
    .cellule_nom_fonction {
        text-align:center;
    }
    
    .pointer {
        cursor:pointer;
    }

    tr:not(.ligne_entete)>td:not(.intitule_numero) {
        white-space: nowrap;
        min-width:25px;
        max-width: 200px;
        overflow-x: auto;
    }

    td.selected {
        border-style:dotted;
    }

    td.reload {
        text-align:center;
    }
    
    div.slider { width:75%; margin:10px 0; background-color:#ccc; height:10px; position: relative; white-space: nowrap; }

    div.slider div.handle { width:10px; height:15px; background-color:#f00; cursor:move; position: absolute; }
    </style>
    <script type="text/javascript" src="<?=base_url()?>system/application/views/js/prototype.js" ></script>
    <script type="text/javascript" src="<?=base_url()?>system/application/views/js/scriptaculous/src/scriptaculous.js" ></script>
    <script type="text/javascript" src="<?=base_url()?>system/application/views/js/json2.js" ></script>
    <script type="text/javascript" src="<?=base_url()?>system/application/views/js/jscolor.js" ></script>
    <script type="text/javascript" src="<?=base_url()?>system/application/views/js/jscolor.js" ></script>
    <script type="text/javascript">
        var first_cell=null;
        var zoom=1.5;
        var pays='<?=$pays?>';
        var magazine='<?=$magazine?>';
        var numeros_dispos;

        function disableselect(e){
            return false
        }

        function reEnable(){
            return true
        }
        function reload_observers_cells() {
            $$('tr:not(.ligne_entete)>td:not(.intitule_numero)').invoke('stopObserving','mousedown');
            $$('tr:not(.ligne_entete)>td:not(.intitule_numero)').invoke('stopObserving','mouseup');
            $$('tr:not(.ligne_entete)>td:not(.intitule_numero)').invoke('stopObserving','mousemove');
            $$('tr:not(.ligne_entete)>td:not(.intitule_numero)').invoke('stopObserving','click');
            
            $$('tr:not(.ligne_entete)>td:not(.intitule_numero)').invoke('observe','mousedown',function(event) {
                $('table_parametres').onselectstart=new Function ("return false")

                //if NS6
                if (window.sidebar){
                        $('table_parametres').onmousedown=disableselect
                        $('table_parametres').onclick=reEnable
                }
                first_cell=Event.element(event);
                if (first_cell.tagName=='DIV')
                    first_cell=first_cell.up();
                marquer_cellules(first_cell,first_cell);
            });
            $$('tr:not(.ligne_entete)>td:not(.intitule_numero)').invoke('observe','mousemove',function(event) {
                if (first_cell != null) {
                    var element=Event.element(event);
                    var this_cell=element;
                    if (this_cell.tagName=='DIV')
                        this_cell=this_cell.up();
                    if (first_cell.previousSiblings().length == this_cell.previousSiblings().length) { // Même colonne
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
        
        function est_dans_intervalle(numero,intervalle) {
            if (numero==null || intervalle=='Tous' || numero==intervalle)
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
            for (i in Object.keys(numeros_debut)) {
                var numero_debut=numeros_debut[i];
                var numero_fin=numeros_fin[i];
                if (numero_debut === numero_fin) {
                    if (numero_debut == numero)
                        return true;
                }
                else {
                    numero_debut_trouve=false;
                    for(numero_dispo in numeros_dispos) {
                        if (numero_dispo==numero_debut)
                            numero_debut_trouve=true;
                        if (numero_dispo==numero && numero_debut_trouve) {
                            return true;
                        }
                        if (numero_dispo==numero_fin) 
                            continue;
                    }
                }
            }
            return false;
        }

        var chargements;
        var chargement_courant;
        var numero_chargement;

        function preview_numero(element) {
            var numero=element.up('tr').readAttribute('id').substring('ligne_'.length,element.up('tr').readAttribute('id').length);
            $('numero_preview').writeAttribute({'name':numero}).update('N&deg; '+numero);
            var table=new Element('table');
            for (var i=0;i<=2;i++) {
                var tr=new Element('tr');
                element.up('tr').select('.num_checked').each(function(td_etape) {
                    var num_etape=td_etape.readAttribute('name').substring('etape_'.length,td_etape.readAttribute('name').length);
                    if (num_etape != -1) {
                        var td=new Element('td');
                        switch(i) {
                            case 0:
                                td.addClassName('reload');
                                var image_reload=new Element('img',{'src':'<?=base_url()?>../images/reload.png'}).addClassName('pointer');
                                td.update(num_etape).writeAttribute({'name':'reload_etape_'+num_etape}).update(image_reload);
                                image_reload.observe('click',function(event) {
                                    var element=Event.element(event).up('td');
                                    var name=element.readAttribute('name');
                                    num_etape=name.substring(name.lastIndexOf('_')+1,name.length);
                                    charger_preview_etape(num_etape,numero);
                                });
                            break;
                            case 1:
                                td.update(num_etape).writeAttribute({'name':'preview_etape_'+num_etape}).addClassName('num_etape_preview');
                            break;
                            case 2:
                                td.writeAttribute({'name':'image_etape_'+num_etape});
                            break;
                        }
                        tr.insert(td);
                     }
                });
                var tranche_finale=new Element('td');
                switch(i) {
                    case 1:
                        tranche_finale.addClassName('num_etape_preview final').update('Tranche');
                    break;
                    case 2:
                        tranche_finale.writeAttribute({'name':'image_etape_final'});
                    break;
                }
                tr.insert(tranche_finale);
                table.insert(tr);
            }
            $('previews').update(table);
            chargements=new Array();
            numero_chargement=numero;
            $$('.num_etape_preview').each(function(td_num_etape) {
                if (td_num_etape.hasClassName('final')) {
                    var num_etape=new Array();
                    $$('.num_etape_preview:not(.final)').each(function(element) {
                        var name_etape=element.readAttribute('name');
                        num_etape.push(name_etape.substring(name_etape.lastIndexOf('_')+1,name_etape.length));
                    });
                }
                else {
                    var name=td_num_etape.readAttribute('name');
                    var num_etape=name.substring(name.lastIndexOf('_')+1,name.length);
                }
                chargements.push(num_etape);
            });
            chargement_courant=0;
            charger_preview_etape(chargements[chargement_courant]);
        }

        
        function charger_preview_etape(etapes_preview) {
            var parametrage=new Object();
            var est_visu=true;
            if (typeof(etapes_preview) == 'string' || typeof(etapes_preview) == 'number')
                etapes_preview=new Array(etapes_preview);
            charger_image('<?=site_url('viewer/index/'.$pays.'/'.$magazine.'/')?>/'+numero_chargement+'/'+zoom+'/'+etapes_preview.join("-")+'/'+JSON.stringify(parametrage)+'/'+(est_visu?'false':'true'),etapes_preview.join("-"));
        }
        
        function charger_image(src,num_etape) {
            var random=Math.random();
            src+='/'+random;
            var image=new Element('img');
            var est_visu=true;
            if (typeof($$('[name="image_etape_'+num_etape+'"]')[0])=='undefined')
                $$('[name="image_etape_final"]')[0].update(image);
            else
                $$('[name="image_etape_'+num_etape+'"]')[0].update(image);
            image.writeAttribute({'title':src,'src':src});
            image.observe('load',function() {
                if (est_visu) {
                    //$('regle').writeAttribute({'height':(300*val_zoom)});
                    $('chargement').update();
                    $('erreurs').update();
                    chargement_courant++;
                    if (chargement_courant < chargements.length)
                        charger_preview_etape(chargements[chargement_courant]);
                }
                    
            });
            image.observe('error',function(event) {
                if (est_visu) {
                    //$('regle').writeAttribute({'height':0});
                    $('chargement').update('Erreur !');
                    $('erreurs').update(new Element('iframe',{'src':Event.element(event).src+'/debug'}));
                    chargement_courant++;
                    if (chargement_courant < chargements.length)
                        charger_preview_etape(chargements[chargement_courant]);
                }
            });
        }

        function marquer_cellules(first_cell,last_cell) {
            $$('.selected.tmp').invoke('removeClassName','selected tmp');
            var texte_erreurs=new Array('Erreur : ');
               
            var pos_colonne=first_cell.previousSiblings().length;
            $$('td.selected').each(function(selected) {
                if (selected.previousSiblings().length != pos_colonne) // Une cellule précédemment sélectionnée n'est pas dans la même colonne que celle(s) sélectionnée(s) maintenant
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
                if (current_cell == last_cell) // Dernière cellule de la sélection
                    break;
                if (current_cell.up('tr').nextSiblings().length==0) // Dernière ligne du tableau
                    break;
                current_cell=current_cell.up('tr').next().down('td',pos_colonne);
            }
            if ($$('td.selected').length > 0) {
                var texte=new Element('div').insert(new Element('span').setStyle({'fontWeight':'bold'}).update($$('td.selected').length+' num&eacute;ro(s) s&eacute;lectionn&eacute;(s)'))
                                            .insert(new Element('br'));
                var nom_option=$('ligne_noms_options').down('td',pos_colonne).retrieve('nom_option');
                if (typeof(nom_option) == 'undefined' || nom_option == '')
                    nom_option='Est utilis&eacute;';
                texte.insert('Option : '+nom_option)
                     .insert(new Element('br'));
                $A(sans_doublons($$('td.selected'))).each(function(td_sel) {
                    texte.insert(new Element('br')).insert(formater_valeur(new Element('td'),nom_option,td_sel.retrieve('valeur_reelle')));
                    td_sel.classNames().each(function(className) {
                       switch(className) {
                           case 'erreur_valeurs_multiples':
                               texte_erreurs.push('Valeurs multiples : '+td_sel.retrieve('valeur_reelle').split('--').join(' et '));
                       }
                   });
                });
                $('chargement').update(texte);
            }
            else
                $('chargement').update();
            
            if (texte_erreurs.length == 1)
                $('erreurs').update();
            else
                $('erreurs').update(texte_erreurs.join('<br />'));
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

        function formater_valeur(td,nom_option,valeur) {
            if (typeof (valeur) == 'undefined')
                valeur='[Non d&eacute;fini]';

            else if (nom_option.indexOf('Couleur') != -1)
                td.setStyle({'backgroundColor':(valeur.indexOf(',') == -1 ? valeur : 'rgb('+valeur+')')});
            else if (nom_option.indexOf('Dimension') != -1 || nom_option.indexOf('Pos') != -1)
                valeur+=' mm';
            else if (nom_option.indexOf('Compression') != -1)
                valeur=valeur*100+'%';
            else if (nom_option.indexOf('Rotation') != -1)
                valeur+='&deg;';
            td.update(valeur);
            return td;
        }

        new Event.observe(window, 'load',function() {
            reload_observers_cells();

            $('chargement').update('Chargement de la liste INDUCKS...');
            new Ajax.Request('<?=site_url('numerosdispos')?>/index/'+pays+'/'+magazine, {
                method: 'post',
                onSuccess:function(transport) {
                    numeros_dispos=transport.headerJSON;
                    $('chargement').update();
                }
            });
            $$('td .preview').invoke('observe','click',function(event) {
                preview_numero(Event.element(event));
            });

            $$('tr.ligne_dispo>td:not(.intitule_numero)').each(function(td) {
                td.store('valeur_reelle',td.hasClassName('num_checked') ? 'Utilis&eacute;' : 'Non utilis&eacute;');
            });
            
            $$('.lien_etape span').invoke('observe','click',function (event) {
                var element=Event.element(event);
                element=element.up('td');
                var id=element.readAttribute('id');
                var num_etape=id.substring(id.lastIndexOf('_')+1, id.length);
                if ($$('.etape_'+num_etape+'__option').length > 0) {
                    $$('.etape_'+num_etape+'__option').each(function (colonne_entete) {
                        var num_colonne=colonne_entete.previousSiblings().length;
                        $$('.ligne_dispo,#ligne_noms_options').each(function(ligne) {
                            ligne.down('td',num_colonne).remove();
                        });
                    });
                    $('entete_etape_'+num_etape).writeAttribute({'colspan':1});
                }
                else {
                    //var num_colonne=$('ligne_1').down('[name="etape_'+num_etape+'"]')+lement.previousSiblings().length;
                    var num_colonne=element.previousSiblings().length;
                    var num_colonne_interne=$('ligne_1').down('[name="etape_'+num_etape+'"]').previousSiblings().length;
                    $('chargement').update('Chargement des param&egrave;tres...');
                    new Ajax.Request('<?=site_url('parametrageg')?>/index/'+pays+'/'+magazine+'/'+num_etape, {
                        method: 'post',
                        parameters: 'etape='+num_etape,
                        onSuccess:function(transport) {
                            var nb_options=Object.values(transport.headerJSON).length;
                            $('ligne_etapes').down('td',num_colonne).writeAttribute({'colspan':parseInt($('ligne_etapes').down('td',num_colonne).readAttribute('colspan'))+nb_options});
                            var i=0;
                            var nouvelle_cellule;
                            var contenu;
                            var texte='';
                            for (var option_nom in transport.headerJSON) {
                                $$('.ligne_dispo,#ligne_noms_options').each(function(ligne) {
                                    nouvelle_cellule=new Element('td');
                                    if (ligne.id == 'ligne_noms_options') {
                                        nouvelle_cellule.addClassName('etape_'+num_etape+'__option');
                                        contenu=option_nom;
                                        nouvelle_cellule.insert(contenu)
                                                        .store('nom_option',option_nom);
                                    }
                                    else {
                                        texte=new Array();
                                        var id=ligne.readAttribute('id');
                                        var numero=id.substring(id.indexOf('_')+1,id.length);
                                        if (ligne.down('[name="etape_'+num_etape+'"]').hasClassName('num_checked')) {
                                            for (var intervalle in transport.headerJSON[option_nom]) {
                                                if (est_dans_intervalle(numero, intervalle)) {
                                                    texte.push(transport.headerJSON[option_nom][intervalle]);
                                                }
                                            }
                                            if (texte.length > 1) {
                                                nouvelle_cellule.addClassName('erreur_valeurs_multiples');
                                            }
                                            texte=texte.join('--');
                                            nouvelle_cellule=formater_valeur(nouvelle_cellule,option_nom,texte)
                                                             .store('valeur_reelle',texte);
                                        }
                                        else {
                                            nouvelle_cellule.update().addClassName('non_concerne');
                                        }
                                    }
                                    ligne.down('td',num_colonne_interne+i).insert({'before':nouvelle_cellule});

                                });
                                i++;
                            }
                            $('chargement').update();
                            reload_observers_cells();
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
                zoom=value;
                if ($('numero_preview').readAttribute('name') != null)
                    preview_numero($('ligne_'+$('numero_preview').readAttribute('name')).down('td'));
                $('zoom_value').update(zoom);
              }
            });
            $('zoom_value').update(zoom);
        });

    </script>
<title><?=$title?></title>
</head>
<body id="body" style="margin:0;padding:0">
    <div id="chargement"></div>
    <div id="erreurs"></div>
    <div id="viewer">
        <h1>Preview</h1>
        <div id="zoom_slider" class="slider">
            <div class="handle"></div>
        </div>&nbsp;Zoom : <span id="zoom_value"></span>
        <div id="numero_preview"></div>
        <div id="previews"></div>
    </div>