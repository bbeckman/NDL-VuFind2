/*global VuFind, finna */
finna.feed = (function finnaFeed() {
  function calculateScrollSpeed(scrollCnt, scrollSpeed) {
    return scrollSpeed * Math.max(1, (scrollCnt / 5));
  }

  // Horizontal carousel:
  function centerImages(holder) {
    holder.find('.carousel-feed:not(.slick-vertical) .slick-slide .wrapper img').each (function centerEachImage() {
      centerImage($(this));
    });
  }

  function centerImage(img) {
    var offset = img.width() - img.closest('.slick-slide').width();
    img.css('margin-left', offset > 0 ? '-' + offset / 2 + 'px' : 'auto');
  }

  function adjustWidth(holder) {
    holder.find('.carousel-slide-header p, .carousel-text')
      .width(holder.find('.slick-slide').width() - 20);

    holder.find('.slick-slide .wrapper img').each (function centerEachImage() {
      centerImage($(this));
    });
  }

  function adjustTitles(holder) {
    // Move title field below image
    var maxH = 0;
    holder.find('.carousel-feed .slick-slide .carousel-slide-header p').each(function getMaxH() {
      maxH = Math.max(maxH, $(this).innerHeight());
      $(this).addClass('title-bottom');
    });
    holder.find('.carousel-feed .slick-list').css('padding-bottom', maxH + 'px');
    holder.find('.carousel-feed .slick-slide .carousel-text').addClass('text-bottom');
  }

  // Vertical carousel:
  function adjustTextFields(holder) {
    holder.find('.carousel-feed .slick-slide').each(function adjustEachTextField() {
      adjustTextField($(this));
    });
  }

  function adjustTextField(slide) {
    var imgH = slide.find('.wrapper').height();
    var titleH = slide.find('.carousel-slide-header p').height();
    var textF = slide.find('.carousel-text');
    var pos = titleH + 10;
    textF.css('margin-top', pos + 'px');
    var h = imgH - pos - 10;
    if (h < 0) {
      return;
    }
    var lineH = textF.css('line-height');
    if (lineH) {
      lineH = Math.floor(parseInt(lineH.replace('px', '')));
      var dif = h / lineH;
      h -= (dif - 2);
      textF.height(h);
    }
  }

  function loadFeed(holder) {
    var id = holder.data('feed');
    if (typeof id == 'undefined') {
      return;
    }
    processLoadFeed(holder, {method: 'getFeed', id: id});
  }

  function loadFeedFromUrl(holder) {
    var feedUrl = holder.data('url');
    var id = holder.data('feed');

    if (typeof feedUrl == 'undefined' || typeof id == 'undefined') {
      return;
    }
    processLoadFeed(
      holder,
      {
        method: 'getOrganisationPageFeed',
        url: feedUrl,
        id: id
      }
    );
  }

  function processLoadFeed(holder, params) {
    params['touch-device'] = (finna.layout.isTouchDevice() ? 1 : 0);

    var url = VuFind.path + '/AJAX/JSON?' + $.param(params);

    // Append spinner
    holder.append('<i class="fa fa-spin fa-spinner hide"></i>');
    holder.find('.fa-spin').delay(1000).fadeIn();

    $.getJSON(url)
      .done(function loadFeedDone(response) {
        if (response.data) {
          holder.html(response.data.html);
          var settings = response.data.settings;
          if (typeof settings.height == 'undefined') {
            settings.height = 300;
          }
          var type = settings.type;

          var carousel = type === 'carousel' || type === 'carousel-vertical';

          if (carousel) {
            var vertical = type === 'carousel-vertical';
            settings.vertical = vertical;

            var obj = holder.find('.carousel-feed').slick(getCarouselSettings(settings));

            var titleBottom = typeof settings.titlePosition !== 'undefined' && settings.titlePosition === 'bottom';

            var callbacks = {};
            if (!vertical) {
              callbacks.resize = function resizeCallback() {
                adjustWidth(holder);
                if (titleBottom) {
                  adjustTitles(holder);
                }
                centerImages(holder);
              };
            } else {
              callbacks.resize = function resizeCallback2() {
                adjustTextFields(holder);
              };
            }

            var refreshId = null;
            $(window).resize(function resizeWindow() {
              clearInterval(refreshId);
              refreshId = setTimeout(function resizeTimeoutCallback() {
                callbacks.resize();
              }, 250);
            });

            if (!vertical) {
              adjustWidth(holder);

              if (titleBottom) {
                adjustTitles(holder);
                holder.find('.carousel-hover-title').hide();
              }

              holder.find('.slick-slide')
                .css('max-height', settings.height + 'px')
                .addClass('adjusted-height')
                .find('.wrapper img').css('height', settings.height + 'px')
                .find('.slick-track, .slick-slide .wrapper').css('max-height', settings.height + 'px');
            } else {
              holder.find('.slick-track, .slick-slide .wrapper').css('height', settings.height + 'px');
            }

            // Carousel image onload-listeners
            holder.find('.carousel-feed .slick-slide .wrapper img').each (function addOnLoadListeners() {
              $(this).on('load', function onLoadFeed() {
                if (!vertical) {
                  centerImage($(this));
                } else {
                  adjustTextField($(this).closest('.slick-slide'));
                }
              });
            });

            // Text hover for touch devices
            if (finna.layout.isTouchDevice() && typeof settings.linkText === 'undefined') {
              $('.carousel-text').css('padding-bottom', '30px');
              holder.find('.slick-slide a').click(function onClickSlideLink(/*event*/) {
                if (!$(this).closest('.slick-slide').hasClass('clicked')) {
                  $(this).closest('.slick-slide').addClass('clicked');
                  return false;
                }
              });
              if (navigator.userAgent.match(/iemobile/i)) {
                $('.slick-slide').click(function onIeClick() {
                  $(this).toggleClass('ie-mobile-tap');
                });
              }
            } else {
              holder.find('.carousel').addClass('carousel-non-touch-device');
            }
            // Force refresh to make sure that the layout is ok
            obj.slick('slickGoTo', 0, true);
          }

          // Bind lightbox if feed content is shown in modal
          if (typeof settings.modal !== 'undefined' && settings.modal) {
            holder.find('a').click(function onClockHolderLink() {
              $('#modal').addClass('feed-content');
            });
            VuFind.lightbox.bind(holder);
          }
        }
      })
      .fail(function loadFeedFail(response/*, textStatus, err*/) {
        var err = '<!-- Feed could not be loaded';
        if (typeof response.responseJSON !== 'undefined') {
          err += ': ' + response.responseJSON.data;
        }
        err += ' -->';
        holder.html(err);
      });
  }

  function getCarouselSettings(settings) {
    var autoplay = typeof settings.autoplay !== 'boolean' ? parseInt(settings.autoplay, 10) : 0;
    return {
      dots: settings.dots,
      swipe: !settings.vertical,
      infinite: true,
      touchThreshold: 8,
      autoplay: autoplay !== 0,
      autoplaySpeed: autoplay,
      slidesToShow: settings.slidesToShow.desktop,
      slidesToScroll: settings.scrolledItems.desktop,
      speed: calculateScrollSpeed(settings.scrolledItems.desktop, settings.scrollSpeed),
      vertical: settings.vertical,
      responsive: [
        {
          breakpoint: 1200,
          settings: {
            slidesToShow: settings.slidesToShow['desktop-small'],
            slidesToScroll: settings.scrolledItems['desktop-small'],
            speed: calculateScrollSpeed(settings.scrolledItems['desktop-small'], settings.scrollSpeed)
          }
        },
        {
          breakpoint: 992,
          settings: {
            slidesToShow: settings.slidesToShow.tablet,
            slidesToScroll: settings.scrolledItems.tablet,
            speed: calculateScrollSpeed(settings.scrolledItems.tablet, settings.scrollSpeed)
          }
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: settings.slidesToShow.mobile,
            slidesToScroll: settings.scrolledItems.mobile,
            speed: calculateScrollSpeed(settings.scrolledItems.mobile, settings.scrollSpeed)
          }
        }
      ]
    };
  }

  function initComponents() {
    $('.feed-container[data-init!="0"]').each(function loadEachFeed() {
      loadFeed($(this));
    });
  }

  var my = {
    loadFeedFromUrl: loadFeedFromUrl,
    init: function init() {
      initComponents();
    }
  };

  return my;
})();
