<footer class="cn-footer">
  <div class="container">
    <div class="row">
      <div class="span4">
        <nav>
          <h3>Mais informações:</h3>
          <ul>
            <li><a href="{$site.URL}a-revista">Quem somos</a></li>
            <li><a href="{$site.URL}nany-e-kim">Conheça Nany &amp; Kim</a></li>
            <li><a href="{$site.URL}anuncie">Anuncie conosco</a></li>
            <li><a href="{$site.URL}trabalhe">Trabalhe conosco</a></li>
            <li><a href="{$site.URL}publique">Publique sua história</a></li>
            <li><a href="{$site.URL}politica-privacidade">Política de privacidade</a></li>
          </ul>
        </nav>
      </div>
      <div class="span4">
        <nav>
          <h3>Mais informações:</h3>
          <ul>
            <li><a href="{$site.URL}a-revista">Quem somos</a></li>
            <li><a href="{$site.URL}anuncie">Anuncie conosco</a></li>
            <li><a href="{$site.URL}publique">Publique sua história</a></li>
            <li><a href="{$site.URL}politica-privacidade">Política de privacidade</a></li>
          </ul>
        </nav>
      </div>
      <div class="span4">
        <div class="row-fluid">
          <div class="span4">
            <a href="https://www.facebook.com/ConexaoNanquim"><i class="icon-facebook-sign"></i> Facebook</a>
          </div>
          <div class="span4">
            <a href="https://twitter.com/RDNanquim"><i class="icon-twitter-sign"></i> Twitter</a>
          </div>
          <div class="span4">
            <a href="https://plus.google.com/117322227454132402626" rel="publisher"><i class="icon-google-plus-sign"></i> Google+</a>
          </div>
        </div>
        <p class="cn-copyleft">
          <img src="{$site.fullURL}images/layout/cn-license.jpg" /><br />
          Conexão Nanquim, Nany e Kim e Reação Editora são marcas pertencentes a <a href="{$site.fullURL}a-revista">Reação Editora</a> sob a licença da Creative Commons, versão <a href="http://creativecommons.org/licenses/by-nc-sa/3.0/br/">(BY-NC-SA) 3.0 Não Adaptada</a>. 
        </p>
      </div>
    </div>
  </div>
</footer>
    <!-- Ta aí o que faz o negocio todo funcionar.. -->
    <script type="text/javascript" src="{$site.fullURL}min/?g=core"></script>
    <script type="text/javascript">
      window.___gcfg = { lang: 'pt-BR'};

      (function() {
        var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
        po.src = 'https://apis.google.com/js/plusone.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
      })();
    </script>
    <script type="text/javascript">
      /*window.fbAsyncInit = function() {
        FB.init({
          appId: '{$site.facebookID}', 
          cookie: true, 
          xfbml: true,
          oauth: true
        });
       /* FB.Event.subscribe('auth.login', function(response) {
          window.location.reload();
        });
        FB.Event.subscribe('auth.logout', function(response) {
          window.location.reload();
        }); * /
      };*/
    </script>
    {if $leitor}
    <script type="text/javascript" src="{$site.fullURL}min/?g=reader_dependencies"></script>
    <script type="text/javascript" src="{$site.fullURL}min/?g=reader"></script>
    <script>
        $(function () {
            $('#cn-edition').cnReader({
                url: '{$site.URL}leitor/edicoes/{$edition.ano}/{$edition.numero}/',
                user: {
                  nome: '{$profile.name}',
                  email: '{$profile.email}',
                  url: '{$profile.username}'
                },
                container: '#cn-container'
            });
        });

        $('#login-form-modal').on('show', function () {
          $('#login-form-more').hide()
          $('#login-form-nuncavotei').show()
        })
        $('.vote-wrapper').tooltip({ placement: 'right', animation:false, html:true });
        $('#cn-edition-chapters .label').tooltip({ placement: 'right', animation:false, html: true, title: function () { return $(this).data('subtitle') } });

        function loginShowMore() {
          $('#login-form-more').slideDown()
          $('#login-form-nuncavotei').hide()
        }

        function loginCN() {
          var email_val = $('#login-form-email').val(),
              nome_val = $('#login-form-nome').val()

          $('#login-form-loading').slideDown()
          $('#login-form-buttons').hide()

          // voto via ajax
          $.ajax({
              type: "POST",
              url: '{$site.URL}leitor/login/',
              data: { 
                  email: email_val, 
                  nome: nome_val
              },
              success: function (d) {
                $('#login-form-loading').stop().slideUp()
                $('#login-form-buttons').show()

                if (d === 'OK') {
                  window.location.reload()
                } else if (d === 'NAOEXISTE') {
                  loginShowMore()
                } else if (d === 'EMAILINVALIDO') {
                  alert('E-mail inválido!')
                  $('#login-form-email').focus()
                } else {
                  alert(d)
                }
              }
          });
        }

        function loginFacebook() {
            FB.login(function(response) {
                 if (response.authResponse) {
                     window.location.reload();
                 } else {
                     //console.log('User cancelled login or did not fully authorize.');
                 }
             }, { scope: 'email' });
        }
        function logout() {
          FB.logout(function(response) {
            window.location.href = '{$site.URL}leitor/logout/?edicao={$edition.id}';
          });
        }
          /*window.fbAsyncInit = function() {
            FB.init({
              appId: '{$facebookAppId}', 
              cookie: true, 
              xfbml: true,
              oauth: true
            });
           /* FB.Event.subscribe('auth.login', function(response) {
              window.location.reload();
            });
            FB.Event.subscribe('auth.logout', function(response) {
              window.location.reload();
            });* /
          };
          (function() {
            var e = document.createElement('script'); e.async = true;
            e.src = document.location.protocol +
              '//connect.facebook.net/pt_BR/all.js';
            document.getElementById('fb-root').appendChild(e);
          }());*/
    </script>
    {/if}
</body>
</html>