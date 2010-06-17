var nom_pays_old="";
var nom_magazine_old="";
var fic_liste_tmp=null;
var user_inducks=null;
var pass_inducks=null;
var pays_sel=null;
var magazine_sel=null;
var log_is_empty=true;
var myMenuItems;
var etats_charges=false;
var tab_achats=new Array();
var nouvel_achat_o;


function init_observers_gerer_numeros() {
	l10n_action('fillArray',l10n_acquisitions,'l10n_acquisitions');
	var myAjax = new Ajax.Request('Database.class.php', {
	   method: 'post',
	   parameters:'database=true&liste_achats=true',
	   onSuccess:function(transport,json) {
	    	var reg=new RegExp("_", "g");
	    	var achats=transport.responseText.split(reg);
	    	for (var i=0;i<achats.length-1;i++) {
	    		var reg_caract_achat=new RegExp("~","g");
	    		var caract_achat=achats[i].split(reg_caract_achat);
	    		var achat=new Object();
	    		achat['name']='Achat "'+caract_achat[0]+'"<br />'+caract_achat[1];
	    		achat['className']='date2';
	    		achat['groupName']='achat';
	    		achat['selected']=false;
	    		tab_achats[i]=achat;
	    	}
	    		
	    	var arr_l10n=new Array(
	    			'','conserver_etat_actuel','marquer_non_possede','marquer_possede',
	    			'marquer_mauvais_etat','marquer_etat_moyen','marquer_bon_etat',
	    			'','conserver_date_achat','desassocier_date_achat','associer_date_achat',
	    			'','conserver_volonte_vente','marquer_a_vendre','marquer_pas_a_vendre',
	    			'','enregistrer_changements');
	    	l10n_action('remplirSpanIndex',arr_l10n);
	    	
			myMenuItems = [
			  {
			    separator: true
			  },{
			    className: 'non_marque', 
			    groupName: 'etat',
			    selected: true
			  },{
			    className: 'non_possede', 
			    groupName: 'etat'
			  },{
			    className: 'possede',  
			    groupName: 'etat'
			  },{
			    className: 'mauvais',  
			    groupName: 'etat'
			  },{
			    className: 'moyen',  
			    groupName: 'etat'
			  },{
			    className: 'bon',  
			    groupName: 'etat'
			  },{
			    separator: true
			  },{
			    className: 'non_date',   
			    groupName: 'achat',
			    selected: true
			  },{
			    className: 'pas_date',   
			    groupName: 'achat'
			  },{
			    className: 'date',
			    groupName: 'achat',
			    subMenu : true
			  }
			];
			var myMenuItems2=[
			    {
			    separator: true
			  },{
			    className: 'non_marque_a_vendre', 
			    groupName: 'vente',
			    selected: true
			  },{
			    className: 'a_vendre', 
			    groupName: 'vente'
			  },{
			    className: 'pas_a_vendre', 
			    groupName: 'vente'
			  },{
			    separator: true
			  },{
			    className: 'save'
			  }];
			myMenuItems=myMenuItems.concat(myMenuItems2);
			
			new Proto.Menu({
			  selector: '#liste_numeros',
			  className: 'menu desktop',
			  menuItems: myMenuItems
			});
			$$('.num_manque','.num_possede').invoke(
		        'observe',
		        'mouseover',
		        function(event) {
		        	lighten(Event.element(event));
		          }
		    ); 
		    $$('.num_manque','.num_possede').invoke(
		        'observe',
		        'mouseout',
		        function(event) {
		        	unlighten(Event.element(event));
		          }
		    ); 
		    $$('.num_manque','.num_possede').invoke(
		        'observe',
		        'mouseup',
		        function(event) {
		        	if (event.isLeftClick())
		        		stop_selection(Event.element(event));
		        	
		          }
		    ); 
		    $$('.num_manque','.num_possede').invoke(
		        'observe',
		        'mousedown',
		        function(event) {
		        	if (event.isLeftClick())
		        		start_selection(Event.element(event));
		          }
		    );
		    $$('.num_manque','.num_possede').invoke(
		        'observe',
		        'mousemove',
		        function(event) {
		        	pre_select(Event.element(event));
		          }
		    );  
		    var image_checked= new Image;
		
			image_checked.src = "checkedbox.png";
	   }
	});
    
}


function appel(user,pass,user_pcent,user_urled,pass_pcent,pass_urled,data,type,url,cookie,host) {
	user_inducks=user;
	pass_inducks=pass;
	l10n_action('defiler_log','connexion_inducks');
	var myAjax = new Ajax.Request('appel.php', {
		   method: 'post',
		   parameters:'data='+data+'&type='+type+'&url='+url+'&cookie='+cookie+'&host='+host,
		   onSuccess:function(transport,json) {
		    	var texte_reponse=transport.responseText;
	         	if (texte_reponse.indexOf("<td><input type='password'")!=-1) { // Problème d'identification
		        	l10n_action('defiler_log','identifiants_incorrects');
		         	var formulaire='<form method="post" action="index.php?action=import">';
		         	formulaire+='<table border="0"><tr><td>Nom d\'utilisateur Inducks:</td><td><input type="text" name="user" /></td></tr>';
	 				formulaire+='<tr><td>Mot de passe Inducks:</td><td><input type="password" name="pass" /></td></tr>';
	 				formulaire+='<tr><td align="center" colspan="2"><input type="submit" value="Connexion"/></td></tr></table></form>';
		         	$("contenu").update(formulaire);
		         }
		         else  {
		        	l10n_action('defiler_log','connexion_reussie');
		        	 var myAjax2 = new Ajax.Request('appel.php', {
		      		   method: 'post',
		      		   parameters:'data=&type=GET&url='+url+'&cookie='+cookie+'&cookie2=coa-login='+user_urled+'%3A'+pass_urled+'&host='+host,
		      		   onSuccess:function(transport,json) {
			        	 l10n_action('defiler_log','recuperation_liste');
			        	 url='rawOutput';
			        	 var myAjax3 = new Ajax.Request('appel.php', {
				      		   method: 'post',
				      		   parameters:'ecrire=true&data=rawOutput&type=GET&url='+url+'&cookie='+cookie+'&cookie2=coa-login='+user_urled+'%3A'+pass_urled+'&host='+host,
				      		   onSuccess:function(transport,json) {	
			        		 		if (transport.responseText.indexOf(".txt")!=-1) {
			        		 			fic_liste_tmp=transport.responseText;
			        		 			l10n_action('defiler_log','succes');
			        		 			l10n_action('defiler_log','analyse_liste');
			        		 			var myAjax4 = new Ajax.Request('Liste.class.php', {
			        		 				method: 'post',
			        		 				parameters:'liste='+transport.responseText+'&import=true',
			        		 				onSuccess:function(transport,json) {
			        		 					$('contenu').update(transport.responseText);
			        		 					l10n_action('defiler_log','ok');
			        		 				}
			        		 			});
			        		 		}
			        			 }
			        		 });
			        	 }
			         });
		         }
		   }
	});
}

function defiler_log (texte) {
	if (log_is_empty) {
		var span1=new Element('span',{'id':'log1'});
		var span2=new Element('span',{'id':'log2'});
		$('log').update(span1);
		$('log').insert('<br />');
		$('log').insert(span2);
		$('log1').update('DucksManager 3');
	}
	else
		$('log1').update($('log2').innerHTML);
	$('log2').update(texte);
	log_is_empty=false;
}

function importer(accept,utilisateur_existant) {
	if (utilisateur_existant) {
		if (accept)
			importer_numeros(true);
		else
			window.location.replace("index.php?action=gerer");
	}
	else {
		if (accept) {
			var texte=new Element('span').update('DucksManager va maintenant procéder à l\'importation de vos numéros.<br />' +
					  'Pour cela, renseignez les champs ci-dessous.');
			//var table='<table><tr><td colspan="2"><span id="use_same_text" onclick="griser(this)"><input id"use_same" type="checkbox" /></span></td></tr></table>';
			var table=new Element('table');
			var tr1=new Element('tr');
			var td1=new Element('td',{'colspan':'2'});
			var checkbox_use_same=new Element('input',{'id':'use_same','type':'checkbox','onclick':'griser(this)'}); 
			var use_same_span=new Element('span',{'id':'use_same_text','onclick':'griser(this)'}).insert(' Utiliser les mêmes identifiants que pour mon compte Inducks');
			use_same_span.setStyle({'cursor':'default'});
			td1.insert(checkbox_use_same).insert(use_same_span);
			tr1.insert(td1);
			var tr2=new Element('tr');
			var td2_1=new Element('td');
			var user_text=new Element('span',{'id':'user_text'}).update('Nom d\'utilisateur : ');
			td2_1.insert(user_text);
			var td2_2=new Element('td');
			var user=new Element('input',{'id':'user','type':'text'}); 
			td2_2.insert(user);
			tr2.insert(td2_1).insert(td2_2);
			var tr3=new Element('tr');
			var td3_1=new Element('td');
			var pass_text=new Element('span',{'id':'pass_text'}).update('Mot de passe (au moins 6 caractères): ');
			td3_1.insert(pass_text);
			var td3_2=new Element('td');
			var pass=new Element('input',{'id':'pass','type':'password'});
			td3_2.insert(pass);
			tr3.insert(td3_1).insert(td3_2);
			var tr4=new Element('tr');
			var td4_1=new Element('td');
			var pass_text2=new Element('span',{'id':'pass_text2'}).update('Mot de passe (confirmation) : ');
			td4_1.insert(pass_text2);
			var td4_2=new Element('td');
			var pass2=new Element('input',{'id':'pass2','type':'password'});
			td4_2.insert(pass2);
			tr4.insert(td4_1).insert(td4_2);
			var tr5=new Element('tr');
			var td5=new Element('td',{'colspan':'2'});
			var valider=new Element('input',{'type':'submit','value':'Inscription'}).addClassName('valider');
			valider.observe('click',function() {
				verif_valider_inscription(user,pass,pass2,true);
			});
			
			td5.insert(valider);
			tr5.insert(td5);
			table.insert(tr1).insert(tr2).insert(tr3).insert(tr4).insert(tr5);
			//$('contenu').update(texte);
			$('contenu').update();
			$('contenu').appendChild(table);
			$('contenu').show();
			// update(table);
			
		}
		else window.location.replace("index.php?action=gerer");
	}
}

function griser(caller) {
	var griser;
	var checkbox_use_same=$('use_same');
	var id_caller=(caller.originalTarget?caller.originalTarget.id:caller.id);
	if ((id_caller=='use_same_text' && !checkbox_use_same.checked)
	  ||(id_caller=='use_same' && checkbox_use_same.checked)) {
		griser=true;
	}
	else {
		if ((id_caller=='use_same_text' && checkbox_use_same.checked)
	  	  ||(id_caller=='use_same' && !checkbox_use_same.checked)) {
			griser=false;
		}
	}
	var textes=new Array('user_text','pass_text','pass_text2');
	var inputs=new Array('user','pass','pass2');
	
	if (griser) {
		$('use_same').checked=true;
		textes.each(function(texte) {
			$(texte).setStyle({'color':'gray'});
		});
		inputs.each(function(input) {
			$(input).setStyle({'backgroundColor':'gray','borderColor':'gray'});
			//$(input).setAttribute('disabled', 'disabled');
		});
		$('user').value=user_inducks;
		$('pass').value=pass_inducks;
		$('pass2').value=pass_inducks;
	}
	else {
		$('use_same').checked=false;
		textes.each(function(texte) {
			$(texte).setStyle({'color':'white'});
		});
		inputs.each(function(input) {
			$(input).setStyle({'backgroundColor':'white','borderColor':'white'});
			$(input).removeAttribute('disabled');
		});
		$('user').value='';
		$('pass').value='';
		$('pass2').value='';
	}
}
function verif_valider_inscription(user,pass,pass2,importation) {
	var valeur_user=user.value;
	if (pass.value.length<6) {
		l10n_action('alert','mot_de_passe_6_char_erreur');
		pass.value='';
		pass2.value='';
		return;
	}
	if (pass.value!=pass2.value) {
		l10n_action('alert','mots_de_passe_differents');
		pass.value='';
		pass2.value='';
		return;
	}
	else {
		var myAjax = new Ajax.Request('Database.class.php', {
		   method: 'post',
		   parameters:'database=true&user='+user.value,
		   onSuccess:function(transport,json) {
				defiler_log(transport.responseText);
				if (transport.responseText.indexOf('valide')!=-1) {
					l10n_action('defiler_log','inscription_en_cours');
					var myAjax2 = new Ajax.Request('Database.class.php', {
					   	method: 'post',
					   	parameters:'database=true&inscription=true&user='+user.value+'&pass='+pass.value,
					   	onSuccess:function(transport,json) {
					   		if (transport.responseText.indexOf("Erreur")==-1) {
					   			l10n_action('defiler_log','inscription_reussie');
					   			$('light').src="vert.png";
					   			$('texte_connecte').update('Connecté(e) en tant que<br />'+valeur_user);
					   			if (importation) {
					   				importer_numeros(false);
					   			}
					   			else
					   				window.location.replace("index.php?action=gerer");
					   		}
					   		else
					   			l10n_action('defiler_log',transport.responseText);
					   	}
					});
				}
		   }
		});
	}
}

function importer_numeros(utilisateur_existant) {
	l10n_action('defiler_log','importation_numeros');
        var myAjax3 = new Ajax.Request('Database.class.php', {
        method: 'post',
        parameters:'database=true&from_file='+fic_liste_tmp
                          +(utilisateur_existant?'':('&user='+user.value+'&pass='+pass.value)),
        onSuccess:function(transport,json) {
            if (transport.responseText.indexOf('OK.')!=-1)
                    setTimeout(function() {window.location.replace("index.php?action=gerer");},2000);
            else {
                if (utilisateur_existant)
                        $('contenu').update(transport.responseText);
                else
                        l10n_action('defiler_log',transport.responseText);
            }
        }
	});
}

function connexion(user,pass) {
	var myAjax = new Ajax.Request('Database.class.php', {
		   method: 'post',
		   parameters:'database=true&user='+user+'&pass='+pass+'&connexion=true',
		   onSuccess:function(transport,json) {
		    	if (transport.responseText.indexOf('invalides')!=-1) {
		    		l10n_action('defiler_log','identifiants_incorrects');
		    		afficher_form_open();
		    	}
		   }
	});
}

function initPays() {
        if (!$('liste_pays')) return;
	var myAjax = new Ajax.Request('Inducks.class.php', {
		   method: 'post',
		   parameters:'get_pays=true',
		   onSuccess:function(transport,json) {
		   		$('liste_pays').update(transport.responseText);
		   		if ($('liste_magazines'))
		   			select_magazine();
		   }
	});
}

function initTextures() {
    if (!$('texture1')) return;
    [1,2].each (function (n) {
        new Ajax.Request('Edge.class.php', {
               method: 'post',
               parameters:'get_texture=true&n='+n,
               onSuccess:function(transport) {
                    $('texture'+n).update(transport.responseText);
                    setTimeout(function() {
                        select_sous_texture(n);
                    },1000);
               }
        });
    });
}

function select_sous_texture (n) {
    if (!$('sous_texture'+n)) return;
    var el_select=$('texture'+n);
    var myAjax = new Ajax.Request('Edge.class.php', {
           method: 'post',
           parameters:'get_sous_texture=true&texture='+$('texture'+n).options[$('texture'+n).options.selectedIndex].text+'&n='+n,
           onSuccess:function(transport) {
                $('sous_texture'+n).update(transport.responseText);
           }
    });
}
function select_magazine() {
	var el_select=$('liste_pays');
	$('form_pays').value=el_select.options[el_select.options.selectedIndex].id;
	if (el_select.options[0].id!='chargement_pays') {
		var nom_pays=el_select.options[el_select.options.selectedIndex].text;
		if (nom_pays==nom_pays_old)
			return;
		nom_pays_old=nom_pays;
  		var id_pays=el_select.options[el_select.options.selectedIndex].id;
  		pays_sel=id_pays;
		var option_chargement=new Element('option',{'id':'chargement_magazines'})
								.update("Chargement des magazines");
		$('liste_magazines').update(option_chargement);
		var myAjax = new Ajax.Request('Inducks.class.php', {
		   method: 'post',
		   parameters:'get_magazines=true&pays='+id_pays,
		   onSuccess:function(transport,json) {
		   		$('liste_magazines').update(transport.responseText);
		   		if ($('liste_numeros'))
		   			select_numero();
		   		magazine_selected();
		   	
		   }
		});
	}
}

function magazine_selected() {
	var el_select=$('liste_magazines');
	$('form_magazine').value=el_select.options[el_select.options.selectedIndex].id;
}

function select_numero() {
	var el_select=$('liste_magazines');
	var el_select_pays=$('liste_pays');
	if (el_select.options[0].id!='chargement_magazines') {
		var nom_magazine=el_select.options[el_select.options.selectedIndex].text;
		if (nom_magazine==nom_magazine_old)
			return;
		nom_magazine_old=nom_magazine;
  		var id_magazine=el_select.options[el_select.options.selectedIndex].id;
  		var id_pays=el_select_pays.options[el_select_pays.options.selectedIndex].id;
  		magazine_sel=id_magazine;
		var option_chargement=new Element('option',{'id':'chargement_numeros'})
								.update("Chargement des num&eacute;ros");
		$('liste_numeros').update(option_chargement);
		var myAjax = new Ajax.Request('Inducks.class.php', {
		   method: 'post',
		   parameters:'get_numeros=true&pays='+id_pays+'&magazine='+id_magazine,
		   onSuccess:function(transport,json) {
		   		$('liste_numeros').update(transport.responseText);
		   		if ($('liste_etats'))
		   			select_etats(); 
		   }
		});
	}
}

function select_etats() {
	var myAjax = new Ajax.Request('Database.class.php', {
	   method: 'post',
	   parameters:'database=true&liste_etats=true',
	   onSuccess:function(transport,json) {
			$('liste_etats').update();
			var reg=new RegExp("~", "g");
	    	var etats=transport.responseText.split(reg);
			for (var i=0;i<etats.length;i++) {
				var option=new Element('option').insert(etats[i]);
				$('liste_etats').insert(option);
				etats_charges=true;
				nom_pays_old="";
				nom_magazine_old="";
			}
	   	
	   }
	});
}

function afficher_numeros(pays,magazine) {
        if (pays == null || magazine == null) {
            var el_select=$('liste_magazines');
            if (el_select.options[0].id=='vide') {
                    l10n_action('alert','selectionner_magazine');
                    return;
            }
            var id_magazine=el_select.options[el_select.options.selectedIndex].id;
            magazine_sel=id_magazine;
            pays=pays_sel;
            magazine=magazine_sel;
            if (!pays || !magazine) {
                    l10n_action('alert','remplir_pays_et_magazine');
                    return;
            }
        }
	l10n_action('defiler_log','recuperation_numeros');
	var myAjax = new Ajax.Request('Database.class.php', {
		   method: 'post',
		   parameters:'database=true&affichage=true&pays='+pays+'&magazine='+magazine,
		   onSuccess:function(transport,json) {
		    	$('liste_numeros').update(transport.responseText);
		    	l10n_action('defiler_log','termine');
                init_observers_gerer_numeros();
		   }
	});
}

function observe_options_clicks() {
	$$('.options_text').each(function(element) {
		element.observe('click',function() {
			element.toggleClassName('options_text_selected');
			var pays_et_magazine=element.id.substring(new String('options').length,element.id.length);
			if (element.hasClassName('options_text_selected')) {
				$('box_'+element.id).update('Type de la liste :<br />');
				$('box_'+element.id).setStyle({'display':'block'});
				var select=new Element('select',{'id':'select_type'+pays_et_magazine});
				select.observe('change',function(element) {
					var type_liste=null;
					var id_select=null;
					if (element.srcElement) {
						type_liste=element.srcElement.options[element.srcElement.options.selectedIndex].innerHTML;
						id_select=element.srcElement.id;
					}
					else {
						if (element.originalTarget) {
							type_liste=element.originalTarget.value;
							id_select=element.originalTarget.id;
						}
					}
					var reg=new RegExp("_", "g");
					var pays_et_magazine=id_select.substring((new String('select_type')).length,id_select.length).split(reg);
					var pays=pays_et_magazine[0];
					var magazine=pays_et_magazine[1];
					var myAjax = new Ajax.Request('Liste.class.php', {
						   method: 'post',
						   parameters:'sous_liste=true&type_liste='+type_liste+'&pays='+pays+'&magazine='+magazine,
						   onSuccess:function(transport,json) {
						    	$(pays+'_'+magazine).firstChild.update(transport.responseText);
								var id_type_liste='type_liste'+pays+'_'+magazine;
								$(id_type_liste).update(type_liste);
						   }
					});
				});
				for (var i=0;i<types_listes.length;i++) {
					var item=new Element('option');
					var id_type_liste='type_liste'+pays_et_magazine;
					if (types_listes[i]==$(id_type_liste).innerHTML)
						item.writeAttribute({'selected':'true'});
					item.insert(types_listes[i]);
					select.insert(item);
				}
				$('box_'+element.id).insert(select);
			}
			else {
				//$('box_'+element.id).update('');
				$('box_'+element.id).setStyle({'display':'none'});
			}
		});
	});
	
}



function afficher_form_open() {
	var contenu='<form method="post" action="index.php?action=open">'
			   +'<table border="0"><tr><td>Nom d\'utilisateur :</td><td><input type="text" name="user" /></td></tr>'
			   +'<tr><td>Mot de passe :</td><td><input type="password" name="pass" /></td></tr>'
			   +'<tr><td align="center" colspan="2"><input type="submit" value="Connexion"/></td></tr></table></form>';
	$('contenu').insert(contenu);
}
	
function l10n_action(fonction,index,param) {
	if (typeof index!='string')
		index_param=index.join('~');
	else
		index_param=index;
	var myAjax = new Ajax.Request('locales/lang.php', {
		   method: 'post',
		   parameters:'index='+index_param,
		   onSuccess:function(transport,json) {
				if (transport.responseText.indexOf('~')!=-1) {
					transport.responseText=transport.responseText.split('~');
				}
				if (fonction=='affiche')
					return transport.responseText;

				if (typeof transport.responseText=='string') {
					if (fonction=='remplirSpan')
						window[fonction](index,transport.responseText);
					else
						window[fonction](transport.responseText);
				}
				else {
					for (var i=0;i<transport.responseText.length;i++) {
						switch (fonction) {
							case 'remplirSpanIndex':
								window[fonction](i,transport.responseText[i]);
							break;
							case 'remplirSpan':
								window[fonction](index[i],transport.responseText[i]);
							break;
							case 'fillArray':
								window[param][index[i]]=transport.responseText[i];
							break;
							case 'remplirSpan':
								window[fonction](index[i],transport.responseText[i]);
							break;
							default:
								window[fonction](transport.responseText[i]);
						}
					}
				}
		    }
	});
}

function remplirSpanIndex (index,trad) {
	if ($('item'+index)) {
		$('item'+index).update(trad);
		if ($('item'+index).hasClassName('sub_menu'))
			$('item'+index).insert('&nbsp;&gt;&gt;');
	}
}

function remplirSpan (idSpan, trad) {
	if ($(idSpan))
		$(idSpan).update(trad);
}
