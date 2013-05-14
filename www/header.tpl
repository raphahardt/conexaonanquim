<!DOCTYPE html>
<!--[if lt IE 7 ]> <html class="no-js ie6" lang="pt-BR"> <![endif]-->
<!--[if IE 7 ]>    <html class="no-js ie7" lang="pt-BR"> <![endif]-->
<!--[if IE 8 ]>    <html class="no-js ie8" lang="pt-BR"> <![endif]-->
<!--[if IE 9 ]>    <html class="no-js ie9" lang="pt-BR"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]>--> <html class="no-js no-ie" lang="pt-BR"> <!--<![endif]-->
  <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# conexaonanquim: http://ogp.me/ns/fb/conexaonanquim#">
    <meta charset="utf-8">
    <title>{if $page.title}{$page.title} - {/if}{$site.title}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      
    <meta name="author" content="{$site.owner}" />
    <meta name="copyright" content="{$site.copyright}" />
    <meta name="Keywords" content="{$site.keywords}" />
    <meta name="description" content="{$site.description}" />

    <meta property="fb:app_id" content="123292314484308" /> 
    {if $leitor}
    <meta property="og:title" content="Conexão Nanquim Edição #{$edition.numero}"/>
    <meta property="og:type" content="object"/>
    <meta property="og:url" content="{$site.fullURL}leitor/edicoes/{$edition.ano}/{$edition.numero}/"/>
    <meta property="og:image" content="{$site.fullURL}images/editions/{$edition.folder}capa.jpg"/>
    <meta property="og:site_name" content="{$site.title}"/>
    <meta property="og:description" content="Edição número {$edition.numero}"/>
    {elseif $serie}
    <meta property="og:type"   content="conexaonanquim:series" /> 
    <meta property="og:url"    content="{$site.fullURL}series/{$serie.key}" /> 
    <meta property="og:title"  content="{$serie.nome} - {$site.title}" /> 
    <meta property="og:image"  content="https://fbstatic-a.akamaihd.net/images/devsite/attachment_blank.png" />
    <meta property="og:description" content="{$serie.sinopse}"/>
    <meta property="conexaonanquim:rating" content="{$serie.rating}" /> 
    <meta property="conexaonanquim:authors" content="De: {$serie.autores}" /> 
    {else}
    <meta property="og:title" content="{if $page.title}{$page.title} - {/if}{$site.title}"/>
    <meta property="og:type" content="object"/>
    <meta property="og:url" content="{if $page.fullURL}{$page.fullURL}{else}{$site.fullURL}{/if}"/>
    <meta property="og:image" content="{$site.fullURL}images/icones/cn-icon300.png"/>
    <meta property="og:site_name" content="{$site.title}"/>
    <meta property="og:description" content="{if $page.description}{$page.description}{else}{$site.description}{/if}"/>
    {/if}
    
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="{$site.URL}images/icones/cn-icon144.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{$site.URL}images/icones/cn-icon114.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{$site.URL}images/icones/cn-icon72.png">
    <link rel="apple-touch-icon-precomposed" href="{$site.URL}images/icones/cn-icon57.png">
    <link rel="shortcut icon" href="{$site.URL}favicon.ico">

    {*<link href='http://fonts.googleapis.com/css?family=Patrick+Hand|Crafty+Girls|Patrick+Hand+SC|Indie+Flower|Handlee|Coming+Soon|Architects+Daughter|Amatic+SC:400,700' rel='stylesheet' type='text/css'>*}
    <link href='http://fonts.googleapis.com/css?family=Coming+Soon|Architects+Daughter' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="{$site.fullURL}www/css/font-awesome.css" type="text/css">
    <link rel="stylesheet" href="{$site.fullURL}min/?g=styles" type="text/css">
    <link rel="stylesheet" href="{$site.fullURL}www/css/main.css" type="text/css">
    
    <script type="text/javascript" src="{$site.fullURL}min/?g=essentials"></script>

    <!-- Analytics -->
    <script type="text/javascript">

      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-37321398-1']);
      _gaq.push(['_setDomainName', 'conexaonanquim.com.br']);
      _gaq.push(['_setAllowLinker', true]);
      _gaq.push(['_trackPageview']);

      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' === document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();

    </script>
  </head>
  <body>
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/pt_BR/all.js#xfbml=1&appId={$site.facebookID}";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
    <script>!function(d,s,id){ var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){ js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
    <header class="cn-header">
      <nav class="cn-menu">
        <div class="container">
          <ul>
            <li><a href="{$site.fullURL}a-revista">A Revista</a></li>
            <li><a href="{$site.fullURL}edicoes">Edições</a></li>
            <li><a href="{$site.fullURL}series">As Séries</a></li>
            <li><a href="{$site.fullURL}publique">Publique!</a></li>
            <li><a href="{$site.fullURL}contato">Contato</a></li>
          </ul>
        </div>
      </nav>
      <div class="container cn-headerbg cn-headerbg1">
        <hgroup>
          <h1><a href="{$site.fullURL}">Conexão Nanquim</a></h1>
          <h2>A maior revista digital do país</h2>
        </hgroup>
      </div>
    </header>
    {if $breadcrumb}
      <div class="container">
        <ul class="breadcrumb">
        {foreach $breadcrumb as $bread}
          {if $bread@last}
            <li class="active">{$bread.title}</li>
          {else}
            <li><a href="{$site.fullURL}{$bread.url}">{$bread.title}</a> <span class="divider">/</span></li>
          {/if}
        {/foreach}
        </ul>
      </div>
    {/if}