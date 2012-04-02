<div id="login-form" class="wizard" title="Bienvenue sur EdgeCreator !">	
	<p>
		DucksManager EdgeCreator est un outil permettant de reconstituer des tranches de magazines Disney,
		afin qu'elles apparaissent fid&egrave;lement dans votre biblioth&egrave;que DucksManager.  
	</p>
	<hr />
	<p>
		Si vous poss&eacute;dez un acc&egrave;s &agrave; EdgeCreator,
		entrez vos identifiants ci-dessous et cliquez sur "Connexion".
		<br />
		Sinon, cliquez sur "Connexion en tant que visiteur".
	</p>
	<p class="erreurs"></p>
	<form>
		<fieldset>
			<label for="username">Pseudo: </label>
			<input type="text" name="username" id="username" class="text ui-widget-content ui-corner-all" />
			<label for="password">Mot de passe: </label>
			<input type="password" name="password" id="password" value="" class="text ui-widget-content ui-corner-all" />
			<br />
			<input type="checkbox" name="mode_expert" id="mode_expert" value="" class="text ui-widget-content ui-corner-all" />
			<label for="mode_expert">Mode expert</label>
		</fieldset>
	</form>
</div>


<div id="wizard-1" class="wizard" title="Assistant DucksManager">
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
	
		<div id="wizard-proposition-clonage" class="wizard" title="Assistant DucksManager - Cr&eacute;ation">
			<p>
				Certaines tranches sont d&eacute;j&agrave; pr&ecirc;tes pour le magazine s&eacute;lectionn&eacute;. 
				Si la v&ocirc;tre ressemble &agrave; l'une d'elles, sa cr&eacute;ation sera facilit&eacute;e.<br />
				L'une des tranches si-dessous est identique &agrave; la v&ocirc;tre, ou bien seules quelques couleurs ou quelques textes sont diff&eacute;rents ? 
				Si oui, s&eacute;lectionnez cette tranche. Sinon, cliquez sur "Cr&eacute;er une tranche originale".
				<form>
					<div id="tranches_pretes_magazine">
					
					</div>
					<br />
					<div class="buttonset">
						<input type="radio" checked="checked" name="choix" value="to-wizard-clonage" id="to-wizard-clonage" /><label for="to-wizard-clonage">J'ai trouv&eacute; une tranche similaire</label>
						<input type="radio" name="choix" value="to-wizard-conception" id="to-wizard-conception1" /><label for="to-wizard-conception1">Cr&eacute;er une tranche originale</label>
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
					
					<input type="hidden" checked="checked" name="choix" value="to-wizard-conception" id="to-wizard-conception2" />
				</p>
			</div>
			
			
		<div id="wizard-conception" class="wizard" title="Assistant DucksManager - Conception de la tranche">
			<p>
				<div class="chargement">Chargement...</div>
				<form class="cache">
					<span id="nom_complet_numero"></span>
					Dimensions de la tranche : 
					<input type="text" id="dimension_x" name="dimension_x" maxlength="3" size="2"> mm 
					x 
					<input type="text" id="dimension_y" name="dimension_y" maxlength="3" size="2"> mm
					<button id="modifier_dimensions" class="cache small">Modifier</button>
					<br />
					La tranche est d&eacute;j&agrave; en partie con&ccedil;ue.<br />
					Chacune des manipulations permettant de cr&eacute;er la tranche sont appel&eacute;es des &eacute;tapes.
					<br />
					&lt; Les &eacute;tapes de votre tranche sont pr&eacute;sent&eacute;es &agrave; gauche, dans leur ordre d'utilisation.<br />
					&lt; 
					Cliquez sur une &eacute;tape pour l'afficher ou la modifier.<br />
					<p style="text-align: right">
						La tranche telle qu'elle sera affich&eacute;e dans la biblioth&egrave;que DucksManager est pr&eacute;sent&eacute;e &agrave; droite. &gt;
					</p> 
				</form>
			</p>
		</div>
		
		<div class="wizard preview_etape initial">
			
		</div>
		
		<div id="options-etape--Remplir" class="options_etape cache">
			<img class="point_remplissage" src="../images/cross.png" />
			<p>
				Le remplissage de la tranche est d&eacute;fini par une couleur et par des coordonn&eacute;es indiquant le point &agrave; partir duquel le remplissage est effectu&eacute;.
				<br />
				&gt; D&eacute;placez le curseur en forme de croix pour modifier le point de remplissage.<br />
				&gt; S&eacute;lectionnez une couleur pour modifier la couleur de remplissage.
			</p>
			<form id="options_etape">
				<div class="picker cache"></div>
				<label for="option-Couleur">Couleur s&eacute;lectionn&eacute;e : </label>
				<input type="text" name="option-Couleur" size="4" maxlength="7" readonly="readonly"/>
			</form>
		</div>
		
		<div id="options-etape--TexteMyFonts" class="options_etape cache">
			<div style="text-align: center">Propri&eacute;t&eacute;s du texte</div>
			<div style="border:1px dashed black">
				<table style="border:0" cellspacing="0" cellpadding="1">
					<tr>
						<td>Texte : </td>
						<td><input name="option-Chaine" type="text" maxlength="90" size="30" /></td>
					</tr>
					<tr>
						<td>Police de caract&egrave;res : </td>
						<td><input name="option-URL" type="text" maxlength="30" size="20" /></td>
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
							Aper&ccedil;u : <br />
							<div class="apercu_myfonts"></div>
						</td>
					</tr>
				</table>
			</div>
			<br />
			<div style="text-align: center">Int&eacute;gration &agrave; la tranche</div>
			<div style="border:1px dashed black">
				<table style="border:0" cellspacing="0" cellpadding="1">
					<tr>
						<td>Rotation : </td>
						<td><input name="option-Rotation" type="text" maxlength="90" size="30" /></td>
					</tr>
					<tr>
						<td>Compression (?) : </td>
						<td><input name="option-Compression" type="text" maxlength="30" size="20" /></td>
					</tr>
				</table>
			</div>
		</div>
	
		<div id="wizard-numero-inconnu" class="wizard dead-end" title="Num&eacute;ro non r&eacute;f&eacute;renc&eacute;">
			<p>
				Les tranches ne peuvent &ecirc;tre reproduites que pour les num&eacute;ros 
				r&eacute;f&eacute;renc&eacute;s sur la base <a target="_blank" href="http://coa.inducks.org">Inducks</a>.
				R&eacute;f&eacute;rencez votre num&eacute;ro pour Inducks pour qu'il apparaisse dans les listes.
			</p>
		</div>
