<div id="chargement"></div>
<div style="float:left;overflow-y:auto;height:100%;overflow-x:auto;">
    <img alt="Prefix" id="preview_prefix" src="<?=site_url('viewer/index/'.$pays.'/'.$magazine.'/')?>" />
    Zoom : <?=$zoom?>
    <a href="javascript:void(0)" class="toggleable visu_gen visu actif">Visualiser</a> | 
    <a class="toggleable visu_gen gen" href="javascript:void(0)">G&eacute;n&eacute;rer</a><br /><br />
    <div id="visu" class="toggleable actif visu_gen">
        <?=$preview_form?>
        <br /><br />
        <img id="preview" alt="preview" src="" />
        <img alt="regle" height="300" id="regle" src="http://localhost/DucksManager/images/regle.png" />
        <div id="error_log"></div>
    </div>
    <div id="gen" class="toggleable visu_gen">
        <?=$gen_form?>
        <br />
        Les images seront enregistr&eacute;es uniquement si le niveau de zoom est r&eacute;gl&eacute; &agrave; 1.5
        <div id="generated_issues"></div>
    </div>
</div>