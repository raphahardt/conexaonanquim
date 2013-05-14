/**
 * Leitor CN
 */

// undefined is used here as the undefined global 
// variable in ECMAScript 3 and is mutable (i.e. it can 
// be changed by someone else). undefined isn't really 
// being passed in so we can ensure that its value is 
// truly undefined. In ES5, undefined can no longer be 
// modified.

// window and document are passed through as local 
// variables rather than as globals, because this (slightly) 
// quickens the resolution process and can be more 
// efficiently minified (especially when both are 
// regularly referenced in your plugin).
;
(function($, window, document, undefined) {

// objeto jquery do leitor
  $.fn.cnReader = function(option, page) {
    return this.each(function() {
      var $this = $(this),
          data = $this.data('cnreader'),
          options = $.extend({}, $.fn.cnReader.defaults, typeof option === 'object' && option);

      if (!data)
        $this.data('cnreader', (data = new cnReader(this, options)));

      if (typeof option === 'string') {
        data[option](page);
      }
      ;
    });
  };

  $.fn.cnReader.defaults = {
    url: '',
    prevButton: '#cn-edition-prev',
    nextButton: '#cn-edition-next',
    chapterButton: '#cn-edition-chapters',
    pageStatusButton: '#cn-edition-pagechoicer',
    voteButton: '#cn-edition-vote',
    zoomButton: '#cn-edition-zoom',
    page: {
      width: 1240,
      height: 1754
    },
    urls: {
      vote: null
    },
    container: document
  };

  var cnReader = function (target, options) {

    var that = this,
    d = document,
    // objetos
    $reader_wrapper,
    $reader,
    $container,
    reader_wrapper,
    reader,
    pl0, pl1, pl2, //pagers
    pr0, pr1, pr2,
    // aux objects
    $next_button,
    $prev_button,
    $chapter_button,
    $pagestatus_button,
    $zoom_button,
    $vote_button,
    // variaveis
    pages,
    pages_img = [],
    pages_img_cache = [],
    pages_html = [],
    page_width = options.page.width,
    page_height = options.page.height,
    transitionType = 'in-out',
    transitionSpeed = 300,
    ratio = page_width / page_height,
    eventNamespace = 'cnr',
    busy = false,
    busyHash = false,
    currentPage = 1,
    disabled = false,
    zoomed = false,
    resizeInterv,
    // constructor
    init = function() {
      log('Metodo: init()');

      // instancias
      reader = target;
      reader_wrapper = d.createElement('div');
      reader_wrapper.id = 'cn-edition-wrapper';
      
      if (options.container === d) {
        d.body.appendChild(reader_wrapper);
        $container = $(d.body);
      } else {
        $( options.container )[0].appendChild(reader_wrapper);
        $container = $( options.container );
      }
      reader_wrapper.appendChild(reader);

      $reader_wrapper = $(reader_wrapper);
      $reader = $(reader);

      //$reader.wrap( $reader_wrapper )

      // pega todas as paginas
      pages = $reader.children('.cn-page');

      pages.each(function() {
        var img_obj = $('img', this),
            html_obj = $('.special-content', this),
            page_obj = $(this);

        // joga os srcs as images num array
        pages_img.push({
          file: img_obj.data('original'),
          folder: img_obj.data('folder'),
          chapter: page_obj.data('chapter-title'),
          chapterSubtitle: page_obj.data('chapter-subtitle'),
          chapterKey: page_obj.data('chapter-key'),
          seriesKey: page_obj.data('series-key'),
          votable: page_obj.data('votable')
        });

        // joga htmls num array
        pages_html.push(html_obj.html());
      });

      // define os manipuladores
      $next_button = $(options.nextButton);
      $prev_button = $(options.prevButton);
      $chapter_button = $(options.chapterButton);
      $zoom_button = $(options.zoomButton);
      $pagestatus_button = $(options.pageStatusButton);
      $vote_button = $(options.voteButton);

      // limpa o interior do div para criar a revista
      $reader.empty();

      // cria os elementos necessarios pra revista
      pl0 = d.createElement('div');
      pl1 = d.createElement('div');
      pl2 = d.createElement('div');
      pr0 = d.createElement('div');
      pr1 = d.createElement('div');
      pr2 = d.createElement('div');

      // seta as propriedades das paginas
      pl0.className = 'page pl0 page-left';
      pl1.className = 'page pl1 page-left';
      pl2.className = 'page pl2 page-left';
      pr0.className = 'page pr0 page-right';
      pr1.className = 'page pr1 page-right';
      pr2.className = 'page pr2 page-right';

      // esconde as paginas que vao ficar em cima
      pl2.style.display = 'none';
      pr2.style.display = 'none';
      // esconde as paginas que vao ficar em baixo
      pl0.style.display = 'none';
      pr0.style.display = 'none';

      // adiciona todos ao node, na ordem
      reader.appendChild(pl0); //pagina da esquerda, que fica atras
      reader.appendChild(pr0); // pagina da direita que fica atras
      reader.appendChild(pl1); //pagina da esquerda, que está lendo
      reader.appendChild(pr1); // pagina da direita que está lendo
      reader.appendChild(pl2); //pagina da esquerda (que fica escondida na direita), que será a prox pagina
      reader.appendChild(pr2); // pagina da direita (que fica escondida na esquerda), que será a prox pagina

      // define a pagina inicial pelo hash
      currentPage = getHash();

      // carrega as paginas iniciais
      loadPage($(pl0), currentPage - 3);
      loadPage($(pr2), currentPage - 2);
      loadPage($(pl1), currentPage - 1);
      loadPage($(pr1), currentPage);
      loadPage($(pl2), currentPage + 1);
      loadPage($(pr0), currentPage + 2);

      // redimenciona as paginas corretamente
      refreshSizes();

      // update em tudo
      updateInfos();

      $(window).resize(function() {
        clearInterval(resizeInterv);

        resizeInterv = setTimeout(refreshSizes, 500);
      });

      // keyboard controls
      $(document).bind('keyup.' + eventNamespace, function(event) {
        if (!disabled && !busy) {
          //log('Key: '+event.keyCode)
          switch (event.keyCode) {
            case 37: // esquerda
              prev();
              break
            case 39: // direita
              next();
              break
            case 38: // pra cima
              move('up', 200);
              break
            case 40: // pra baixo
              move('down', 200);
              break
            case 90: // letra Z (zoom)
              zoom();
              break
            case 73: // letra I (voltar pro indice) PROVISORIO
              gotoPage(1);
              break
          }
        }
      });

      $(document).bind('mousewheel.' + eventNamespace, function(event, delta) {
        if (!disabled && !busy) {
          var direction = delta > 0 ? 'up' : 'down',
                  vel = Math.abs(delta);

          if (zoomed) {
            // subir e descer pagina
            move(direction, vel * 70, true);
          } else {
            // virar pagina
            if (vel > 2.5) {
              if (direction === 'up') {
                prev();
              } else {
                next();
              }
            }
          }
        }
      });

      /*$(document)
       .bind('swipeleft.'+eventNamespace, function (event) {
       if (!disabled && !busy) {
       next()
       }
       event.preventDefault()
       event.stopPropagation()
       })
       .bind('swiperight.'+eventNamespace, function (event) {
       if (!disabled && !busy) {
       prev()
       }
       event.preventDefault()
       event.stopPropagation()
       })*/
      /*.bind('vclick.'+eventNamespace, function (event) {
       if (!disabled && !busy) {
       zoom()
       }
       event.preventDefault()
       event.stopPropagation()
       })*/

      $(window).hashchange(watchHash);

      var init_y, init_x, final_x, double_tap = false, interv_tap;

      function doTouchInit(e) {
        e.preventDefault();
        var touch = e.touches[0];

        clearInterval(interv_tap);

        init_y = touch.pageY;
        init_x = final_x = touch.pageX;

      }

      function doTouch(e) {
        e.preventDefault();
        var touch = e.touches[0];

        var delta = touch.pageY - init_y,
            direction = delta > 0 ? 'up' : 'down',
            vel = Math.abs(delta);

        clearInterval(interv_tap);

        interv_tap = setTimeout(function() {
          move(direction, vel * 2, true);
        }, 20);

        init_y = touch.pageY;
        final_x = touch.pageX;
      }

      function doTouchEnd(e) {
        e.preventDefault();

        clearInterval(interv_tap);

        var delta = final_x - init_x,
            turn = delta > 0 ? 'prev' : 'next';

        delta = Math.abs(delta);

        if (delta > 100) {
          if (turn === 'next') {
            next();
          } else {
            prev();
          }
        }

      }

      document.addEventListener('touchstart', function(e) {
        doTouchInit(e);
      }, false);
      document.addEventListener('touchmove', function(e) {
        doTouch(e);
      }, false);
      document.addEventListener('touchend', function(e) {
        doTouchEnd(e);
      }, false);

      // outros binds
      if ($next_button.length) {
        $next_button
                .bind('click', function(event) {
          next();
          event.preventDefault();
          return false;
        });
        $prev_button
                .bind('click', function(event) {
          prev();
          event.preventDefault();
          return false;
        });
      }

      if ($zoom_button.length) {
        $zoom_button
        .bind('click', function(event) {
          zoom();
          event.preventDefault();
          return false;
        });
      }

      if ($vote_button.length) {
        $vote_button
        .find('.vote').each(function(i_vote) {
          var self_vote = this;

          $(this)
          .find('.vote-star')
          .hover(function() {
              //if (!$('.vote').hasClass('voted')) {
              $('.vote-star', self_vote).not(this).removeClass('active');
              $(this).prevAll().addClass('active');
              //}
            },
            function() {
              updateVote();
            }
          );

          $(this)
          .find('a')
          .click(function(event) {

            vote($(this).attr('rel'), i_vote);

            event.preventDefault();
            event.stopPropagation();
            return false;
          });
        });
      }
    },
    
    // metodos privados
    getWrapperSize = function() {
      log('Metodo: getWrapperSize()');

      var w, h, h2;

      w = $(window).width();
      h2 = $(window).height();
      h = (w / 2) / ratio;

      if (h > h2) {
        h = h2;
        w = (h * ratio) * 2;
      }

      log(' - Retornou: width: ' + w + ', height: ' + h);
      return {
        width: w,
        height: h
      };
    },
    getPagePercent = function() {
      //log('Metodo: getPagePercent()')

      var w = getWrapperSize().width / (zoomed ? 1 : 2);

      //log(' - Retornou: percent: '+(w / page_width) )
      return w / page_width;
    },
    getPageSize = function() {
      log('Metodo: getPageSize()');

      var sizes = getWrapperSize();

      if (zoomed) {
        sizes.height = sizes.width / ratio;
      } else {
        sizes.width /= 2;
      }

      log(' - Retornou: width: ' + (sizes.width) + ', height: ' + sizes.height);
      return {
        width: sizes.width,
        height: sizes.height
      };
    },
    refreshSizes = function() {
      log('Metodo: refreshSizes()');

      var ws = getWrapperSize(),
          ps = getPageSize();


      $container.css({
        width: $(window).width(),
        height: $(window).height()
      });

      $reader
        .css({width: ws.width, height: ws.height})
        // arruma o tamanho das paginas
        .find('.page, .page .divimg')
        .css({width: ps.width, height: ps.height, top: 0})
        // arruma o left das paginas a direita
        .filter('.page-right')
        .css({left: (zoomed ? 0 : ps.width), top: (zoomed ? ps.height : 0)});

      $reader
        .find('.html')
        .css({
        webkitTransform: 'scale(' + getPagePercent() + ')',
        mozTransform: 'scale(' + getPagePercent() + ')',
        transform: 'scale(' + getPagePercent() + ')',
        top: 0,
        left: 0
      });

      $reader_wrapper.
        css({width: ws.width, height: ws.height});

      //give the pages the proper scale for itens
      /*reader.find('.cn-page-specialcontent').css({
       'mozTransform': 'scale('+(that.prop.percent())+')',
       'webkitTransform': 'scale('+(that.prop.percent())+')',
       'transform': 'scale('+(that.prop.percent())+')'
       });*/
    },
    // deixa o numero da pagina sempre impar e acerta para a pagina não passar do maximo permitido
    normalizePage = function(page) {
      log('Metodo: normalizePage(' + page + ')');

      page = parseInt(page, 10);

      // se a pagina for par, a pagina desejada é a impar adjacente
      if (page % 2 === 0)
        page++;

      // se a pagina escolhida não existir, limitar
      if (page < 1)
        page = 1;
      if (page > pages.length + 1)
        page = pages.length + 1;

      log(' - Retornou: ' + page + '');
      return page;
    },
    loadPage = function(obj, page) {
      log('Metodo: loadPage(' + page + ')');

      var index = page - 1;

      // limpa o container
      obj.empty();

      // se for a pagina 0 ou a pagina final, não mostrar nada
      if (page === 0 || page === pages.length + 1) {
        obj.addClass('blank');
      } else {
        obj.removeClass('blank');
      }

      if (pages_img[ index ]) {
        var content;

        content = '<div class="divimg"><img src="' + (pages_img[ index ].folder) + 'large' + (pages_img[ index ].file) + '" />';

        if (pages_html[ index ]) {
          content += '<div class="html">' + pages_html[ index ] + '</div>';
        }
        content += '</div><div class="page-shadow"></div>';

        obj.html(content);

        //refreshSizes()
      }
    },
    resetPagers = function() {
      log('Metodo: resetPagers()');

      pl0 = $('.pl0');
      pl1 = $('.pl1');
      pl2 = $('.pl2');
      pr0 = $('.pr0');
      pr1 = $('.pr1');
      pr2 = $('.pr2');
    },
    resetPagersSizes = function() {
      log('Metodo: resetPagersSizes()');

      $([pl0, pl1, pl2, pr0, pr1, pr2]).each(function() {
        var left = 0;
        if ($(this).hasClass('page-right'))
          left = getPageSize().width;

        $(this).css({
          width: getPageSize().width,
          left: left,
          top: 0,
          boxShadow: 'none'
        }).show();
      });
    },
    rotatePagers = function(direction) {
      log('Metodo: rotatePagers()');

      if (direction === 'right') {

        pr0.addClass('pr1').removeClass('pr0');
        pr1.addClass('pr2').removeClass('pr1').hide();
        pr2.addClass('pr0').removeClass('pr2').hide();

        pl2.addClass('pl1').removeClass('pl2');
        pl0.addClass('pl2').removeClass('pl0').hide();
        pl1.addClass('pl0').removeClass('pl1').hide();

      } else if (direction === 'left') {

        pl0.addClass('pl1').removeClass('pl0');
        pl1.addClass('pl2').removeClass('pl1').hide();
        pl2.addClass('pl0').removeClass('pl2').hide();

        pr2.addClass('pr1').removeClass('pr2');
        pr0.addClass('pr2').removeClass('pr0').hide();
        pr1.addClass('pr0').removeClass('pr1').hide();
      }
    },
    gotoPage = function(to) {
      log('Metodo: gotoPage(' + to + ')');

      var callback;

      to = normalizePage(to);

      if (busy || disabled)
        return;

      resetPagers();

      // verifica pra que lado vai virar
      if (to < currentPage) {
        // vira pra esquerda (volta)

        // carrega as proximas paginas
        loadPage(pl0.show(), to - 1);
        loadPage(pr2, to);

        refreshSizes();

        busy = true;

        if (zoomed) {
          // quando estiver com zoom

          callback = function() {
            //resetPagersSizes()

            rotatePagers('left');

            // seta a pagina atual
            currentPage = to;

            // pre carrega as paginas anteriores
            loadPage(pr0, to - 2);
            loadPage(pl2, to - 3);

            // update no hash
            updateInfos();

            busy = false;
          };

          setTimeout(function() {
            pl1
            .css({
              top: 0
            })
            .transition({
              top: getWrapperSize().height
            }, transitionSpeed, transitionType);

            pl0
            .css({
              top: pl0.hasClass('blank') ? -(getPageSize().height - getWrapperSize().height) : -(getPageSize().height + getWrapperSize().height)
            });

            // flip animation
            pr2
            .show()
            .css({
              top: -getPageSize().height
            })
            .transition({
              top: -(getPageSize().height - getWrapperSize().height)

            }, transitionSpeed, transitionType, callback);

          }, 50);


        } else {

          // quando estiver sem zoom
          callback = function() {
            resetPagersSizes();

            rotatePagers('left');

            // seta a pagina atual
            currentPage = to;

            // pre carrega as paginas anteriores
            loadPage(pr0, to - 2);
            loadPage(pl2, to - 3);

            // update no hash
            updateInfos();

            busy = false;
          };

          setTimeout(function() {
            pl1
            .css({
              left: 0
            })
            .transition({
              left: getPageSize().width,
              width: 0
            }, transitionSpeed, transitionType);

            // shadow animation
            /*pr2
             .transition({ boxShadow: '50px 0 40px rgba(0,0,0, 0.4)' }, {
             duration: transitionSpeed/2, 
             easing: transitionType,
             queue: false
             })
             .transition({ boxShadow: 'none' }, {
             duration: transitionSpeed/2, 
             easing: transitionType
             })*/

            // flip animation
            pr2
            .show()
            .css({
              left: 0,
              width: 0
            })
            .transition({
              left: getPageSize().width,
              width: getPageSize().width//,
                      //boxShadow: '50px 0 40px rgba(0,0,0, 0.4)'

            }, transitionSpeed, transitionType, callback);

          }, 50);

        }

      } else if (to > currentPage) {
        // vira pra direita (avança)

        // carrega as proximas paginas
        loadPage(pr0.show(), to);
        loadPage(pl2, to - 1);

        refreshSizes();

        busy = true;

        if (zoomed) {
          // quando estiver com zoom

          callback = function() {
            //resetPagersSizes()

            rotatePagers('right');

            // seta a pagina atual
            currentPage = to;

            // precarrega as proximas paginas
            loadPage(pr2, to + 2);
            loadPage(pl0, to + 1);

            // update hash
            updateInfos();

            busy = false;
          };

          setTimeout(function() {
            pl1
            .css({
              top: -(getPageSize().height + getWrapperSize().height)
            });

            pr1
            .css({
              top: -(getPageSize().height - getWrapperSize().height)
            })
            .transition({
              top: -getPageSize().height
            }, transitionSpeed, transitionType);

            pr0
            .css({
              top: pr0.hasClass('blank') ? 0 : getPageSize().height
            });

            // flip animation
            pl2
            .show()
            .css({
              top: getWrapperSize().height
            })
            .transition({
              top: 0

            }, transitionSpeed, transitionType, callback);

          }, 50);


        } else {

          // quando estiver sem zoom

          callback = function() {
            resetPagersSizes();

            rotatePagers('right');

            // seta a pagina atual
            currentPage = to;

            // precarrega as proximas paginas
            loadPage(pr2, to + 2);
            loadPage(pl0, to + 1);

            // update hash
            updateInfos();

            busy = false;
          };

          setTimeout(function() {
            pr1
            .transition({
              width: 0
            }, transitionSpeed, transitionType);

            // shadow animation
            /*pl2
             .transition({ boxShadow: '-50px 0 40px rgba(0,0,0, 0.4)' }, {
             duration: transitionSpeed*2, 
             easing: transitionType,
             queue: false
             })
             .transition({ boxShadow: 'none' }, {
             duration: transitionSpeed*2, 
             easing: transitionType
             })*/

            // flip animation
            pl2
            //.appendTo(target)
            .show()
            .css({
              left: getWrapperSize().width,
              width: 0
            })
            .transition({
              left: 0,
              width: getPageSize().width//,
              //boxShadow: '-50px 0 40px rgba(0,0,0, 0.4)'

            }, transitionSpeed, transitionType, callback);

          }, 50);
        }

      } else {
        // faz nada
      }
      return;
    },
    watchHash = function(event) {
      if (!busyHash) {
        var hash = getHash();

        gotoPage(hash);
      }
    },
    updateHash = function() {
      busyHash = true;
      setTimeout( function () {
        if (window.location.hash != '#/page/' + currentPage) //fix bug do favicon no firefox
          window.location.hash = '/page/' + currentPage;
        busyHash = false;
      }, window.location.hash ? 10 : 5000); // fix bug do favicon no firefox
    },
    getHash = function() {
      if (window.location.hash) {
        var h = window.location.hash;

        h = h.replace('#/page/', '');

        return normalizePage(h);
      } else
        return 1;
    },
    next = function() {
      gotoPage(currentPage + 2);
    },
    prev = function() {
      gotoPage(currentPage - 2);
    },
    zoom = function() {
      log('Metodo: zoom()');

      if (busy || disabled)
        return;

      resetPagers();

      // verifica se vai aumentar ou diminuir
      if (zoomed) {
        // para diminuir
        zoomed = false;

        resetPagersSizes();

        refreshSizes();

        pl0.hide();
        pr0.hide();
        pl2.hide();
        pr2.hide();

      } else {
        // para aumentar
        zoomed = true;

        busy = true;

        pl0.hide();
        pr0.hide();
        pl2.hide();
        pr2.hide();

        pl1
        .add('.divimg', pl1)
        .css({
          left: 0
        })
        .transition({
          width: getPageSize().width,
          height: getPageSize().height
        }, transitionSpeed, transitionType)
        .find('.html')
        .transition({
          scale: getPagePercent()
        }, transitionSpeed, transitionType);

        pr1
        .transition({
          left: 0,
          top: pl1.hasClass('blank') || pr1.hasClass('blank') ? 0 : getPageSize().height,
          width: getPageSize().width,
          height: getPageSize().height
        }, transitionSpeed, transitionType)
        .find('.divimg')
        .css({
          left: 0,
          top: 0,
          width: getPageSize().width,
          height: getPageSize().height
        })
        .find('.html')
        .transition({
          scale: getPagePercent()
        }, transitionSpeed, transitionType);

        setTimeout(function() {
          busy = false;
        }, transitionSpeed + 200);

      }
    },
    move = function(direction, velocity, mousewheel) {
      log('Metodo: move(' + direction + ')');

      if (!zoomed)
        return;

      if (disabled || busy)
        return;

      if (direction === 'up') {

        busy = true;

        var pos = '+=' + velocity + 'px';

        //log('height total: '+getWrapperSize().height);
        //log('top: '+(pl1.offset().top +200)+', height:'+(pl1.height())+', '+(pr1.offset().top +200)+', height:'+(pl1.height()) )

        if (pl1.offset().top > -velocity && pl1.offset().top < 0) {
          pos = '+=' + (pl1.offset().top * -1) + 'px';

        } else if (pl1.offset().top >= 0) {
          //pos = 0
          busy = false;
          return;
        }

        if (mousewheel) {

          pl1
          .add(pr1)
          .css({
            top: pos
          });

          busy = false;

        } else {

          pl1
          .add(pr1)
          .transition({
            top: pos
          }, transitionSpeed / 2, transitionType);

          setTimeout(function() {
            busy = false;
          }, transitionSpeed);
        }


      } else if (direction === 'down') {

        busy = true;

        var pos = '-=' + velocity + 'px';

        //log('height total: '+getWrapperSize().height);
        //log('top: '+(pl1.offset().top -200)+', height:'+(pl1.height())+', '+(pr1.offset().top -200)+', height:'+(pl1.height()) )

        if (pr1.offset().top + getWrapperSize().height < velocity) {
          pos = '-=' + (pr1.offset().top + getWrapperSize().height) + 'px';

        } else if (pr1.offset().top <= (getWrapperSize().height * -1)) {
          //pos = 0
          busy = false;
          return;
        }

        if (mousewheel) {

          pl1
          .add(pr1)
          .css({
            top: pos
          });

          busy = false;

        } else {

          pl1
          .add(pr1)
          .transition({
            top: pos
          }, transitionSpeed / 2, transitionType);

          setTimeout(function() {
            busy = false;
          }, transitionSpeed);
        }
      }
    },
    updateVote = function() {
      var votable = false;
      if (pages_img[ currentPage - 1 ]) {
        votable = pages_img[ currentPage - 1 ].votable;
        if (pages_img[ currentPage - 2 ]) {
          votable = votable || pages_img[ currentPage - 2 ].votable;
        }
      }
      if (!votable) {
        $vote_button.hide();
      } else {
        $vote_button.show();

        try {
          $('.vote', $vote_button).each(function(i_vote) {
            var self_vote = this,
                page_vote = currentPage - 2 + i_vote;

            // se for o secondary
            if (i_vote > 0 && pages_img[ page_vote ].votable) {
              // se for diferente da pagina anterior, mostrar
              $(self_vote).hide();
              if (pages_img[ page_vote - 1 ].chapterKey !== pages_img[ page_vote ].chapterKey) {
                $(self_vote).show();
                $('.vote-label', $vote_button).show();
              }
            } else {
              $('.vote-label', $vote_button).hide();

              if (!pages_img[ page_vote ].votable) {
                $(self_vote).hide();
              } else {
                $(self_vote).show();
              }
            }

            $('.vote-label', self_vote).text('Para ' + pages_img[ page_vote ].chapter);

            $('.vote-star', self_vote).removeClass('active');
            if (myVotes[ pages_img[ page_vote ].chapterKey ]) {
              $(self_vote).addClass('voted');

              $('.vote-' + myVotes[ pages_img[ page_vote ].chapterKey ], self_vote)
                .addClass('active')
                .prevAll()
                .addClass('active');

            } else {
              $(self_vote).removeClass('voted');
            }
          });

        } catch (e) {

        }
      }
    },
    updateInfos = function() {
      updateHash();
      
      log('Metodo: updateInfos()');

      var pageShow = '',
              chapterShow = '',
              subtitleShow = '';

      if (pages_img[ currentPage - 1 ]) {
        chapterShow = pages_img[ currentPage - 1 ].chapter;
        subtitleShow = pages_img[ currentPage - 1 ].chapterSubtitle;
        if (pages_img[ currentPage - 2 ] && pages_img[ currentPage - 1 ].chapter !== pages_img[ currentPage - 2 ].chapter) {
          chapterShow = pages_img[ currentPage - 2 ].chapter + ' / ' + chapterShow;
          subtitleShow = '&bull; ' + pages_img[ currentPage - 2 ].chapterSubtitle + ' <br>&bull; ' + subtitleShow;
        }
      }

      if (pages_img[ currentPage - 1 ]) {
        $chapter_button.find('.label').text(chapterShow).data('subtitle', subtitleShow);
      }


      pageShow = currentPage;
      if (currentPage > 1 && currentPage < pages.length + 1) {
        pageShow = (currentPage - 1) + '-' + pageShow;
      } else if (currentPage >= pages.length + 1) {
        pageShow = pages.length;
      }

      $pagestatus_button.find('.label').text(pageShow);

      updateVote();
    },
    vote = function(vote, odd) {
      var vote_page = currentPage - 1 - (odd ? 0 : 1);
      if (pages_img[ vote_page ]) {

        $('.vote-loading').addClass('showing');
        $('.vote-wrapper', $vote_button).hide();

        // voto via ajax
        $.ajax({
          type: "POST",
          url: options.url + 'vote/',
          data: {
            capt: pages_img[ vote_page ].chapterKey,
            serie: pages_img[ vote_page ].seriesKey,
            grade: vote
          },
          success: function(d) {
            $('.vote-loading').removeClass('showing');
            $('.vote-wrapper', $vote_button).show();

            if (d != '1') {
              alert(d);

            } else {
              var chapter = pages_img[ vote_page ].chapterKey;

              myVotes[ chapter ] = vote;

              $('.vote').addClass('voted');
              $('.vote-' + vote).addClass('active');

              //$.cookie('votes['+unescape(chapter)+']', vote)
            }

            updateVote();
          }
        });
      }
    },
    // log
    log = function(html) {
      console.log(html);
    };
    
    // construct
    init();

    // metodos e propriedades da api    
    this.options = options;
    this.element = target;
    
    this._prev = prev;
    this._next = next;
    this._zoom = zoom;
    this._gotoPage = gotoPage;
    
    // permite chain
    return this;
  };
  
  cnReader.prototype.constructor = cnReader;
  cnReader.prototype.prev = function () {
    this._prev();
    return this;
  };
  cnReader.prototype.next = function () {
    this._next();
    return this;
  };
  cnReader.prototype.zoom = function () {
    this._zoom();
    return this;
  };
  cnReader.prototype.gotoPage = function (i) {
    this._gotoPage(i);
    return this;
  };

})(jQuery, window, document);