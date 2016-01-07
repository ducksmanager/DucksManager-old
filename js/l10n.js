function l10n_action(fonction,index,param,param2) {
    if (typeof index!='string') {
        index_param=index.join('~')+'~';
    }
    else
        index_param=index;
    new Ajax.Request('locales/lang.php', {
        method: 'post',
        parameters:'index='+index_param,
        onSuccess:function(transport,json) {
            if (transport.responseText.indexOf('~')!=-1) {
                transport.responseText=transport.responseText.split('~');
            }

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
                        case 'remplirSpanName':
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

function remplirSpanName (nameSpan, trad) {
    $$('[name="'+nameSpan+'"]').each(function (element) {
        $(element).update(trad);
    })
}