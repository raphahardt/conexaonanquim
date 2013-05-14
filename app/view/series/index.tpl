<div class="container">
  <div>
    <h1>Séries</h1>
    <ul class="cn-series">
    {foreach $series as $serie}
      <li>
        <a href="{$site.URL}series/{$serie.key}"><img src="{$site.URL}images/series/{$serie.key}/arq_thumb.jpg" /></a>
        <a class="cn-serie-title" href="{$site.URL}series/{$serie.key}">{$serie.nome}</a> 
        <div class="cn-author">por {foreach $serie.autores as $autor}
          <a href="{$site.URL}autores/{$autor.id}">{$autor.nome}</a>{if !$autor@last}, {/if}
        {/foreach}</div>
        {if $serie.tipo == 0}
          <div class="cn-capitulos">Capítulos: {$serie.numcapitulos}</div>
        {else}
          <div class="cn-capitulos">{$serie.tipo_nome}</div>
        {/if}
        <div class="cn-rating" title="Nota geral: {$serie.rating}/5"><div class="cn-rating-inner" style="width:{$serie.rating_percent}%">Nota: {$serie.rating}/5</div></div></li>
    {/foreach}
    </ul>
  </div>
</div>