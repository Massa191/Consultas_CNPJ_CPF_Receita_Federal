<?php
// Criado por Marcos Peli
// ultima atualização Marco/2017 alterada URL para geração do captcha da consulta de CPF
// o objetivo dos scripts deste repositório é integrar consultas de CNPJ e CPF diretamente da receita federal
// para dentro de aplicações web que necessitem da resposta destas consultas para proseguirem, como e-comerce e afins.

//	tipo de consulta (cpf ou cnpj) para gerar o captcha corretamente
$tipo_consulta = $_GET['tipo_consulta'];

//	define o local onde serão guardados os cookies de sessão , path real e completo
$pasta_cookies = 'cookies/';
define('COOKIELOCAL', str_replace('\\', '/', realpath('./')).'/'.$pasta_cookies);
define('HTTPCOOKIELOCAL',$pasta_cookies);

// inicia sessão
@session_start();
	
// define os nomes dos arquivos de cookie para cada tipo de consulta
if($tipo_consulta == 'cpf')
{
	// define arquivo de cookie e url da chamada curl para geração de captcha para consulta de cpf
	$cookieFile = COOKIELOCAL.'cpf_'.session_id();
	$cookieFile_fopen = HTTPCOOKIELOCAL.'cpf_'.session_id();

	$url = 'https://www.receita.fazenda.gov.br/Aplicacoes/SSL/ATCTA/CPF/ConsultaSituacao/captcha/gerarCaptcha.asp';	// URL Alterada Marco/2017
}
else if ($tipo_consulta == 'cnpj')
{
	// define arquivo de cookie e url da chamada curl para geração de captcha para consulta de cnpj
	$cookieFile = COOKIELOCAL.'cnpj_'.session_id();
	$cookieFile_fopen = HTTPCOOKIELOCAL.'cnpj_'.session_id(); 
	$url = 'http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/captcha/gerarCaptcha.asp';	
}
else
{die("faltou parâmetro tipo_consulta");}

if(!file_exists($cookieFile))
{
	$file = fopen($cookieFile, 'w');
	fclose($file);
}

// faz a chamada Curl que gera a imagem de captcha para consulta de CPF ou CNPJ conforme o parâmetro passado por get
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);	// para consulta de CPF, necessário devido SSL (https), para CNPJ este parametro não interfere na consulta
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);	// para consulta de CPF, necessário devido SSL (https), para CNPJ este parametro não interfere na consulta
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$imgsource = curl_exec($ch);
curl_close($ch);		

// se tiver imagem , mostra
if(!empty($imgsource))
{
	$img = imagecreatefromstring($imgsource);
	header('Content-type: image/jpg');
	imagejpeg($img);
}



// --------------- aqui abaixo hack para consulta de cnpj.-----------
//	observei que a primeira consulta de cnpj retorna vazia , possivelmente deve ter alguma variavel de sessão que precisa ser iniciada antes , na página inicial da receita -- Cnpjreva_Solicitacao2.asp
//	resolvi fazendo a consulta curl abaixo , ...enviando o Session name e session id que o captcha gerou para a página Cnpjreva_Solicitacao2.asp
// isso ainda não é necessário para consulta de cpf

if ($tipo_consulta == 'cnpj')
{

	// pega os dados de sessão gerados na visualização do captcha dentro do cookie
	$file = fopen($cookieFile_fopen, 'r');
	while (!feof($file))
	{$conteudo .= fread($file, 1024);}
	fclose ($file);
	
	$explodir = explode(chr(9),$conteudo);
			
	$sessionName = trim($explodir[count($explodir)-2]);
	$sessionId = trim($explodir[count($explodir)-1]);	
	
	// constroe o parâmetro de sessão que será passado no próximo curl
	$cookie = $sessionName.'='.$sessionId;
	
	$ch = curl_init('http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/Cnpjreva_Solicitacao2.asp');
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);	// dados do arquivo de cookie
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);	// dados do arquivo de cookie
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0');
	curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$html = curl_exec($ch);
	curl_close($ch);
	
}

?>
