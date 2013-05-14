<div id="cn-container">
    <div class="cn-panel">
        <div class="cn-panel-inner">
        <h1>Edição #{$edition.numero} <small>/ {$edition.dtlanc}</small></h1>

        <div id="cn-edition-pagechoicer">
            <span class="label">-</span>
        </div>
        
        <div id="cn-edition-chapters">
            <span class="label">-</span>

            <div id="cn-edition-vote">
                Sua nota:
                {if $logged}
                    <div class="vote-loading">Votando...</div>
                    <div class="vote-wrapper"{if $expired} title="Você pode votar ou mudar seu voto, mas ele não será mais&lt;br&gt; contabilizado pois o ranking já foi definido. Seu voto aqui&lt;br&gt; será tratado como &quot;geral&quot;"{/if}>
                        <div class="vote">
                            <span class="vote-label"></span>
                            <ul>
                                <li class="vote-star vote-1"><a href="#" rel="1">1</a></li>
                                <li class="vote-star vote-2"><a href="#" rel="2">2</a></li>
                                <li class="vote-star vote-3"><a href="#" rel="3">3</a></li>
                                <li class="vote-star vote-4"><a href="#" rel="4">4</a></li>
                                <li class="vote-star vote-5"><a href="#" rel="5">5</a></li>
                            </ul>
                        </div>
                        <div class="vote" id="secondary-vote">
                            <span class="vote-label"></span>
                            <ul>
                                <li class="vote-star vote-1"><a href="#" rel="1">1</a></li>
                                <li class="vote-star vote-2"><a href="#" rel="2">2</a></li>
                                <li class="vote-star vote-3"><a href="#" rel="3">3</a></li>
                                <li class="vote-star vote-4"><a href="#" rel="4">4</a></li>
                                <li class="vote-star vote-5"><a href="#" rel="5">5</a></li>
                            </ul>
                        </div>
                    </div>
                    {if $expired}
                    <div class="error">
                        Votação encerrada.
                    </div>
                    {/if}
                {else}
                <div class="error">
                    Você precisa estar logado para votar.
                </div>
                {/if}
            </div>
        </div>
        

        <ul class="cn-options">
            <li><a href="#/page/1">Início <small>(I)</small></a></li>
            <li><a href="#" id="cn-edition-zoom">Ampliar <small>(Z)</small></a></li>
            {if $edition.id == 11 || $edition.id == 12}
            <li><a href="{$site.URL}leitor/edicoes/{$edition.ano}/{$edition.numero}/download/pdf">Download PDF</a></li>
            <li><a href="{$site.URL}leitor/edicoes/{$edition.ano}/{$edition.numero}/download/cbr">Download CBR</a></li>
            {/if}
            {if $logged}
            <li><a href="#" onclick="logout(); return false;">Deslogar</a></li>
            {else}
            <li><a href="#login-form-modal" data-toggle="modal">Login</a></li>
            {/if}
        </ul>

        <fb:like send="true" layout="button_count" width="280" show_faces="false"></fb:like>
        {if $logged}
        <div id="cn-logged">
            <div>Votando como </div>
            <img src="{if $profile.username}https://graph.facebook.com/{$profile.username}/picture{else}{$site.URL}images/layout/cn-unknown.gif{/if}" /> {$profile.name}
        </div>
        {/if}
        </div>

    </div>
    <div id="cn-edition">
    {foreach $pages as $cnpage}
        <div class="cn-page"{if $cnpage.title} data-chapter-title="{$cnpage.title}" data-chapter-subtitle="{$cnpage.cap_title}" data-chapter-key="{$cnpage.titlekey}" data-series-key="{$cnpage.seriekey}"{/if} data-votable="{$cnpage.votable}">
            <img 
                data-original="/{$cnpage.img}" 
                data-folder="{$site.URL}images/editions/{$cnpage.folder}" 
                src="{$site.URL}images/layout/none.gif" 
                alt=""/>
            {if $cnpage.html}
            <div class="special-content">{eval var=$cnpage.html}</div>
            {/if}
        </div>
    {/foreach}
    </div>
    <div class="cn-handlers">
        <a href="{$site.URL}#/page/prev" id="cn-edition-prev">&lt;</a>
        <a href="{$site.URL}#/page/next" id="cn-edition-next">&gt;</a>
    </div>
    <div class="cn-copyleft">
        <hr />
        <img src="{$site.URL}{$folderImages}layout/cn-logo-leitor.png" alt="Leitor Online - Conexão Nanquim" />
        <small>Equipe Conexão Nanquim - Alguns direitos reservados</small>
    </div>
</div>
<script>
var myVotes = {$jsonvotes}
</script>
<div id="login-form-modal" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>Login</h3>
  </div>
  <div class="modal-body">
    <div class="row-fluid">
        <div class="span7">
            <p>Comece a votar fazendo o login no site.</p> 
            <p>Se você já votou antes, basta digitar seu e-mail abaixo:</p>
            <p><input type="text" placeholder="Seu e-mail" value="" name="login-form-email" id="login-form-email" /></p>
            <div id="login-form-more">
                <p>Nunca votou? Então só informar seu nome também:</p>
                <p><input type="text" placeholder="Seu nome completo" value="" name="login-form-nome" id="login-form-nome" /></p>
            </div>
            <div id ="login-form-loading" style="display:none;">
                Aguarde...
            </div>
            <p id="login-form-buttons">
                <a href="#" onclick="loginCN(); return false;" class="btn btn-primary">Login</a>
                <a href="#" onclick="loginShowMore(); return false;" class="btn" id="login-form-nuncavotei">Nunca votei</a>
            </p>
        </div>
        <div class="span5 rside">
            <p>Ou então você votar com sua conta do Facebook.</p>
            <p>
                <a href="#" onclick="loginFacebook(); return false;" class="btn btn-facebook btn-large">
                    Login
                </a>
            </p>
        </div>
    </div>
  </div>
  <div class="modal-footer">
    <a href="#" data-dismiss="modal" class="btn">Cancelar</a>
  </div>
</div>