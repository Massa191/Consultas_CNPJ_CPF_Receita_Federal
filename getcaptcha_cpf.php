<?php
// Criado por Marcos Peli
// Novo Script para geração de Cpatcha e Token da nova consulta CPF receita (29/Maio/2017)
// OBS. A Geração do Captcha e token agora se da por javascript, portanto é necessário echoar estes no Navegador para geração, escondendo o conteudo da receita dentro de uma tag com css "display:none"
// o objetivo dos scripts deste repositório é integrar consultas de CNPJ e CPF e NFE diretamente da receita federal
// para dentro de aplicações web que necessitem da resposta destas consultas para prosseguirem, como e-comerce e afins.

include("funcoes.php");

// inicia sessão
@session_start();

// define arquivo de cookie e url da chamada curl para geração de captcha para consulta de cpf
$cookieFile = COOKIELOCAL.'cpf_'.session_id();
$cookieFile_fopen = HTTPCOOKIELOCAL.'cpf_'.session_id();

if(!file_exists($cookieFile))
{
	$file = fopen($cookieFile, 'w');
	fclose($file);
}

// faz a chamada Curl que gera a imagem do captcha para consulta de NFE e pega todos os inputs hiddens necessários
$url = 'http://cpf.receita.fazenda.gov.br/situacao/';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($ch);
curl_close($ch);		

echo $html ;

?>