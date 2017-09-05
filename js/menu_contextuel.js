/** 
 * @description		prototype.js based context menu
 * @author        Juriy Zaytsev; kangax [at] gmail [dot] com; http://thinkweb2.com/projects/prototype/
 * @version       0.6
 * @date          12/03/07
 * @requires      prototype.js 1.6
*/
var achats_affiches=false;
var l10n=['date_question','date_invalide','description_question','selectionner_date_achat',
    'description','description_invalide','acquisition_existante','mise_a_jour'];
var protos=[];
var l10n_items;

if (Object.isUndefined(Proto)) {
    var Proto = { };
}

Proto.Menu = Class.create({
    initialize: function() {
        l10n_items=[];
        var body = $('body');

        var e = Prototype.emptyFunction;
        this.ie = Prototype.Browser.IE;
        this.options = Object.extend({
            selector: '.contextmenu',
            className: 'protoMenu',
            pageOffset: 25,
            fade: false,
            zIndex: 100,
            beforeShow: e,
            beforeHide: e,
            beforeSelect: e
        }, arguments[0] || { });
		
        this.shim = new Element('iframe', {
            style: 'position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);display:none',
            src: 'javascript:false;',
            frameborder: 0
        });

                
        this.options.fade = this.options.fade && !Object.isUndefined(Effect);

        this.container = new Element('div', {
            id:this.getId('menu_contextuel'),
            className: this.options.className, 
            style: 'display:none'
        });
        var entete=new Element('div',{
            'id':this.getId('entete'),
            'align':'center'
        })
        .addClassName('entete')
        .setStyle({
            'font-size':'11px'
        });
        switch (this.options.type) {
            case 'gestion_numeros' :
                var span_nb_selectionnes=new Element('span',{
                    'id':'nb_selection'
                }).insert(0);
                var texte_span_nb_selectionnes=new Element('span',{
                    'id':'numero_selectionne'
                });
                var texte_span_nb_selectionnes_pl=new Element('span',{
                    'id':'numeros_selectionnes'
                }).setStyle({
                    'dispay':'none'
                });
                entete.insert(span_nb_selectionnes).insert(' ').insert(texte_span_nb_selectionnes).insert(texte_span_nb_selectionnes_pl);
                break;
        }
        var list = new Element('ul');
        this.options.menuItems.each(function(item,index) {
            l10n_items[index]=item.groupName;
            if (!(item.separator)) {
                var contenu_lien=item.groupName+(item.subMenu?'&nbsp;&gt;&gt;':'');
                switch (this.options.type) {
                    case 'gestion_numeros' :
                        var name= item.groupName;
                        var groupname_name=item.groupName.split(new RegExp("_", "g"));
                        contenu_lien=new Element('span',{
                            'name':item.groupName.substring(groupname_name[0].length+1,item.groupName.length)
                            });
                        item.groupName=groupname_name[0];
                        break;
                }
            }
            var lien=new Element('a', {
                id: 'item'+index,
                href: 'javascript:return false;',
                name: name,
                style: (item.className=="date2"||item.className=="n_date"?'display:none':''),
                className: (item.subMenu?'sous_menu ':'')
                +(item.className || '')
                +(item.disabled ? ' disabled' : (item.selected?' enabled selected':' enabled'))
                });
                                
            list.insert(new Element('li', {
                className: item.separator ? 'separator' : ''
                })
            .insert(
                item.separator
                ? ''
                : Object.extend(lien, {
                    _callback: item.callback
                    })
                .observe('mouseover', this.onMouseOver.bind(this))
                .observe('click', this.onClick.bind(this))
                .observe('contextmenu', Event.stop)
                .update(contenu_lien)
                .insert(typeof (item.nextSpanName) != 'undefined'
                    ? new Element('span',{
                        'name':item.nextSpanName
                        })
                    : '')));
        }.bind(this));
        l10n_action('remplirSpanName',l10n_items);
                
        $(document.body).insert(this.container.insert(entete).insert(list).observe('contextmenu', Event.stop));
        if (this.ie) {
            $(document.body).insert(this.shim);
        }
		
        document.observe('click', function(e) {
            if (Event.element(e).tagName=='BUTTON' || (typeof prevent_click != 'undefined' && prevent_click)) {
                prevent_click=false;
                return;
            }
            switch (this.options.type) {
                case 'gestion_numeros' :
                    if (this.container.visible() && !e.isRightClick() && e.target.readAttribute('name')!='achat' && e.target.tagName!='INPUT') {
                        this.cacher_tous_menus(e);

                    }
                    else if (e.target.readAttribute('name')=='achat') {
                        e.target.addClassName('selected');
                        $$('.date,.pas_date,.non_date').each(function(item) {
                            item.removeClassName('selected');
                        });
                        $$('.sous_menu.date')[0].addClassName('selected');
                    }
                break;
            }
        }.bind(this));

        switch (this.options.type) {
            case 'gestion_numeros':
                $$(this.options.selector).invoke('observe', Prototype.Browser.Opera ? 'click' : 'contextmenu', function(e){
                    if (Prototype.Browser.Opera && !e.ctrlKey) {
                        return;
                    }
                    this.show(e);
                }.bind(this));
            break;
        }

        /** Sous-menu **/
        switch (this.options.type) {
            case 'gestion_numeros' :
                var sous_menu=new Element('div',{
                    'id':'sous_menu_achat_associer_date_achat'
                })
                .addClassName('menu desktop')
                .setStyle({
                    'display':'none'
                });

                var liste_achats=new Element('ul');
                var section_achats_existants=new Element('div',{
                    'id':'dates_existantes',
                    'align':'center'
                });
                tab_achats.each(function(achat) {
                    liste_achats.insert(new Element('li',{
                    	'id':'achat_'+achat.id,
                        'class':'editer_date'
                    }).insert(Object.extend(
                    	new Element('img', {
                    		'src':'images/supprimer.png',
                    		'title':'Supprimer cette date d\'achat'})
                    		.setStyle({'position': 'absolute', 'right': 0,'width':'10px','cursor':'pointer'})
                    		.observe('click', function (e) {
		                        if (confirm('Confirmez-vous la suppression de cette date d\'achat ?')) {
		                        	var id_achat=Event.element(e).up('li').readAttribute('id').split(new RegExp(/_/g))[1]; 
		                        	new Ajax.Request('Database.class.php', {
		                                 method: 'post',
		                                 parameters:'database=true&supprimer_acquisition='+id_achat,
		                                 onSuccess:function() {
		                                	 $('achat_'+id_achat).remove();
		                                	 $$('.bloc_details .achat_'+id_achat).invoke('down','img').invoke('remove');
		                                	 alert('La date d\'achat a ete supprimee');
		                                 }
		                        	 });
		                        }
		                    })))
                      .insert(Object.extend(
                        new Element('a', {
                            href: 'javascript:return false;',
                            name: achat.groupName,
                            'class': 'item_sous_menu enabled supprimer_date'
                        }))
                    .observe('click', function (e) {
                        $$('a[name="'+e.target.name+'"]').each(function(item) {
                            item.removeClassName('selected');
                        });

                    })
                    .update(achat.name+(achat.subMenu?'&nbsp;&gt;&gt;':''))));
                });
                var section_nouvel_achat=new Element('div',{
                    'id':'nouvel_achat',
                    'align':'center'
                });
                var nouvel_achat_o = {
                    name: 'nouvelle_date_achat',
                    className: 'n_date',
                    groupName: 'achat',
                    selected: false
                };
                var nouvel_achat=new Element('li').update(Object.extend(
                    new Element('a', {
                        id:'creer_date_achat',
                        href: 'javascript:return false;',
                        name: nouvel_achat_o.groupName,
                        class: 'enabled'
                    }))
                .observe('click', function () {
                    if ($('nouvelle_description'))
                        return;
                    var nouvelle_date_li=new Element('li');
                    var nouvelle_date_input1=new Element('input',{
                        id:'nouvelle_description',
                        type:'text',
                        size:30,
                        maxlength:30
                    });
                    var nouvelle_date_input2=new Element('input',{
                        'id':'nouvelle_date',
                        'type':'text',
                        'size':30,
                        'readonly':'readonly',
                        'maxlength':10
                    }).setValue(today());
                    var nouvelle_date_ok=new Element('input',{
                        'id':'nouvelle_date_ok',
                        'type':'submit',
                        'value':'OK'
                    });
                    nouvelle_date_input1.writeAttribute({
                        'value':l10n['description']
                        });
                    nouvelle_date_li.update(nouvelle_date_input1).insert('<br />').insert(nouvelle_date_input2).insert(nouvelle_date_ok);
                    if ($($('dates_existantes').next().lastChild))
                        $('dates_existantes').next().lastChild.insert({
                            'after':nouvelle_date_li
                        });
                    else
                        $('dates_existantes').next().insert(nouvelle_date_li);

                    jQuery('#nouvelle_date').datepicker({
                        format: "yyyy-mm-dd",
                        keyboardNavigation: false,
                        maxViewMode: 2,
                        autoclose: true,
                        language: locale === 'fr' ? 'fr' : 'en-GB'
                    });

                    nouvelle_date_ok.observe('click', function () {
                      var date_valide=true;
                      var nouvelleDate = $('nouvelle_date');
                      var nouvelleDescription = $('nouvelle_description');

                      var date_entree=nouvelleDate.getValue();
                        if (!isDate(date_entree) || !date_entree)
                            date_valide=false;
                        if (!date_valide) {
                            nouvelleDate.setStyle({
                                'fontStyle':'italic',
                                'color':'red'
                            });
                            nouvelleDate.value=l10n['date_invalide'];
                            nouvelleDescription.focus();
                            setTimeout(function() {
                                $('nouvelle_date').setStyle({
                                    'fontStyle':'',
                                    'color':''
                                });
                                $('nouvelle_date').value=date_entree;
                            },2000);
                        }
                        var description_entree=$F('nouvelle_description');

                        var description_valide=true;
                        if (description_entree.textLength>30 || description_entree=='')
                            description_valide=false;
                        if (!description_valide) {
                            nouvelleDescription.setStyle({
                                'fontStyle':'italic',
                                'color':'red'
                            });
                            nouvelleDescription.writeAttribute({
                                'value':l10n['description_question']
                                });
                            setTimeout(function() {
                                $('nouvelle_description').setStyle({
                                    'fontStyle':'',
                                    'color':''
                                });
                                $('nouvelle_description').writeAttribute({
                                    'value':date_entree
                                });
                            },2000);
                        }
                        if (!description_valide || !date_valide)
                            return;
                        var reg_date=new RegExp("-","g");
                        var date=date_entree.split(reg_date);
                        new Ajax.Request('Database.class.php', {
                            method: 'post',
                            parameters:'database=true&acquisition=true&afficher_non_defini=false&date_annee='+date[0]+'&date_mois='+date[1]+'&date_jour='+date[2]+'&description='+description_entree,
                            onSuccess:function(transport,json) {

                                if (transport.responseText=='Date') {
                                  var nouvelleDescription = $('nouvelle_description');

                                  nouvelleDescription.setStyle({
                                        'fontStyle':'italic',
                                        'color':'red'
                                    });
                                    nouvelleDescription.value=l10n['acquisition_existante'];
                                    setTimeout(function() {
                                        $('nouvelle_description').setStyle({
                                            'fontStyle':'',
                                            'color':''
                                        });
                                        $('nouvelle_description').value=description_entree;
                                    },2000);
                                }
                                else {
                                    var nouvelle_date_a=new Element('a',{
                                        'class':'enabled',
                                        'href':'javascript:;',
                                        'name':'achat'
                                    });
                                    nouvelle_date_a.setStyle({
                                        'display':'block'
                                    });
                                    nouvelle_date_a.update('Achat "'+description_entree+'"<br />'+date[0]+'-'+date[1]+'-'+date[2]);
                                    nouvelle_date_li.update(nouvelle_date_a)
                                    .observe('click', function (e) {
                                        $$('a[name="'+e.target.name+'"]').each(function(item) {
                                            item.removeClassName('selected');
                                        });

                                    });
                                    var ajoute=false;
                                    $$('.date2').each(function(element) {
                                        var date_courante=element.lastChild.textContent;
                                        if (!ajoute&&est_superieure_a(date_courante, date[2]+'-'+date[1]+'-'+date[0])) {
                                            element.parentNode.insert({
                                                'before':nouvelle_date_li
                                            });
                                            ajoute=true;
                                        }
                                    });
                                }
                            }
                        });
                    });

                })
                .update(nouvel_achat_o.name));

                sous_menu.insert(section_achats_existants).insert(liste_achats).insert(section_nouvel_achat).insert(nouvel_achat);
                body.insert(sous_menu);
                l10n_action('fillArray',l10n,'l10n');
                l10n_action('remplirSpan',['creer_date_achat','nouvel_achat','dates_existantes',
                    'numero_selectionne','numeros_selectionnes']);
                break;
        }
        protos[this.options.type]=this;
    },
    show: function(e) {
        e.stop();
        switch (this.options.type) {
            case 'gestion_numeros' :
                var bcl=true;
                while (bcl) {
                    if ($('nb_selection').innerHTML=="0") {
                        $$('.enabled').each(function(item) {
                            item.removeClassName('enabled');
                            item.addClassName('disabled');
                        });
                        break;
                    }
                    else {
                        $$('.disabled').each(function(item) {
                            item.removeClassName('disabled');
                            item.addClassName('enabled');
                        });
                    }
                    bcl=false;
                }
            break;
        }
        this.options.beforeShow(e);
        var x = Event.pointer(e).x,
        y = Event.pointer(e).y,
        vpDim = document.viewport.getDimensions(),
        vpOff = document.viewport.getScrollOffsets(),
        elDim = this.container.getDimensions(),
        elOff = {
            left: ((x + elDim.width + this.options.pageOffset) > vpDim.width
                ? (vpDim.width - elDim.width - this.options.pageOffset) : x) + 'px',
            top: ((y - vpOff.top + elDim.height) > vpDim.height && (y - vpOff.top) > elDim.height
                ? (y - elDim.height) : y) + 'px'
        };
        this.container.setStyle(elOff).setStyle({
            zIndex: this.options.zIndex
        });
        if (this.ie) {
            this.shim.setStyle(Object.extend(Object.extend(elDim, elOff), {
                zIndex: this.options.zIndex - 1
            })).show();
        }
        this.options.fade ? Effect.Appear(this.container, {
            duration: 0.25
        }) : this.container.show();
        this.event = e;
    },
    onMouseOver: function(e) {
        action_onmouseover(this,e);
    },
    onClick: function(e) {
        action_onclick(this,e); 
    },
    getId: function(id){
        switch (this.options.type) {
            case 'gestion_numeros':
                return id;
                break;
            default:
                return id+'_'+this.options.type;
                break;
        }
    },
    cacher_tous_menus: function(e) {
        this.options.beforeHide(e);
        if (this.ie) this.shim.hide();
        $$('.menu').each(function(menu) {
            $(menu).hide();
        });
    }
});

function action_onmouseover(proto,e) {
    var target=e.target.tagName=='SPAN' ? $(e.target.parentNode) : $(e.target);
    e.stop();
    $$('.menu').each(function(sous_menu) {
        if (!(target.hasClassName('item_sous_menu')) && $(sous_menu).readAttribute('id').indexOf('sous_menu') != -1)
            $(sous_menu).hide();
    });
    if (target.hasClassName('sous_menu')) {
        if ($('sous_menu_'+target.name)) {
            if ($('sous_menu_'+target.name).getStyle('display')=='none') { // Afficher le sous-menu
                var val_left=$(proto.getId('menu_contextuel')).offsetLeft+target.offsetWidth+2;
                var extremite_droite=val_left+target.offsetWidth;
                if (extremite_droite>=$('body').offsetWidth) {
                    val_left=$(proto.getId('menu_contextuel')).offsetLeft-target.offsetWidth-2;
                }
                $('sous_menu_'+target.name)
                .setStyle({
                    left  :val_left+'px',
                    top   :$(proto.getId('menu_contextuel')).offsetTop +target.offsetTop+'px',
                    zIndex:100,
                    display:'block'
                });
            }
        }
    }
}

function action_onclick(proto,e) {
    var target=e.target.tagName=='SPAN' ? $(e.target.parentNode) : $(e.target);
    switch (proto.options.type) {
        case 'gestion_numeros' :
            e.stop();
            var nom_groupe=target.readAttribute('name').substring(0, target.readAttribute('name').indexOf('_'));
            if (target.hasClassName('disabled'))
                return;
            if (target.hasClassName('save')) {
                if ($('creer_date_achat').hasClassName('selected')) {
                    alert(l10n['selectionner_date_achat']);
                    return;
                }
                var etat;
                var date_achat;
                var av;
                var liste_sel_num=[];
                $$('.selected').each(function(item) {
                    var nom_groupe_selected=item.readAttribute('name').substring(0, item.readAttribute('name').indexOf('_'));
                    var classes=item.classNames().toArray();
                    switch(nom_groupe_selected) {
                        case 'etat':
                            etat=classes[0];
                            break;
                        case 'achat':
                            if (classes[0]=='non_date')
                                date_achat=-2;
                            else if (classes[0]=='pas_date')
                                date_achat=-1;
                            else {
                                $$('[name="achat"]').each (function (element) {
                                    if (element.hasClassName('selected') && element.childNodes.length>2)
                                        date_achat=element.childNodes[2].textContent;
                                });
                            }
                            break;
                        case 'vente':
                            av=(classes[0]=="non_marque_a_vendre"?-1:classes[0]=="a_vendre");
                    }
                });
                $$('.num_checked').each(function(item) {
                    var numero=item.title;
                    liste_sel_num.push(numero);
                });
                proto.options.beforeSelect(e);
                if (proto.ie) proto.shim.hide();
                proto.container.hide();
                update_numeros(liste_sel_num,etat,date_achat,av);
            }
            else if(target.hasClassName("date")) {
                if (!achats_affiches) {
                    $$('.date2','.n_date').each(function(item) {
                        item.style.display="block";
                    });
                }
                else {
                    $$('.date2','.n_date').each(function(item) {
                        item.style.display="none";
                    });
                }
                achats_affiches=!achats_affiches;
            }
            else if (target.hasClassName('non_possede')) {
                $$('a[name^="'+nom_groupe+'"]').each(function(item) {
                    item.removeClassName('selected');
                });
                $$('a:not([name^=etat])').each(function(item) {
                    if (!(item.hasClassName('save'))) {
                        item.removeClassName('enabled');
                        item.addClassName('disabled');
                    }
                });
                target.addClassName('selected');
            }
            else if (nom_groupe=="etat") {
                $$('a[name^="'+nom_groupe+'"]').each(function(item) {
                    item.removeClassName('selected');
                });
                $$('a:not([name^=etat])').each(function(item) {
                    item.removeClassName('disabled');
                    item.addClassName('enabled');
                });
                target.addClassName('selected');
            }
            else {
                $$('a[name^="'+nom_groupe+'"]').each(function(item) {
                    item.removeClassName('selected');
                });
                target.addClassName('selected');
            }

            break;
    }
}

function update_numeros(liste,etat,date_achat,av) { 
    var liste_serialized=liste.join();
    var pays=$('pays').innerHTML;
    var magazine=$('magazine').innerHTML;
    new Ajax.Request('Database.class.php', {
        method: 'post',
        parameters:'database=true&update=true&pays='+pays+'&magazine='+magazine+'&list_to_update='+liste_serialized+'&etat='+etat+'&date_acquisition='+date_achat+'&av='+av,
        onSuccess:function() {
            window.location.replace("?action=gerer&onglet=ajout_suppr&onglet_magazine="+pays+"/"+magazine);
        }
    });
}

function est_superieure_a (date1, date2) {
    var reg_date=new RegExp("-","g");
    date1=date1.split(reg_date);
    date2=date2.split(reg_date);
    if (date1[0]>date2[0])
        return true;
    if (date2[0]>date1[0])
        return false;
    
    if (date1[1]>date2[1])
        return true;
    if (date2[1]>date1[1])
        return false;
    	
    if (date1[2]>date2[2])
        return true;
    if (date2[2]>date1[2])
        return false;
    	
    return true;
}

function today() {
    var la_date=new Date();
    var jour=la_date.getDate();
    if (jour<10) jour='0'+jour;
    var mois=la_date.getMonth()+1;
    if (mois<10) mois='0'+mois;
    var annee=la_date.getFullYear();
    return annee+'-'+mois+'-'+jour;
}