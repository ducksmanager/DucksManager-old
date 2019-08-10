function toggle_item_menu(element_clic) {
    element_clic = jQuery(element_clic);

    var tagName = element_clic.prop('tagName').toUpperCase();
    if (tagName==='LI') {
        element_clic = element_clic.parent();
    }
    element_clic.parent().find('li.active').removeClass('active');
    element_clic.toggleClass('active');
    element_clic.parent().find('li a').each(function(i, element) {
        jQuery('#contenu_'+element.attr('name')).css({'display':'none'});
    });
    jQuery('#contenu_'+element_clic.children().eq(0).attr('name')).css({'display':'block'});
}
