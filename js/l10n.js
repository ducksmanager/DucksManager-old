function l10n_action(fonction,index,param, callback) {
    var index_param;
    if (typeof index!=='string') {
        index_param=index.join('~')+'~';
    }
    else
        index_param=index;
    jQuery.post('locales/lang.php', {
        data: {index: index_param},
        success:function(response) {
            if (response.indexOf('~')!==-1) {
	            response=response.split('~');
            }

            if (typeof response==='string') {
                if (fonction==='remplirSpan')
                    window[fonction](index,response);
                else
                    window[fonction](response);
            }
            else {
                jQuery.each(response, function(i, chunk) {
                    switch (fonction) {
                        case 'remplirSpanName':
                            window[fonction](index[i],chunk);
                            break;
                        case 'fillArray':
                            window[param][index[i]]=chunk;
                            break;
                        case 'remplirSpan':
                            window[fonction](index[i],chunk);
                            break;
                        default:
                            window[fonction](chunk);
                    }
                });
            }

            callback && callback();
        }
    });
}

function remplirSpan (idSpan, trad) {
	jQuery('#'+idSpan).text(trad);
}

function remplirSpanName (nameSpan, trad) {
    jQuery('[name="'+nameSpan+'"]').text(trad);
}
