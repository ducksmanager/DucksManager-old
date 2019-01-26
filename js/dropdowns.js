function charger_dropdowns() {
    $('.dropdown-menu').each(function(i, dropdown) {
        $(dropdown).find('li a').on('click', function() {
            var selectedValue = $(this).data().dropdownOption;
            var menuWrapper = $(this).closest('.dropdown-menu');
            var dropdownName = menuWrapper.data().dropdownName;

            menuWrapper.siblings('.dropdown-toggle').find('.selected').html($(this).html());
            menuWrapper.siblings('input[name="'+dropdownName+'"]').val(selectedValue);
        });
    });
}