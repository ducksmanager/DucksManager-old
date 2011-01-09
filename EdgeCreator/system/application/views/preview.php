<div id="chargement"></div>
<div id="visu_gen_doublons" class="toggleable_links" style="float:left;overflow-y:auto;height:100%;overflow-x:auto;">
    <img alt="Prefix" id="preview_prefix" src="<?=site_url('viewer/index/'.$pays.'/'.$magazine.'/')?>" />
    Zoom : <?=$zoom?>
    <a name="visu" class="toggleable visu_gen_doublons actif" href="javascript:void(0)">Visualiser</a> | 
    <a name="gen" class="toggleable visu_gen_doublons" href="javascript:void(0)">G&eacute;n&eacute;rer</a> | 
    <a name="doublons" class="toggleable visu_gen_doublons" href="javascript:void(0)">Sv les doublons</a><br /><br />
    <div name="visu" class="toggleable actif visu_gen_doublons">
        <?=$preview_form?>
        <br /><br />
        <img id="preview" alt="preview" src="" />
        <img alt="regle" height="300" id="regle" src="http://localhost/DucksManager/images/regle.png" />
        <div id="error_log"></div>
    </div>
    <div name="gen" class="toggleable visu_gen_doublons">
        <?=$gen_form?>
        <br />
        Les images seront enregistr&eacute;es uniquement si le niveau de zoom est r&eacute;gl&eacute; &agrave; 1.5
        <div id="generated_issues"></div>
    </div>
    <div name="doublons" class="toggleable visu_gen_doublons">
        <a href="javascript:void(0)" onclick="sv_doublons('<?=$pays?>','<?=$magazine?>')">Go !</a>
    </div>
</div>