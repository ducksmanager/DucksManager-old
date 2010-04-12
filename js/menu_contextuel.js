/** 
 * @description		prototype.js based context menu
 * @author        Juriy Zaytsev; kangax [at] gmail [dot] com; http://thinkweb2.com/projects/prototype/
 * @version       0.6
 * @date          12/03/07
 * @requires      prototype.js 1.6
*/
var achats_affiches=false;
var l10n=new Array('date_question','date_invalide','description_question','selectionner_date_achat',
				   'description','description_invalide','acquisition_existante','mise_a_jour');

if (Object.isUndefined(Proto)) { var Proto = { }; }

Proto.Menu = Class.create({
	initialize: function() {
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
		this.container = new Element('div', {id:'menu_contextuel',className: this.options.className, style: 'display:none'});
		var span_nb_selectionnes=new Element('span',{'id':'nb_selection'}).insert(0);
		var texte_span_nb_selectionnes=new Element('span',{'id':'numero_selectionne'});
		var texte_span_nb_selectionnes_pl=new Element('span',{'id':'numeros_selectionnes'}).setStyle({'dispay':'none'});
		var entete=new Element('div',{'align':'center'})
			.insert(span_nb_selectionnes).insert(' ').insert(texte_span_nb_selectionnes).insert(texte_span_nb_selectionnes_pl);
		entete.setStyle({'font-size':'11px'});
		var list = new Element('ul');
		this.options.menuItems.each(function(item,index) {
			list.insert(
				new Element('li', {className: item.separator ? 'separator' : ''}).insert(
					item.separator 
						? '' 
						: Object.extend(new Element('a', {
							id: 'item'+index, 
							href: 'javascript:return false;',
							name: item.groupName,
							style: (item.className=="date2"||item.className=="n_date"?'display:none':''),
							className: (item.subMenu?'sub_menu ':'')
									  +(item.className || '') 
									  +(item.disabled ? ' disabled' : (item.selected?' enabled selected':' enabled')) 
						}), { _callback: item.callback })
						.observe('mouseover', this.onMouseOver.bind(this))
						.observe('click', this.onClick.bind(this))
						.observe('contextmenu', Event.stop)
						.update(item.name+(item.subMenu?'&nbsp;&gt;&gt;':''))
				)
			);
		}.bind(this));
		$(document.body).insert(this.container.insert(entete).insert(list).observe('contextmenu', Event.stop));
		if (this.ie) { $(document.body).insert(this.shim); }
		
		document.observe('click', function(e) {
			if (this.container.visible() && !e.isRightClick() && e.target.readAttribute('name')!='achat' && e.target.tagName!='INPUT') {
				this.options.beforeHide(e);
				if (this.ie) this.shim.hide();
				this.container.hide();
				$('sous_menu').hide();
			}
			else if (e.target.readAttribute('name')=='achat') {
				e.target.addClassName('selected');
				$$('.date,.pas_date,.non_date').each(function(item) {
					item.removeClassName('selected');
				});
				$$('.sub_menu.date')[0].addClassName('selected');
			}
		}.bind(this));
		
		$$(this.options.selector).invoke('observe', Prototype.Browser.Opera ? 'click' : 'contextmenu', function(e){
			if (Prototype.Browser.Opera && !e.ctrlKey) {
				return;
			}
			this.show(e);
		}.bind(this));

		/** Sous-menu **/
		var sous_menu=new Element('div',{'id':'sous_menu'})
			 .addClassName('menu desktop')
			 .setStyle({'display':'none'});

		var liste_achats=new Element('ul');
		var section_achats_existants=new Element('div',{'id':'dates_existantes','align':'center'});
		tab_achats.each(function(achat) {
			liste_achats.insert(new Element('li',{'class':'editer_date'}).update(Object.extend(
				new Element('a', {
						href: 'javascript:return false;',
						name: achat.groupName,
						'class': 'enabled supprimer_date'
					}))
				.observe('click', function (e) {
					$$('a[name="'+e.target.name+'"]').each(function(item) {
						item.removeClassName('selected');
					});
					
				})
				.update(achat.name+(achat.subMenu?'&nbsp;&gt;&gt;':''))));
		});
		var section_nouvel_achat=new Element('div',{'id':'nouvel_achat','align':'center'});
    	nouvel_achat_o=new Object();
    	nouvel_achat_o['name']=l10n_action('affiche','nouvelle_date_achat');
    	nouvel_achat_o['className']='n_date';
    	nouvel_achat_o['groupName']='achat';
    	nouvel_achat_o['selected']=false;
		var nouvel_achat=new Element('li').update(Object.extend(
		new Element('a', {
			id:'creer_date_achat',
			href: 'javascript:return false;',
			name: nouvel_achat_o.groupName,
			'class': 'enabled'
		}))
		.observe('click', function (event) {
			if ($('nouvelle_description'))
				return;
			var nouvelle_date_li=new Element('li');
    		var nouvelle_date_input1=new Element('input',{'id':'nouvelle_description','type':'text','size':30,'maxlength':30});
    		var nouvelle_date_input2=new Element('input',{'id':'nouvelle_date','type':'text','size':30,'maxlength':10}).setValue(today());
    		var nouvelle_date_ok=new Element('input',{'id':'nouvelle_date_ok','type':'submit','value':'OK'});
    		nouvelle_date_input1.writeAttribute({'value':l10n['description']}); 
    		nouvelle_date_li.update(nouvelle_date_input1).insert('<br />').insert(nouvelle_date_input2).insert(nouvelle_date_ok);
			$('dates_existantes').next().lastChild.insert({'after':nouvelle_date_li});
			nouvelle_date_ok.observe('click', function (event) {
				date_valide=true;
				var date_entree=$('nouvelle_date').getValue();
				if (!isDate(date_entree) || !date_entree)
					date_valide=false;
				if (!date_valide) {
					$('nouvelle_date').setStyle({'fontStyle':'italic','color':'red'});
					$('nouvelle_date').value=l10n['date_invalide'];
					$('nouvelle_description').focus();
					setTimeout(function() {
						$('nouvelle_date').setStyle({'fontStyle':'','color':''});
						$('nouvelle_date').value=date_entree;
					},2000);
				}
				var description_entree=$('nouvelle_description').readAttribute('value');
				
				var description_valide=true;
				if (description_entree.textLength>30 || description_entree=='')
					description_valide=false;
				if (!description_valide) {
					$('nouvelle_description').setStyle({'fontStyle':'italic','color':'red'});
					$('nouvelle_description').writeAttribute({'value':l10n['description_question']});
					setTimeout(function() {
						$('nouvelle_description').setStyle({'fontStyle':'','color':''});
						$('nouvelle_description').writeAttribute({'value':date_entree});
					},2000);
				}
				if (!description_valide || !date_valide)
					return;
				var reg_date=new RegExp("-","g");
				var date=date_entree.split(reg_date);
				var myAjax = new Ajax.Request('Database.class.php', {
				   method: 'post', 
				   parameters:'database=true&acquisition=true&afficher_non_defini=false&date_annee='+date[0]+'&date_mois='+date[1]+'&date_jour='+date[2]+'&description='+description_entree,
				   onSuccess:function(transport,json) {
						
					if (transport.responseText=='Date') {
						$('nouvelle_description').setStyle({'fontStyle':'italic','color':'red'});
						$('nouvelle_description').value=l10n['acquisition_existante'];
						setTimeout(function() {
							$('nouvelle_description').setStyle({'fontStyle':'','color':''});
							$('nouvelle_description').writeAttribute({'value':description_entree});
						},2000);
					}
			    	else {
			    		var nouvelle_date_a=new Element('a',{'class':'enabled',
							 'href':'javascript:;',
							 'name':'achat'});
						nouvelle_date_a.setStyle({'display':'block'}); 
						nouvelle_date_a.update('Achat "'+description_entree+'"<br />'+date[0]+'-'+date[1]+'-'+date[2]);
						nouvelle_date_a
							.observe('click', action_onclick(null,event));
						nouvelle_date_li.update(nouvelle_date_a);
			    		var ajoute=false;
			    		$$('.date2').each(function(element) {
			    			var date_courante=element.lastChild.textContent;
			    			if (!ajoute&&est_superieure_a(date_courante, date[2]+'-'+date[1]+'-'+date[0])) {
			    				element.parentNode.insert({'before':nouvelle_date_li});
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
		$('body').insert(sous_menu);
		l10n_action('fillArray',l10n,'l10n');
		l10n_action('remplirSpan',new Array('creer_date_achat','nouvel_achat','dates_existantes',
											'numero_selectionne','numeros_selectionnes'));
	},
	show: function(e) {
		e.stop();
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
		this.container.setStyle(elOff).setStyle({zIndex: this.options.zIndex});
		if (this.ie) { 
			this.shim.setStyle(Object.extend(Object.extend(elDim, elOff), {zIndex: this.options.zIndex - 1})).show();
		}
		this.options.fade ? Effect.Appear(this.container, {duration: 0.25}) : this.container.show();
		this.event = e;
	},
	onMouseOver: function(e) {
		action_onmouseover(this,e);
	},
	onClick: function(e) {
		action_onclick(this,e); 
		
	}
});

function action_onmouseover(proto,e) {
	e.stop();
	if (e.target.hasClassName('sub_menu') && $('sous_menu').getStyle('display')=='none') {
		
		var val_left=$('menu_contextuel').offsetLeft+e.target.offsetWidth+2;
		var extremite_droite=val_left+e.target.offsetWidth;
		if (extremite_droite>=$('body').offsetWidth) {
			val_left=$('menu_contextuel').offsetLeft-e.target.offsetWidth-2;
		}
		$('sous_menu')
		 	.setStyle({left  :val_left+'px',
					   top   :$('menu_contextuel').offsetTop +e.target.offsetTop+'px',
					   zIndex:100,
					   display:'block'});

	}
	else if (e.target.up('#sous_menu')==null) { // Cacher si on n'entre pas dans le sous-menu
		if ($('sous_menu'))
			$('sous_menu').setStyle({'display':'none'});
	}
}

function action_onclick(proto,e) {
	e.stop();
	if (e.target.hasClassName('disabled'))
		return;
	if (e.target.hasClassName('save')) {
		if ($('creer_date_achat').hasClassName('selected')) {
			alert(l10n['selectionner_date_achat']);
			return;
		}
		var etat;
		var date_achat;
		var av;
		var liste_sel_num=new Array();
		$$('.selected').each(function(item) {
			var classes=item.classNames().toArray();
			if (item.name=="etat")
				etat=classes[0];
			if (item.name=="achat") {
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
			}
			if (item.name=="vente")
				av=(classes[0]=="non_marque_a_vendre"?-1:classes[0]=="a_vendre");
		});
		$$('.num_checked').each(function(item) {
			var numero=item.title;
			liste_sel_num.push(numero);
		});
		update_numeros(liste_sel_num,etat,date_achat,av);
		proto.options.beforeSelect(e);
		if (proto.ie) proto.shim.hide();
		proto.container.hide();
		//e.target._callback(proto.event);
	}
	else if(e.target.hasClassName("date")) {
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
	else if (e.target.hasClassName('non_possede')) {
		$$('a[name="'+e.target.name+'"]').each(function(item) {
			item.removeClassName('selected');
		});
		$$('a:not([name=etat])').each(function(item) {
			if (!(item.hasClassName('save'))) {
				item.removeClassName('enabled');
				item.addClassName('disabled');
			}
		});
		e.target.addClassName('selected');
	}
	else if (e.target.name=="etat") {
		$$('a[name="'+e.target.name+'"]').each(function(item) {
			item.removeClassName('selected');
		});
		$$('a:not([name=etat])').each(function(item) {
			item.removeClassName('disabled');
			item.addClassName('enabled');
		});
		e.target.addClassName('selected');
	}
	else {
		$$('a[name="'+e.target.name+'"]').each(function(item) {
			item.removeClassName('selected');
		});
		e.target.addClassName('selected');
	}
}

function update_numeros(liste,etat,date_achat,av) { 
	liste_serialized=liste.join();
	var pays=$('pays').innerHTML;
	var magazine=$('magazine').innerHTML; 
	defiler_log(l10n['mise_a_jour']);
	var myAjax = new Ajax.Request('Database.class.php', {
		   method: 'post',
		   parameters:'database=true&update=true&pays='+pays+'&magazine='+magazine+'&list_to_update='+liste_serialized+'&etat='+etat+'&date_acquisition='+date_achat+'&av='+av,
		   onSuccess:function(transport,json) {
				//var a;
				window.location.replace("?action=gerer&onglet=ajout_suppr&onglet_magazine="+pays+"/"+magazine);
		   }
	});
}

function est_superieure_a (date1, date2) {
	var reg_date=new RegExp("-","g");
    var date1=date1.split(reg_date);
    var date2=date2.split(reg_date);
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
	var jour=la_date.getDate();if (jour<10) jour='0'+jour;
	var mois=la_date.getMonth()+1;if (mois<10) mois='0'+mois;
	var annee=la_date.getFullYear();
	return annee+'-'+mois+'-'+jour;
}