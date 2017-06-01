<?php
// Criado por Marcos Peli
// ultima atualização 01/Junho/2017 - correçâo ref alteraçâo consulta CPF na receita e otimização de scripts
// introduzidos parametro token_cpf. Obtenção de token e captcha para consulta de CPF introduzidos no script getcaptcha.php
// getcaptcha.php agora é incluido para dentro de index.php e os captchas não são mais gerados pela bib. GD

// o objetivo dos scripts deste repositório é integrar consultas de CNPJ e CPF diretamente da receita federal
// para dentro de aplicações web que necessitem da resposta destas consultas para proseguirem, como e-comerce e afins.

// importante, CPF e DATA de NASCIM. devem ser digitados no formato ###.###.###-##  e  dd/mm/aaaa
// CNPJ devem ser digitados só NUMEROS   ###########  (sem ponto ou hifem)
// essas entradas nâo foram tratadas, pois o objetivo é apenas a implementaçâo da soluçao das consulta e testes

include("getcaptcha.php");

?>
<html>

<head>
<title>CNPJ , CPF e Captcha</title>
</head>

<body>

	<form id="receita_cnpj" name="receita_cnpj" method="post" action="processa.php">
		<p><span class="titleCats">CNPJ e Captcha</span>
			<br />
			<input name="cnpj" type="text" maxlength="14" required /> 
			<b style="color: red">CNPJ</b>
			<br />
			<img id="captcha_cnpj" src="<?php echo $imagem_cnpj; ?>" border="0">
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
			<img id="captcha_cpf" src="<?php echo $imagem_cpf; ?>" border="0">
			<br />
			<input type="text" name="captcha_cpf" minlength="6" maxlength="6" required />
			<b style="color: red">O que vê na imagem acima?</b>
			<br />
		</p>
		<p>
			<input type="hidden" name="token_cpf" id="token_cpf" value="<?php echo $token_cpf; ?>" />
			<input id="enviar" name="enviar" type="submit" value="Consultar"/>
		</p>
		<p>
			_____________________________________________________
		</p>

	</form>

</body>

</html>