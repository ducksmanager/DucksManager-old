<div id="wizard-accueil" class="first wizard" title="Bienvenue sur EdgeCreator !">
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

<div id="wizard-accueil2" class="wizard" title="Bienvenue sur EdgeCreator !">
	<p>
		Pour cr&eacute;er une tranche, vous aurez besoin :<br />
		<div style="float: left;width:50%">
			<img style="width: 100%" src="../images/regle.png" />
			D'une r&egrave;gle
		</div>
		<div style="float: left;width:50%">
			<img style="width: 100%" src="../images/appareil_photo.png" />
			D'un appareil photo (ceux des t&eacute;l&eacute;phones donnent parfois des photos floues)
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
				<input type="radio" name="choix" value="to-wizard-creer" id="to-wizard-creer" /><label for="to-wizard-creer">Cr&eacute;er une tranche de magazine</label><br />
				<input type="radio" name="choix" value="to-wizard-modifier" id="to-wizard-modifier"/><label for="to-wizard-modifier">Modifier une tranche de magazine</label><br />
				<input type="radio" name="choix" value="to-wizard-conception" id="to-wizard-conception" class="disabled"/><label for="to-wizard-conception">Poursuivre une conception de tranche</label><br />
			</div>
			<div id="tranches_en_cours" class="buttonset cache">
				<input type="radio" name="choix_tranche_en_cours" value="numero_tranche_en_cours" id="numero_tranche_en_cours" class="init"/><label for="numero_tranche_en_cours">N&deg;</label>
			</div>
		</form>
	</p>
</div>


	<div id="wizard-creer" class="wizard" title="Assistant DucksManager - Choix de num&eacute;ro">
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
			
			
		<div id="wizard-conception" class="main wizard" title="Assistant DucksManager - Conception de la tranche">
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
					&lt; 
					Cliquez sur une &eacute;tape pour la modifier.<br />
					<p class="texte_presentation_tranche_finale">
						La tranche telle qu'elle sera affich&eacute;e dans la biblioth&egrave;que DucksManager est pr&eacute;sent&eacute;e &agrave; droite. &gt;
					</p> 
				</form>
			</p>
		</div>
		
		<div class="wizard preview_etape initial">
			
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
					<input type="checkbox" name="option-Demi_hauteur" id="option-Demi_hauteur" />&nbsp;<label for="option-Demi_hauteur">Cochez cette case si le texte apparait sur 2 lignes.</label> 
					<br /><br />
					<div>
						<div class="extension_largeur cache">&nbsp;</div>
						<table style="border:0" cellspacing="0" cellpadding="0">
							<tr>
								<td colspan="2" style="text-align: center">
									<div class="apercu_myfonts">
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
				<h3><a href="#">Positionnement</a></h3>
				<div class="positionnement">
					<div class="chargement cache">Veuillez patienter...</div>
					<div class="apres chargement">
						<ul>
							<li>
								D&eacute;placez la zone de texte au niveau de la tranche pour positionner le texte.
							</li>
							<li>
								Tirez les bords de la zone de texte pour &eacute;tirer sa largeur ou sa hauteur.
							</li>
						</ul>
						<div class="apercu_myfonts hidden">
						</div>
					</div>
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
	<ul class="gallery cache">
		<li class="template">
			<img />
		</li>
	</ul>
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