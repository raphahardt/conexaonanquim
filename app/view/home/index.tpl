<style>
html,
body {
	height: 100%;
	/* The html and body elements cannot have any padding or margin. */
}
body { 
	background:#ba0808 url({$siteURL}images/layout/bg-cn.jpg) no-repeat 50% 50%; 
	color:#fff; 
	font-family: 'Gloria Hallelujah', cursive;
}
a { color:#fbcdcd; }
a:hover { color:#fff; text-decoration:none; }

p { font-size: 1.4em; line-height:1.4em; }
h2 { font-size:3em; color:#000; margin-top:-30px; margin-bottom:25px; }	

#wrap {
	min-height: 100%;
	height: auto !important;
	height: 100%;
	/* Negative indent footer by it's height */
	margin: 0 auto -30px;
}
#push,
#footer {
	height: 30px;
	color:#000;
}
/*.cn-container { position:relative; }*/
.cn-center { text-align:center; position:absolute; top:50%; margin:0 auto; margin-top:-230px; height:460px }
.cn-center img { display:block; margin:0 auto; }
#footer p { font-size:14px; }
#footer p a { color:#4d0202; }

@media (max-width: 767px) { 
	p { font-size:1.3em !important; }
	h2 { font-size:2.1em !important; margin-top:-20px !important; }
}

@media (max-width: 480px) {
	p { font-size:0.8em !important }
	h2 { font-size:1.4em !important; margin-top:0px !important; }
}
</style>
<div id="wrap">
	<div class="container">
		<div class="span12 cn-center">
			<h1><img src="{$siteURL}images/layout/conexaonanquim.png" alt="Conexão Nanquim"/></h1>
			<h2>Muita calma nessa hora!</h2>
			<p>
				Um novo site está sendo desenvolvido, ainda tá muito cedo pra você entrar. 
				Enquanto isso você pode continuar por dentro das novidades da revista 
				<a href="http://www.digitalnanquim.com/">no nosso site atual</a>, ver as <a href="http://www.conexaonanquim.com.br/edicoes">edições lançadas</a>, ou participar do
				<a href="http://www.facebook.com/groups/conexaonanquim">nosso grupo do Facebook</a>. :)
			</p>
		</div>
	</div>
</div>
<div id="footer">
	<div class="container">
		<footer>
			<p>
			<span xmlns:dct="http://purl.org/dc/terms/" property="dct:title" style="text-weight:bold">Revista Digital Conexão Nanquim</span>, 
			um projeto da 
			<a xmlns:cc="http://creativecommons.org/ns#" href="http://www.digitalnanquim.com" property="cc:attributionName" rel="cc:attributionURL">Reação Editora</a>. 
			Licenciado com uma <a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/3.0/deed.pt">Creative Commons (BY-NC-ND) 3.0 Não Adaptada</a>.
			</p>
		</footer>
	</div>
</div>