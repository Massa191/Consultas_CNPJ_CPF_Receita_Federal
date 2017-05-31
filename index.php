<?php
// Criado por Marcos Peli
// ultima atualização 30/05/2017 - correçâo ref alteraçâo consulta CPF na receita
// introduzidos parametro token_cpf. Introduzidos javascripts para espelhamento de captcha e token gerados atraves de javascript da receita
// o objetivo dos scripts deste repositório é integrar consultas de CNPJ e CPF diretamente da receita federal
// para dentro de aplicações web que necessitem da resposta destas consultas para proseguirem, como e-comerce e afins.

// importante, CPF e DATA de NASCIM. devem ser digitados no formato ###.###.###-##  e  dd/mm/aaaa
// CNPJ devem ser digitados só NUMEROS   ###########  (sem ponto ou hifem)
// essas entradas nâo foram tratadas, pois o objetivo é apenas a implementaçâo da soluçao das consulta e testes

// inicia sessão
@session_start();
?>
<html>

<head>
<title>CNPJ , CPF e Captcha</title>

<script>
// função espelha(), a cada 200 ms (200 milisegundos) verifica se a imagem de captcha da receita foi gerada pelo script da receita
function espelha()
{intervalos = setInterval("espelha_captcha_receita()",200);}

// espelha os dados gerados nos scripts da receita (Token e captcha) somente após sua geração
function espelha_captcha_receita()
{
	// Para evitar erros no script de "elemento não definido" (quando o script da receita ainda não o criou)
	if (typeof document.getElementById('img_captcha_serpro_gov_br') != 'undefined') 
	{
		// somente pega os dados e os espelha quando a imagem não for vazia, além de clear interval. Obs. o data:image/png abaixo no if de comparação é de uma imagem de captcha VAZIO da receita
		if( document.getElementById('img_captcha_serpro_gov_br').src != 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAALQAAAAyCAYAAAD1JPH3AAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAAOwwAADsMBx2+oZAAAAAd0SU1FB98HDhIGOWu+9+kAAAB3SURBVHja7dIBDQAACMMwwL/n4wNaCcs6SQqOGAkwNBgaDA2GxtBgaDA0GBoMjaHB0GBoMDQYGkODocHQYGgwNIYGQ4OhwdBgaAwNhgZDg6HB0BgaDA2GBkODoTE0GBoMDYbG0GBoMDQYGgyNocHQYGgwNBiabxY7GwRg7rtJrAAAAABJRU5ErkJggg==')
		{
			// espelha a imagem para dentro da nossa tag <img id="captcha_cpf"
			document.getElementById('captcha_cpf').src = document.getElementById('img_captcha_serpro_gov_br').src;
			// espelha o token para dentro da tag <input id="token_cpf"
			document.getElementById('token_cpf').value = document.getElementById('txtToken_captcha_serpro_gov_br').value;
			//	para de executar função a cada 200 ms (200 milisegundos)
			clearInterval(intervalos);
		}
	}
}
</script>

</head>

<body onLoad="espelha()">
        
	<!-- Nesta div id="esconde" ficarão escondidas a HTML e Javascripts da receita -->
    <div id="esconde" style="display:none" >  
		<?php include("getcaptcha_cpf.php");?>
	</div> 

	<form id="receita_cnpj" name="receita_cnpj" method="post" action="processa.php">
		<p><span class="titleCats">CNPJ e Captcha</span>
			<br />
			<input name="cnpj" type="text" maxlength="14" required /> 
			<b style="color: red">CNPJ</b>
			<br />
			<img id="captcha_cnpj" src="getcaptcha.php?tipo_consulta=cnpj" border="0">
			<br />
			<input name="captcha_cnpj" type="text" maxlength="6" required />
			<b style="color: red">O que vê na imagem acima?</b>
			<br />
		</p>
		<p>
			<input id="enviar" name="enviar" type="submit" value="Consultar"/>
		</p>
		<p>
			_____________________________________________________
		</p>
	</form>
        

	<form id="receita_cpf" name="receita_cpf" method="post" action="processa.php">
		<p><span class="titleCats">CPF e Captcha</span>
			<br />
			<input type="text" name="cpf" maxlength="14" minlength="14" required /> 
			<b style="color: red">CPF xxx.xxx.xxx-xx</b>
			<br />
			<input type="text" name="txtDataNascimento" maxlength="10" minlength="10" required /> 
			<b style="color: red">Data Nascim. dd/mm/aaaa</b>
			<br />                           
			<img id="captcha_cpf" src="" border="0">
			<br />
			<input type="text" name="captcha_cpf" minlength="6" maxlength="6" required />
			<b style="color: red">O que vê na imagem acima?</b>
			<br />
		</p>
		<p>
			<input type="hidden" name="token_cpf" id="token_cpf" value="" />
			<input id="enviar" name="enviar" type="submit" value="Consultar"/>
		</p>
		<p>
			_____________________________________________________
		</p>

	</form>

</body>

</html>