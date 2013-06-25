<div id="wizard-accueil" class="first wizard" title="Bienvenue sur EdgeCreator !">
	<p>
		<img src="../images/logo_petit.png" />
	</p>
</div>
<div id="wizard-accueil2" class="wizard" title="Bienvenue sur EdgeCreator !">
	<p>
		L'objectif d'EdgeCreator est de cr&eacute;er des images de tranches.
		<br />
		L'image d'une tranche que vous cr&eacute;erez appara&icirc;tra ensuite 
		dans la section "Ma biblioth&egrave;que" de tous les utilisateurs de DucksManager 
		poss&eacute;dant le num&eacute;ro correspondant.
		<br />
		<img style="height:300px" src="../images/construction_tranche.png" />
	</p>
</div>

<div id="wizard-accueil3" class="wizard" title="Bienvenue sur EdgeCreator !">
	<p>
		Pour cr&eacute;er une tranche, vous aurez besoin :<br />
		<div style="float: left;width:50%">
			<img style="width: 100%" src="../images/regle.png" />
			D'une r&egrave;gle
		</div>
		<div style="float: left;width:50%">
			<img style="width: 100%" src="../images/appareil_photo.png" />
			D'un scanner ou un appareil photo (les capteurs photo des t&eacute;l&eacute;phones donnent parfois des photos floues)
		</div>
		
	</p>
</div>

<div id="login-form" class="wizard" title="Connexion &agrave; EdgeCreator">
	<p>
		Entrez vos identifiants DucksManager habituels ci-dessous et cliquez sur "Connexion".
	</p>
	<p class="erreurs"></p>
	<form>
		<fieldset>
			<label for="username">Pseudo: </label>
			<input type="text" name="username" id="username" class="text ui-widget-content ui-corner-all" />
			<label for="password">Mot de passe: </label>
			<input type="password" name="password" id="password" value="" class="text ui-widget-content ui-corner-all" />
			<br />
		</fieldset>
	</form>
</div>

<div id="wizard-1" class="first wizard" title="Accueil EdgeCreator">
	<p>
		Vous &ecirc;tes &agrave; pr&eacute;sent connect&eacute;(e) sur EdgeCreator.
	</p>
	<p>
		Commen&ccedil;ons par le d&eacute;but... Que voulez-vous faire ?<br />
		<form>
			<div class="buttonset">
				<input type="radio" name="choix" value="to-wizard-creer" id="to-wizard-creer" />
				<label for="to-wizard-creer">Cr&eacute;er une tranche de magazine</label><br />
				<input type="radio" name="choix" value="to-wizard-modifier" id="to-wizard-modifier"/>
				<label for="to-wizard-modifier">Modifier une tranche de magazine</label><br />
				<input type="radio" name="choix" value="to-wizard-conception" id="to-wizard-conception"/>
				<label for="to-wizard-conception">Poursuivre une conception de tranche</label>
    			<button id="selectionner_tranche_en_cours">S&eacute;lectionnez une tranche</button>
			</div>
			<ul id="tranches_en_cours" class="liste_numeros cache">
				<li class="init">
					<input type="radio" id="numero_tranche_en_cours" name="choix_tranche_en_cours">
					<label for="numero_tranche_en_cours">Label</label>
				</li>
			</ul>
		</form>
	</p>
</div>


	<div id="wizard-creer" class="wizard" title="Assistant DucksManager - Cr&eacute;ation de tranche">
		<p>
			Poss&eacute;dez-vous d&eacute;j&agrave; le num&eacute;ro dont vous souhaitez cr&eacute;er la tranche 
			dans votre collection DucksManager ?
			<form>
				<div class="buttonset">
					<input type="radio" name="choix" value="to-wizard-creer-collection" id="to-wizard-creer-collection" /><label for="to-wizard-creer-collection">Oui</label>
					<input type="radio" name="choix" checked="checked" value="to-wizard-creer-hors-collection" id="to-wizard-creer-hors-collection" /><label for="to-wizard-creer-hors-collection">Non</label>
				</div>
			</form>
		</p>
	</div>
	


		<div id="wizard-creer-collection" class="wizard" title="Assistant DucksManager - Choix de num&eacute;ro">
			<p>
				<span class="explication cache">S&eacute;lectionnez le num&eacute;ro dont vous souhaitez cr&eacute;er la tranche.</span>
				<span class="chargement">Veuillez patienter...</span>
				<form>
					<ul id="tranches_non_pretes" class="liste_numeros cache">
						<li class="init">
							<input type="radio" id="numero_tranche_non_prete" name="choix_tranche">
							<label for="numero_tranche_non_prete">Label</label>
						</li>
					</ul>
					<div class="buttonset cache">
						<input type="radio" checked="checked" name="choix" value="to-wizard-proposition-clonage" id="to-wizard-proposition-clonage" /><label for="to-wizard-proposition-clonage">J'ai trouv&eacute; mon num&eacute;ro</label>
					</div>
				</form>
				<p class="pas_de_numero cache">Pas de num&eacute;ro.</p>
			</p>
		</div>
			
		<div id="wizard-creer-hors-collection" class="wizard" title="Assistant DucksManager - Choix de num&eacute;ro">
			<p>
				Choisissez le num&eacute;ro que vous souhaitez mod&eacute;liser.<br />
				<form>
					<fieldset>
						<label for="wizard_pays">Pays: </label>
						<select name="wizard_pays" id="wizard_pays">
							<option>Chargement...</option>
						</select><br />
						<label for="wizard_magazine">Magazine: </label>
						<select name="wizard_magazine" id="wizard_magazine">
							<option>Chargement...</option>
						</select><br />
						<label for="wizard_numero">Num&eacute;ro: </label>
						<select name="wizard_numero" id="wizard_numero">
							<option>Chargement...</option>
						</select><br />
						Les tranches sous fond vert sont d&eacute;j&agrave; disponibles. 
						Si vous souhaitez les modifier, repassez &agrave; l'&eacute;cran pr&eacute;c&eacute;dent
						et choisissez "Modifier une tranche de magazine".
					</fieldset>
					<div class="buttonset">
						<input type="radio" checked="checked" name="choix" value="to-wizard-proposition-clonage" id="to-wizard-proposition-clonage" /><label for="to-wizard-proposition-clonage">J'ai trouv&eacute; mon num&eacute;ro</label>
						<input type="radio" name="choix" value="to-wizard-numero-inconnu" id="to-wizard-numero-inconnu" /><label for="to-wizard-numero-inconnu">Mon num&eacute;ro n'est pas dans la liste</label>
					</div>
				</form>
			</p>
		</div>
	
			<div id="wizard-dimensions" class="wizard first" title="Assistant DucksManager - Conception de la tranche">
				<p>
					<form name="form_options">
						<span id="nom_complet_numero"></span>
						Pour concevoir la tranche du magazine, nous devons connaitre ses dimensions.<br />
						Indiquez ci-dessous l'<b>&eacute;paisseur</b> et la <b>hauteur</b> de la tranche, en millim&egrave;tres.
						
						Dimensions de la tranche : 
						<input type="text" id="Nouvelle_dimension_x" name="Dimension_x" maxlength="3" size="2"> mm 
						x 
						<input type="text" id="Nouvelle_dimension_y" name="Dimension_y" maxlength="3" size="2"> mm
						<div class="buttonset cache">
							<input type="radio" checked="checked" name="choix" value="to-wizard-photos" id="to-wizard-photos" />
						</div>
					</form>
				</p>
			</div>
			
			<div id="wizard-photos" class="wizard dead-end" title="Assistant DucksManager - Photos de la tranche">
				<p>
					<form name="form_options">
						Afin d'assurer la meilleure conception de tranche possible, au moins une photo de la tranche est requise.<br />
						Si certaines parties de la tranche (des logos par exemple) ne sont pas assez visibles depuis cette photo, 
						cela peut &ecirc;tre une bonne id&eacute;e de les photographier &agrave; part.<br />
						Les photos doivent &ecirc;tre nettes, bien &eacute;clair&eacute;es, et les couleurs fid&egrave;les &agrave; la tranche originale.
						<br />
						<div class="accordion">
							<h3><a href="#">Envoyer une photo</a></h3>
							<div class="envoyer_photo">
								<iframe src="<?=base_url().'index.php/helper/index/image_upload.php?photo_tranche'?>"></iframe>
							</div>
							<h3><a href="#">S&eacute;lectionner une photo existante</a></h3>
							<div class="selectionner_photo">
								<p class="chargement_images" >Chargement des images</p>
								<p class="pas_d_image cache" >Aucune image r&eacute;pertori&eacute;e pour ce magazine</p>
								<ul class="gallery cache">
									<li class="template">
										<img />
										<input type="radio" name="numeroPhotoPrincipale" class="cache" />
									</li>
								</ul>
							</div>
						</div>
						<button class="cache" value="to-wizard-resize">
							Rogner la photo s&eacute;lectionn&eacute;e
						</button>
						<br />
						S&eacute;lectionnez une photo de tranche pour poursuivre.						
						<input type="hidden" id="numeroPhotoPrincipale" name="numeroPhotoPrincipale" value=""/>
						<div class="buttonset cache">
							<input type="radio" checked="checked" name="choix" value="to-wizard-conception" id="to-wizard-conception" />
						</div>
					</form>
				</p>
			</div>
	
	<div id="wizard-modifier" class="wizard" title="Assistant DucksManager - Choix de num&eacute;ro">
		<p>
			Choisissez le num&eacute;ro dont vous souhaitez modifier la mod&eacute;lisation.<br />
			<form>
				<fieldset>
					<label for="wizard_pays_modifier">Pays: </label>
					<select name="wizard_pays" id="wizard_pays_modifier">
						<option>Chargement...</option>
					</select><br />
					<label for="wizard_magazine_modifier">Magazine: </label>
					<select name="wizard_magazine" id="wizard_magazine_modifier">
						<option>Chargement...</option>
					</select><br />
					<label for="wizard_numero_modifier">Num&eacute;ro: </label>
					<select name="wizard_numero" id="wizard_numero_modifier">
						<option>Chargement...</option>
					</select><br />
					Les tranches sous fond vert sont modifiables. 
					Si vous souhaitez en cr&eacute;er une nouvelle, repassez &agrave; l'&eacute;cran pr&eacute;c&eacute;dent
					et choisissez "Cr&eacute;er une tranche de magazine".
				</fieldset>
				<div class="buttonset cache">
					<input type="radio" checked="checked" name="choix" value="to-wizard-clonage-silencieux" id="to-wizard-clonage-silencieux" /><label for="to-wizard-clonage-silencieux">J'ai trouv&eacute; mon num&eacute;ro</label>
				</div>
			</form>
		</p>
	</div>
	
		<div id="wizard-proposition-clonage" class="wizard" title="Assistant DucksManager - Cr&eacute;ation">
			<p>
				Certaines tranches ont d&eacute;j&agrave; &eacute;t&eacute; con&ccedil;ues pour le magazine s&eacute;lectionn&eacute;. 
				Si la v&ocirc;tre ressemble &agrave; l'une d'elles, sa cr&eacute;ation sera facilit&eacute;e.<br />
				L'une des tranches si-dessous est identique &agrave; la v&ocirc;tre, ou bien seules quelques couleurs ou quelques textes sont diff&eacute;rents ? 
				Si oui, s&eacute;lectionnez cette tranche. Sinon, cliquez sur "Cr&eacute;er une tranche originale".
				<form>
					<div class="chargement">
						Veuillez patienter...
					</div>
					<div id="tranches_pretes_magazine">
					
					</div>
					<br />
					<div class="buttonset">
						<input type="radio" checked="checked" name="choix" value="to-wizard-clonage" id="to-wizard-clonage" /><label for="to-wizard-clonage">J'ai trouv&eacute; une tranche similaire</label>
						<input type="radio" name="choix" value="to-wizard-dimensions" id="to-wizard-dimensions1" /><label for="to-wizard-dimensions1">Cr&eacute;er une tranche originale</label>
					</div>
				</form>
			</p>
		</div>
		
			<div id="wizard-clonage" class="wizard" title="Assistant DucksManager - Clonage">
				<p>
					Le num&eacute;ro <span class="nouveau_numero"></span> va &eacute;tre cr&eacute;&eacute; &agrave; partir du num&eacute;ro <span class="numero_similaire"></span>.<br />
					Ce processus peut durer plus d'une minute dans certains cas. Veuillez patienter tant que le clonage est en cours, ne fermez pas cette fen&ecirc;tre.
					<div class="loading">Clonage en cours...</div>
					<div class="done cache">Clonage termin&eacute;. Vous pouvez passer &agrave; l'&eacute;tape suivante.</div>
					<form>
						<input type="hidden" checked="checked" name="choix" value="to-wizard-conception" id="to-wizard-conception2" />
					</form>
				</p>
			</div>
		
			<div id="wizard-clonage-silencieux" class="wizard" title="Assistant DucksManager - Pr&eacute;paration de la tranche">
				<p>
					<div class="loading">Veuillez patienter...</div>
					<div class="done cache">La tranche est pr&ecirc;te &agrave; &ecirc;tre modifi&eacute;e. Vous pouvez passer &agrave; l'&eacute;tape suivante.</div>
					<form>
						<input type="hidden" checked="checked" name="choix" value="to-wizard-conception" id="to-wizard-conception3" />
					</form>
				</p>
			</div>
			
			
		<div id="wizard-conception" class="main first wizard dead-end" title="Assistant DucksManager - Conception de la tranche">
			<p>
				<div class="chargement">Chargement...</div>
				<form class="cache" name="form_options">
					<span id="nom_complet_numero"></span>
					Dimensions de la tranche : 
					<input type="text" id="Dimension_x" name="Dimension_x" maxlength="3" size="2"> mm 
					x 
					<input type="text" id="Dimension_y" name="Dimension_y" maxlength="3" size="2"> mm
					<button id="modifier_dimensions" class="cache small">Modifier</button>
					<br />
					Chacune des manipulations permettant de cr&eacute;er la tranche sont appel&eacute;es des <b>&eacute;tapes</b>.
					<br />
					&lt; Les &eacute;tapes de votre tranche sont pr&eacute;sent&eacute;es &agrave; gauche, dans leur ordre d'utilisation.<br />
					&lt; Cliquez sur une &eacute;tape pour la modifier.<br />
					&lt; Passez la souris entre 2 &eacute;tapes pour en ins&eacute;rer une nouvelle.<br />
					<p class="texte_presentation_tranche_finale">
						La tranche telle qu'elle sera affich&eacute;e dans la biblioth&egrave;que DucksManager est pr&eacute;sent&eacute;e &agrave; gauche de la photo de la tranche. &gt;
					</p> 
				</form>
			</p>
		</div>
		
		<div class="wizard preview_etape initial">
			
		</div>
		
		<div id="options-etape--Agrafer" class="options_etape cache">
			<div class="premiere agrafe"></div>
			<div class="deuxieme agrafe"></div>
			<p>
				&gt; D&eacute;placez et redimensionnez les agrafes.<br />
			</p>
		</div>
		
		<div id="options-etape--Degrade" class="options_etape cache">
			<div class="rectangle_degrade"></div>
			<p>
				&gt; D&eacute;placez et redimensionnez la zone de d&eacute;grad&eacute;.<br />
				
				&gt; D&eacute;finissez la premi&egrave;re couleur.<br />
				<div class="picker couleur_debut cache"></div>
				<label for="option-Couleur_debut">Couleur s&eacute;lectionn&eacute;e : </label>
				<input type="text" name="option-Couleur_debut" size="4" maxlength="7" readonly="readonly"/>
				<br />
				
				&gt; D&eacute;finissez la deuxi&egrave;me couleur.<br />
				<div class="picker couleur_fin cache"></div>
				<label for="option-Couleur_fin">Couleur s&eacute;lectionn&eacute;e : </label>
				<input type="text" name="option-Couleur_fin" size="4" maxlength="7" readonly="readonly"/>
				<br />
				
				&gt; Indiquez le sens du d&eacute;grad&eacute;.<br />
				<div style="font-size:16px">
					<div class="small buttonset">
						<input type="radio" name="option-Sens" value="Horizontal" id="Horizontal" /><label for="Horizontal">Gauche vers droite</label>
						<input type="radio" name="option-Sens" value="Vertical" id="Vertical" /><label for="Vertical">Haut vers bas</label>
					</div>
				</div>
			</p>
		</div>
		
		<div id="options-etape--DegradeTrancheAgrafee" class="options_etape cache">
			<div class="premiere agrafe"></div>
			<div class="deuxieme agrafe"></div>
			<div class="premier rectangle_degrade"></div>
			<div class="deuxieme rectangle_degrade"></div>
			<p>
				&gt; D&eacute;finissez la couleur de fond de la tranche.<br />
				<div class="picker cache"></div>
				<label for="option-Couleur">Couleur s&eacute;lectionn&eacute;e : </label>
				<input type="text" name="option-Couleur" size="4" maxlength="7" readonly="readonly"/>
			</p>
		</div>
		
		<div id="options-etape--Remplir" class="options_etape cache">
			<img class="point_remplissage cache" src="../images/cross.png" />
			<p>
				&gt; D&eacute;placez le curseur en forme de croix pour modifier le point de remplissage.<br />
				&gt; S&eacute;lectionnez une couleur pour modifier la couleur de remplissage.
			</p>
			<form id="options_etape">
				<div class="picker cache"></div>
				<label for="option-Couleur">Couleur s&eacute;lectionn&eacute;e : </label>
				<input type="text" name="option-Couleur" size="4" maxlength="7" readonly="readonly"/>
			</form>
		</div>
		
		<div id="options-etape--Arc_cercle" class="options_etape cache">
			<img class="arc_position cache">
			<p>
				&gt; D&eacute;placez et redimensionnez l'arc de cercle.<br />
				&gt; S&eacute;lectionnez une couleur pour modifier la couleur de remplissage ou de contour.<br />
			</p>
			<form id="options_etape">
				<div class="buttonset">
					<input type="radio" name="option-drag-resize" value="deplacement" id="Arc_deplacement" /><label for="Arc_deplacement">D&eacute;placement</label>
					<input type="radio" name="option-drag-resize" value="redimensionnement"  id="Arc_redimensionnement"/><label for="Arc_redimensionnement">Redimensionnement</label><br /><br />
				</div>
				<div class="picker cache"></div>
				<label for="option-Couleur">Couleur : </label>
				<input type="text" name="option-Couleur" size="4" maxlength="7" readonly="readonly"/>
				<br />
				<input type="checkbox" name="option-Rempli" id="option-Rempli" />&nbsp;<label for="option-Rempli">Remplir l'arc</label> 
					
			</form>
		</div>
		
		<div id="options-etape--Polygone" class="options_etape cache">
			<img class="polygone_position cache">
			<div class="point_polygone modele cache"></div>
			<p>
				&gt; Ajoutez et d&eacute;placer les points du polygone.<br />
				&gt; Indiquez la couleur de remplissage du polygone.<br />
			</p>
			<form id="options_etape">
				<div class="buttonset">
					<input type="radio" name="option-action" value="ajout" id="Point_ajout" /><label for="Point_ajout">Ajout de point</label>
					<input type="radio" name="option-action" value="deplacement" id="Point_deplacement" /><label for="Point_deplacement">D&eacute;placement de point</label>
					<input type="radio" name="option-action" value="suppression" id="Point_suppression" /><label for="Point_suppression">Suppression de point</label>
				</div>
				<div id="descriptions_actions">
					<div id="description_ajout" class="cache">
						Cliquez sur le point apr&egrave;s lequel le nouveau point sera plac&eacute;.
					</div>
					<div id="description_deplacement" class="cache">
						Glissez-d&eacute;posez le point &agrave; d&eacute;placer.
					</div>
					<div id="description_suppression" class="cache">
						Cliquez sur le point &agrave; supprimer.
					</div>
				</div>
				<div class="picker cache"></div><br />
				<label for="option-Couleur">Couleur du polygone : </label>
				<input type="text" name="option-Couleur" size="4" maxlength="7" readonly="readonly"/>
					
			</form>
		</div>
		
		<div id="options-etape--Rectangle" class="options_etape cache">
			<div class="rectangle_position cache"></div>
			<p>
				&gt; D&eacute;placez et redimensionnez le rectangle.<br />
				&gt; S&eacute;lectionnez une couleur pour modifier la couleur de remplissage ou de contour.<br />
			</p>
			<form id="options_etape">
				<div class="picker cache"></div>
				<label for="option-Couleur">Couleur : </label>
				<input type="text" name="option-Couleur" size="4" maxlength="7" readonly="readonly"/>
				<br />
				<input type="checkbox" name="option-Rempli" id="option-Rempli" />&nbsp;<label for="option-Rempli">Remplir le rectangle</label> 
					
			</form>
		</div>
		
		<div id="options-etape--Image" class="options_etape cache">
			<div class="image_position cache"></div>
			<p>
				&gt; D&eacute;placez et redimensionnez l'image incrust&eacute;e.<br />
			</p>
			<form id="options_etape">
				Image utilis&eacute;e : 
				<input type="text" name="option-Source" readonly="readonly" />
				<button class="small" name="parcourir">Parcourir</button>
				<br />
				<img class="apercu_image hidden" />
			</form>
		</div>
		
		<div id="options-etape--TexteMyFonts" class="options_etape cache">
			<input type="hidden" name="original_preview_width" />
			<input type="hidden" name="original_preview_height" />
			<div class="image_position cache"></div>
			<div class="accordion">
				<h3><a href="#">Propri&eacute;t&eacute;s du texte</a></h3>
				<div class="proprietes_texte">
					<table style="border:0" cellspacing="0" cellpadding="1">
						<tr>
							<td>Texte : </td>
							<td><input name="option-Chaine" type="text" maxlength="90" size="30" /></td>
						</tr>
						<tr>
							<td>Police de caract&egrave;res : </td>
							<td><input name="option-URL" type="text" maxlength="90" size="30" /></td>
						</tr>
						<tr>
							<td>
								<label for="option-Couleur_texte">Couleur du texte : </label>
							</td>
							<td>
								<div class="picker texte cache"></div>
								<input type="text" name="option-Couleur_texte" size="4" maxlength="7" readonly="readonly"/>
							</td>
						</tr>
						<tr>
							<td>
								<label for="option-Couleur_fond">Couleur du fond : </label>
							</td>
							<td>
								<div class="picker fond cache"></div>
								<input type="text" name="option-Couleur_fond" size="4" maxlength="7" readonly="readonly"/>
							</td>
						</tr>
						<tr>
							<td colspan="2" style="text-align: center">
								<br />
								Texte g&eacute;n&eacute;r&eacute; : <br />
								<div class="apercu_myfonts">
									<img />
								</div>
							</td>
						</tr>
						<tr>
					</table>
				</div>
				<h3><a href="#">Finition du texte g&eacute;n&eacute;r&eacute;</a></h3>
				<div class="finition_texte_genere">
					Faites glisser le bord droit du texte g&eacute;n&eacute;r&eacute; de fa&ccedil;on &agrave; ce qu'il soit enti&egrave;rement visible.
					<br />
					<input type="checkbox" name="option-Demi_hauteur" id="option-Demi_hauteur" />&nbsp;<label for="option-Demi_hauteur">Cochez cette case pour &eacute;viter que le texte apparaisse sur 2 lignes.</label> 
					<br /><br />
					<div>
						<div class="extension_largeur cache">&nbsp;</div>
						<table style="border:0" cellspacing="0" cellpadding="0">
							<tr>
								<td colspan="2" style="text-align: center">
									<div class="apercu_myfonts">
										<img />
									</div>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<h3><a href="#">Rotation</a></h3>
				<div class="rotation">
					Faites tourner la zone de texte pour modifier la rotation du texte sur la tranche.
					<br />
					<table style="border:0" cellspacing="0" cellpadding="1">
						<tr style="height: 320px">
							<td>
								<a href="javascript:void(0)" name="fixer_rotation -90">Fixer &agrave; -90 &deg;</a><br />
								<a href="javascript:void(0)" name="fixer_rotation 0">Fixer &agrave; 0 &deg;</a><br />
								<a href="javascript:void(0)" name="fixer_rotation 90">Fixer &agrave; 90 &deg;</a><br />
								<a href="javascript:void(0)" name="fixer_rotation 180">Fixer &agrave; 180 &deg;</a><br />
							</td>
							<td><input name="option-Rotation" type="text" maxlength="90" size="35" readonly="readonly"
									   value="Faites tourner cette zone (Rotation=0.00&deg;)" /></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	
		<div id="wizard-numero-inconnu" class="wizard dead-end" title="Num&eacute;ro non r&eacute;f&eacute;renc&eacute;">
			<p>
				Les tranches ne peuvent &ecirc;tre reproduites que pour les num&eacute;ros 
				r&eacute;f&eacute;renc&eacute;s sur la base <a target="_blank" href="http://coa.inducks.org">Inducks</a>.
				R&eacute;f&eacute;rencez votre num&eacute;ro pour Inducks pour qu'il apparaisse dans les listes.
			</p>
		</div>

<!--  Dialogues issus du menu et utilitaires -->

<div id="wizard-ajout-etape" class="first wizard modal" title="Ajouter une &eacute;tape">
	<p>
		Que souhaitez-vous faire ? 
		<form>
			<div id="liste_fonctions"></div>
			<input type="hidden" name="etape" />
			<input type="hidden" name="pos" />
		</form>
	</p>
</div>

<div id="wizard-upload" class="wizard" title="Stockage d'une photo de tranche">
	<p>
		S&eacute;lectionnez une photo de tranche stock&eacute;e sur votre ordinateur 
		pour la placer &agrave; c&ocirc;t&eacute; de votre mod&egrave;le de tranche et ainsi pouvoir les comparer facilement.
	</p>
	<iframe src="<?=base_url().'index.php/helper/index/image_upload.php?photo_tranche'?>"></iframe>
</div>

<div id="wizard-gallery" class="wizard" title="Choix d'une image">
	<p>
		S&eacute;lectionnez une image.
	</p>
	<p class="chargement_images" >Chargement des images</p>
	<p class="pas_d_image cache" >Aucune image r&eacute;pertori&eacute;e pour ce pays</p>
	<ul class="gallery cache">
		<li class="template">
			<img />
		</li>
	</ul>
</div>

<div id="wizard-resize" class="wizard first closeable" title="Retouche d'image">
	<p>
		Rognez l'image.
	</p>
	<img /><br />
	<div class="error crop_inconsistent cache">Une partie de votre s&eacute;lection est situ&eacute;e en dehors de l'image.</div>
	<form>
		<div class="buttonset">
			<input type="hidden" checked="checked" name="choix" value="do-in-wizard-enregistrer" id="do-in-wizard-enregistrer" />
			<input type="hidden" checked="checked" name="onClose" value="to-wizard-photos" id="to-wizard-photos" />
		</div>
	</form>
</div>

<div id="wizard-confirmation-supprimer" class="wizard" title="Supprimer l'&eacute;tape ?">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
	Vous allez supprimer cette &eacute;tape. Continuer ?</p>
</div>

<div id="wizard-confirmation-rechargement" class="wizard" title="Sauvegarder les changements ?">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
	Vous avez modifi&eacute; l'&eacute;tape ouverte sans valider ses modifications. 
	Souhaitez-vous valider ces modifications ?</p>
</div>

<div id="wizard-confirmation-annulation" class="wizard" title="Sauvegarder les changements ?">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
	Vous avez modifi&eacute; l'&eacute;tape que vous souhaitez fermer. 
	Souhaitez-vous sauvegarder ces modifications ?</p>
</div>

<div id="wizard-confirmation-suppression" class="wizard" title="Supprimer cette &eacute;tape ?">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
	Cette &eacute;tape va &ecirc;tre supprim&eacute;e. Continuer ?</p>
	<span id="num_etape_a_supprimer" class="cache"></span>
</div>

<div id="wizard-confirmation-suppression-point" class="wizard" title="Supprimer ce point ?">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
	Ce point du polygone va &ecirc;tre supprim&eacute;. Continuer ?</p>
	<span id="nom_point_a_supprimer" class="cache"></span>
</div>

<div id="wizard-confirmation-resize" class="wizard" title="Nom de l'image ?">
	<p>
	Une nouvelle image va &ecirc;etre cr&eacute;e. Indiquez le nom que vous souhaitez lui donner.
	(Exemples : <i>Tete Donald</i> ; <i>Motif arriere plan</i>, etc.</p>
	<form>
		<input type="text" name="nom_image" />
	</form>
</div>

<div id="wizard-confirmation-desactivation-modele" class="wizard" title="Suppression d'un mod&egrave;le EdgeCreator">
	<p>
	Le mod&egrave;le EdgeCreator en cours de conception va &ecirc;tre d&eacute;sactiv&eacute;. Confirmer ?
	</p>
</div>

<div id="wizard-confirmation-validation-modele" class="wizard" title="Validation d'un mod&egrave;le EdgeCreator">
	<p>
	Le mod&egrave;le EdgeCreator en cours de conception va verrouill&eacute; en attendant sa validation. Confirmer ?
	</p>
</div>