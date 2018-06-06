var flipbook;
var flipbookViewport;

var isLoadingBook = false,
    isHidingBook = false;

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
    flipbook.find('.previous-button').toggle(page!==1);
    flipbook.find('.next-button').toggle(page!==flipbook.turn('pages'));
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

            flipbook.find('.next-button').css({height: bound.height, backgroundPosition: '-38px '+(bound.height/2-32/2)+'px'});
            flipbook.find('.previous-button').css({height: bound.height, backgroundPosition: '-4px '+(bound.height/2-32/2)+'px'});
        }

        flipbook.css({top: -bound.height/2, left: -bound.width/2});
    }

    var magazineOffset = flipbook.offset(),
        boundH = height - magazineOffset.top - flipbook.height(),
        marginTop = (boundH - jQuery('.thumbnails > div').height()) / 2;

    if (marginTop < 0) {
        flipbook.find('.thumbnails').css({height:1});
    } else {
        flipbook.find('.thumbnails').css({height: boundH});
        flipbook.find('.thumbnails > div').css({marginTop: marginTop});
    }

    flipbook.find('.made').toggle(magazineOffset.top>=flipbook.find('.made').height());
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

function isLeftPage(page) {
    return page % 2 === 0;
}

function loadBook(pages, edge) {
    if (isLoadingBook) {
        return;
    }
    isLoadingBook = true;

    edge = jQuery(edge);

    var book = jQuery('.book.template').clone(true).removeClass('template').attr({id: 'canvas'});
    jQuery('#body').prepend(book);

    flipbook = book.find('.magazine');
    flipbook
        .addClass('init')
        .css({visibility: 'hidden'});

    flipbookViewport = book.find('.magazine-viewport');

    // Check if the CSS was already loaded
    if (flipbook.width()===0 || flipbook.height()===0) {
        setTimeout(loadBook, 10);
        return;
    }

    var shownPageNumber = 0;
    var firstPage;
    jQuery.each(pages, function(i, page) {
        var currentPageNumber = parseInt(page.page);
        var isCurrentPageLeftPage = i !== 0 && isLeftPage(currentPageNumber);

        var pageImage = jQuery('<img>', {src: page.url})
            .addClass('p' + (++shownPageNumber))
            .toggleClass('odd', isCurrentPageLeftPage).toggleClass('even', !isCurrentPageLeftPage)
            .toggleClass('hard', i === 0);
        flipbook.append(pageImage);

        var previousPage = pages[i-1] && parseInt(pages[i-1].page);
        if (previousPage && isLeftPage(previousPage) && previousPage + 1 !== currentPageNumber) {
            // Draw the right page opposite to the previous page
            flipbook.find('.p' + shownPageNumber).before(jQuery('<img>').addClass('p' + (++shownPageNumber)));
        }

        if (i === 0) {
            firstPage = pageImage;
        }
        else {
            if (!isCurrentPageLeftPage) {
                // Draw the left page opposite to the current page
                flipbook.find('.p' + shownPageNumber).before(jQuery('<img>').addClass('p' + (++shownPageNumber)));
            }
        }
    });

    firstPage
        .on('load', function() {
            var width = edge.width() + 2* edge.height() / (this.height / this.width);
            showBook(edge, shownPageNumber, width, edge.height())
        });
}

function showBook(edge, pageNumber, width, height) {

    flipbook.turn({
        width: width,
        height: height,
        duration: 1000,
        gradients: true,
        autoCenter: true,
        elevation: 50,
        pages: pageNumber,

        when: {
            turning: function(event, page) {
                disableControls(page);
            },

            turned: function(event, page) {
                disableControls(page);

                jQuery(this).turn('center');

                if (flipbook.hasClass('init') && page === 1) {
                    var page_wrapper = jQuery(event.target).find('[page="1"]');

                    edge
                        .addClass('livre-visible')
                        .css({top: edge.offset().top, left: edge.offset().left})
                        .data({bookcaseOffset: edge.offset()})
                        .animate(
                            {top: page_wrapper.offset().top, left: page_wrapper.offset().left},
                            {
                                complete: function() {
                                    jQuery(document).click(hideBook);

                                    var targetWidth = page_wrapper.width();

                                    var elementsToFadeIn = page_wrapper.add(flipbook.find('.shadow'));
                                    elementsToFadeIn.css({width: 0, right: targetWidth});

                                    edge.animate(
                                        { width: 'toggle', height: edge.height() },
                                        { duration: 500 }
                                    );

                                    flipbook.css({visibility: 'visible'});

                                    elementsToFadeIn.animate(
                                        { width: targetWidth, right: 0 },
                                        { duration: 500, complete: function() {
                                            flipbook.removeClass('init');
                                            isLoadingBook = false;
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

    jQuery(document).keydown(onKeyDown);

    // Events for the next button

    flipbook.find('.next-button').bind(jQuery.mouseEvents.over, function() {
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

    flipbook.find('.previous-button').bind(jQuery.mouseEvents.over, function() {
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

function hideBook(e, callback) {
    var book = jQuery('#canvas');

    var isClickOnEdge = e === null;
    var isClickOnFirstPage = e && jQuery(e.target).is('.page.p1');
    var isClickElsewhere = e && !jQuery(e.target).is('.tranche, .page, .previous-button, .next-button');

    if (book.length
     && (isClickOnEdge || isClickOnFirstPage || isClickElsewhere)
     && !isHidingBook && !flipbook.turn("animating")) {
        isHidingBook = true;

        var edge = jQuery('.tranche.livre-visible');
        var firstPage = flipbook.find('.page.p1');

        var _hideBook = function() {
            flipbook.addClass('init');
            firstPage
                .add(firstPage.find('> div'))
                .add(jQuery.find('.shadow'))
                .animate(
                    {width: 0, right: 282},
                    { duration: 500 }
                );

            edge.animate(
                { width: 'toggle', height: edge.height() },
                { duration: 500, complete: function() {
                        flipbook.turn('destroy');

                        jQuery(document)
                            .unbind('keydown', onKeyDown)
                            .unbind('click', hideBook);
                        book.remove();
                        edge.animate(edge.data().bookcaseOffset, { complete: function() {
                                edge
                                    .removeClass('livre-visible')
                                    .css({position: ''});
                                isHidingBook = false;
                                callback && callback();
                            }});
                    }}
            );
        };

        if (firstPage.is(':visible')) {
            _hideBook();
        }
        else {
            flipbook.turn("page", 1);
            setTimeout(_hideBook, 2000);
        }
    }
    else {
        callback && callback();
    }
}

function onKeyDown(e){
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
}