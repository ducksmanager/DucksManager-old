var flipbook;
var flipbookViewport;

function addPage(page, book) {
    // Create a new element for this page
    var element = jQuery('<div />', {});

    // Add the page to the flipbook
    if (book.turn('addPage', element, page)) {

        // Add the initial HTML
        // It will contain a loader indicator and a gradient
        element.html('<div class="gradient"></div><div class="loader"></div>');

        // Load the page
        loadPage(page, element);
    }

}

function loadPage(page, pageElement) {

    // Create an image element

    var img = jQuery('<img />');

    img.mousedown(function(e) {
        e.preventDefault();
    });

    img.on('load', function() {
        jQuery(this)
            .css({width: '100%', height: '100%'})
            .appendTo(pageElement);

        pageElement.find('.loader').remove();
    });

    img.attr('src', 'pages/' +  page + '.jpg');
}

function disableControls(page) {
    jQuery('.previous-button').toggle(page!==1);
    jQuery('.next-button').toggle(page!==flipbook.turn('pages'));
}

function resizeViewport() {

    var width = jQuery(window).width(),
        height = jQuery(window).height(),
        options = flipbook.turn('options');

    flipbook.removeClass('animated');

    flipbookViewport.css({
        width: width,
        height: height
    });

    if (flipbook.turn('zoom')===1) {
        var bound = calculateBound({
            width: options.width,
            height: options.height,
            boundWidth: Math.min(options.width, width),
            boundHeight: Math.min(options.height, height)
        });

        if (bound.width%2!==0)
            bound.width-=1;


        if (bound.width!==flipbook.width() || bound.height!==flipbook.height()) {

            flipbook.turn('size', bound.width, bound.height);

            if (flipbook.turn('page')===1)
                flipbook.turn('peel', 'br');

            jQuery('.next-button').css({height: bound.height, backgroundPosition: '-38px '+(bound.height/2-32/2)+'px'});
            jQuery('.previous-button').css({height: bound.height, backgroundPosition: '-4px '+(bound.height/2-32/2)+'px'});
        }

        flipbook.css({top: -bound.height/2, left: -bound.width/2});
    }

    var magazineOffset = flipbook.offset(),
        boundH = height - magazineOffset.top - flipbook.height(),
        marginTop = (boundH - jQuery('.thumbnails > div').height()) / 2;

    if (marginTop < 0) {
        jQuery('.thumbnails').css({height:1});
    } else {
        jQuery('.thumbnails').css({height: boundH});
        jQuery('.thumbnails > div').css({marginTop: marginTop});
    }

    jQuery('.made').toggle(magazineOffset.top>=jQuery('.made').height());
    flipbook.addClass('animated');
}

// Calculate the width and height of a square within another square

function calculateBound(d) {
    var bound = {width: d.width, height: d.height};

    if (bound.width>d.boundWidth || bound.height>d.boundHeight) {

        var rel = bound.width/bound.height;

        if (d.boundWidth/rel>d.boundHeight && d.boundHeight*rel<=d.boundWidth) {

            bound.width = Math.round(d.boundHeight*rel);
            bound.height = d.boundHeight;

        } else {

            bound.width = d.boundWidth;
            bound.height = Math.round(d.boundWidth/rel);

        }
    }

    return bound;
}

function loadBook(pages, edge) {

    edge = jQuery(edge);

    jQuery('#body').prepend(
        jQuery('.book.template').clone(true).removeClass('template').attr({id: 'canvas'})
    );

    flipbook = jQuery('.magazine');
    flipbook
        .addClass('init')
        .css({visibility: 'hidden'});

    flipbookViewport = jQuery('.magazine-viewport');

    // Check if the CSS was already loaded
    if (flipbook.width()===0 || flipbook.height()===0) {
        setTimeout(loadBook, 10);
        return;
    }

    jQuery.each(pages, function(i, page) {
        var isLeftPage = parseInt(page.page) % 2 === 0;

        var pageImage = jQuery('<img>', {src: page.url})
            .toggleClass('odd', isLeftPage).toggleClass('even', !isLeftPage)
            .toggleClass('hard', i===0);
        flipbook.append(pageImage);

        // Next page is not the opposite right page => create a blank page
        if (isLeftPage && !(pages[i+1] && parseInt(pages[i+1].page) === parseInt(page.page)+1)) {
            pageImage.after(jQuery('<img>'));
        }

        if (i === 0) {
            pageImage.on('load', function() {
                var width = edge.width() + 2* edge.height() / (this.height / this.width);
                showBook(edge, width, edge.height())
            })
        }
    });

}

function showBook(edge, width, height) {

    flipbook.turn({
        width: width,
        height: height,
        duration: 1000,
        gradients: true,
        autoCenter: true,
        elevation: 50,
        pages: flipbook.children(':not(.ignore)').length,

        when: {
            turning: function(event, page) {
                window.location.href = '#page/' + page;
                disableControls(page);

            },

            turned: function(event, page) {
                disableControls(page);

                jQuery(this).turn('center');

                if (flipbook.hasClass('init') && page === 1) {
                    var page_wrapper = jQuery(event.target).find('[page="'+1+'"]');

                    edge
                        .css({position: 'absolute', top: edge.offset().top, left: edge.offset().left})
                        .animate(
                            {top: page_wrapper.offset().top, left: page_wrapper.offset().left},
                            {
                                complete: function() {
                                    var targetWidth = page_wrapper.width();

                                    var elementsToFadeIn = page_wrapper.add(flipbook.find('.shadow'));
                                    elementsToFadeIn.css({width: 0, right: targetWidth});

                                    edge.animate(
                                        { width: 'toggle', height: edge.height() },
                                        { duration: 500 }
                                    );

                                    flipbook.css({visibility: 'visible'});

                                    elementsToFadeIn
                                        .animate(
                                            { width: targetWidth, right: 0 },
                                            { duration: 500, complete: function() {
                                                flipbook.removeClass('init');
                                            }}
                                        );
                                }
                            }
                        );
                }
            },

            missing: function (event, pages) {
                for (var i = 0; i < pages.length; i++)
                    addPage(pages[i], jQuery(this));
            }
        }
    });

    jQuery(document).keydown(function(e){
        var previous = 37, next = 39, esc = 27;

        switch (e.keyCode) {
            case previous:
                flipbook.turn('previous');
                e.preventDefault();

                break;
            case next:
                flipbook.turn('next');
                e.preventDefault();

                break;
            case esc:
                e.preventDefault();

                break;
        }
    });

    // Events for the next button

    jQuery('.next-button').bind(jQuery.mouseEvents.over, function() {
        jQuery(this).addClass('next-button-hover');

    }).bind(jQuery.mouseEvents.out, function() {
        jQuery(this).removeClass('next-button-hover');

    }).bind(jQuery.mouseEvents.down, function() {
        jQuery(this).addClass('next-button-down');

    }).bind(jQuery.mouseEvents.up, function() {
        jQuery(this).removeClass('next-button-down');

    }).click(function() {
        flipbook.turn('next');

    });

    // Events for the next button

    jQuery('.previous-button').bind(jQuery.mouseEvents.over, function() {
        jQuery(this).addClass('previous-button-hover');

    }).bind(jQuery.mouseEvents.out, function() {
        jQuery(this).removeClass('previous-button-hover');

    }).bind(jQuery.mouseEvents.down, function() {
        jQuery(this).addClass('previous-button-down');

    }).bind(jQuery.mouseEvents.up, function() {
        jQuery(this).removeClass('previous-button-down');

    }).click(function() {
        flipbook.turn('previous');

    });

    resizeViewport();

    flipbook.addClass('animated');
}