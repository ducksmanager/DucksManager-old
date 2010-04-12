
var prochain_traitement=null;var image_ouverte=false;var sous_menu_deroule=null;function sel_traitement(traitement){if(!traitement_possible(traitement))
return false;if(prochain_traitement)
document.getElementById(prochain_traitement).style.color='black';document.getElementById(traitement).style.color='red';document.getElementById(traitement).blur=true;prochain_traitement=traitement;return true;}
function traitement_possible(traitement){return true;}
function montrer_cacher(element){var id=element.id;if(document.getElementById(id+'_sous_menu').style.display=='none'){if(id.charAt(0)==id.charAt(0).toUpperCase()){if(sous_menu_deroule)
document.getElementById(sous_menu_deroule).style.display='none';sous_menu_deroule=id+'_sous_menu';}
document.getElementById(id+'_sous_menu').style.display='inline';if(document.getElementById(id+'_0'))
sel_traitement(id+'_0');return;}
else{document.getElementById(id+'_sous_menu').style.display='none';if(prochain_traitement&&prochain_traitement.indexOf(id)!=-1)
prochain_traitement=null;if(sous_menu_deroule&&id.charAt(0)==id.charAt(0).toUpperCase())
sous_menu_deroule=null;}}