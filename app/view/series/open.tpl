<div class="container">
  <div class="cn-container clearfix cn-serie">
    <div style="width: 220px; height: 550px; background: url({$site.URL}images/series/{$serie.key}/arq_preview.jpg) no-repeat 0 0; float: left; margin-right:20px; border-radius: 8px;"></div>
    <div class="cn-share pull-right">
      <div class="fb-like" data-send="false" data-layout="button_count" data-show-faces="false" data-url="{$site.fullURL}series/{$serie.key}"></div>
      <a class="twitter-share-button" href="https://twitter.com/share" data-via="RDNanquim" data-url="{$site.fullURL}series/{$serie.key}" data-text="Eu curti a série {$serie.nome} da #conexaonanquim, curtam tbm galera!" data-hashtags="{$serie.key}" data-lang="pt-BR">Tweetar</a>
      <div class="g-plusone" data-size="medium" data-href="{$site.fullURL}series/{$serie.key}"></div>
    </div>
    <h1 rel="title">{$serie.nome}
      <div class="cn-rating" title="Nota geral: {$serie.rating}/5"><div class="cn-rating-inner" style="width:{$serie.rating_percent}%">Nota: {$serie.rating}/5</div></div>
    </h1>
    <h2 rel="author">por 
      {foreach $serie.autores as $autor}
        <a href="{$site.URL}autores/{$autor.id}">{$autor.nome}</a>{if !$autor@last}, {/if}
      {/foreach}
    </h2>
    
      
    {if $serie.numcapitulos}
      <a href="{$site.URL}leitor/series/{$serie.key}" class="cn-button leia">Leia completo <span class="detail">({$serie.numcapitulos} capítulos)</span></a>
    {else}
      <div class="cn-estreia">Série ainda vai estrear, fique ligado!</div>
    {/if}
    
    {if $serie.sinopse}
      <p class="cn-sinopse">{$serie.sinopse}</p>
    {else}
      <p class="cn-sinopse">O autor dessa série não escreveu uma sinopse. Se você gostou da série, você pode <a href="{$site.fullURL}series/{$serie.key}?sugerir=sinopse">sugerir uma sinopse ao autor</a>.</p>
    {/if}
  </div>
</div>