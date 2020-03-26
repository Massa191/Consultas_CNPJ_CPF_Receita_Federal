<?php
// Criado por Marcos Peli
// ultima atualização 26/03/2020 - Scripts alterados para utilização do captcha sonoro, unica opção após a atualização da receita com recaptcha do google
// o objetivo dos scripts deste repositório é integrar consultas de CNPJ e CPF diretamente da receita federal
// para dentro de aplicações web que necessitem da resposta destas consultas para proseguirem, como e-comerce e afins.

// inicia sessão
@session_start();

//	define o local onde serão guardados os cookies de sessão , path real e completo
$pasta_cookies = 'cookies/';
define('COOKIELOCAL', str_replace('\\', '/', realpath('./')).'/'.$pasta_cookies);

// Headers comuns em todas as chamadas CURL, com exceçao do Índice [0], que muda para CPF e CNPJ
$headers = array(
	0 => '',	// aqui vai o HOST da consulta conforme a necessidade (CPF ou CNPJ)
	1 => 'User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:53.0) Gecko/20100101 Firefox/53.0',
	2 => 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
	3 => 'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
	4 => 'Connection: keep-alive',
	5 => 'Upgrade-Insecure-Requests: 1'	
);	

// urls para obtenção dos dados
$url['cpf'] = 'https://servicos.receita.fazenda.gov.br/Servicos/CPF/ConsultaSituacao/ConsultaPublicaSonoro.asp';
$url_captcha['cpf'] = 'https://servicos.receita.fazenda.gov.br/Servicos/CPF/ConsultaSituacao/ConsultaPublicaSonoro.asp';
$host['cpf'] =  'servicos.receita.fazenda.gov.br';

$url['cnpj'] = 'http://servicos.receita.fazenda.gov.br/Servicos/cnpjreva/Cnpjreva_Solicitacao_CS.asp';
$url_captcha['cnpj'] = 'http://servicos.receita.fazenda.gov.br/Servicos/cnpjreva/captcha/gerarCaptcha.asp';
$host['cnpj'] = 'servicos.receita.fazenda.gov.br';

// percorre os arrays fazendo as chamadas de CNPJ e CPF: $key é o tipo de chamada
foreach ($url as $key => $value)
{
	// define o hosts a ser usado no header da chamada curl conforme $key
	$headers[0] = $host[$key];
	
	// define o nome do arquivo de cookie a ser usado para cada chamada conforme $key
	$cookieFile = COOKIELOCAL.$key.'_'.session_id();
	
	// cria o arquivo se ele não existe
	if(!file_exists($cookieFile))
	{
		$file = fopen($cookieFile, 'w');
		fclose($file);
	}
	else
	{
		// pega os dados de sessão gerados na visualização do captcha dentro do cookie
		$file = fopen($cookieFile, 'r');
		while (!feof($file))
		{$conteudo .= fread($file, 1024);}
		fclose ($file);
		
		$linha = explode("\n",$conteudo);
		
		// monta o cookie com os dados da sessão
		for($contador = 4; $contador < count($linha)-1; $contador++)
		{
			$explodir = explode(chr(9),$linha[$contador]);
			$cookie[$key] .= trim($explodir[count($explodir)-2])."=".trim($explodir[count($explodir)-1])."; ";
		}
		
		// acerta o cookie a ser enviado com os dados da sessão
		$cookie[$key] = substr($cookie[$key],0,-2);
	}
	
	$ch = curl_init($value);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);	// para consulta de CPF, necessário devido SSL (https), para CNPJ este parametro não interfere na consulta
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);	// para consulta de CPF, necessário devido SSL (https), para CNPJ este parametro não interfere na consulta
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);

	// trata os resultados da consulta curl

	if($key == 'cnpj')
	{
		// pega os dados de sessão gerados nas primeiras chamadas e que estão dentro do cookie
		$file = fopen($cookieFile, 'r');
		while (!feof($file))
		{$conteudo .= fread($file, 1024);}
		fclose ($file);
		
		$linha = explode("\n",$conteudo);
		
		// monta o cookie com os dados da sessão
		for($contador = 4; $contador < count($linha)-1; $contador++)
		{
			$explodir = explode(chr(9),$linha[$contador]);
			$cookie[$key] .= trim($explodir[count($explodir)-2])."=".trim($explodir[count($explodir)-1])."; ";
		}
		
		// acerta o cookie a ser enviado com os dados da sessão
		$cookie[$key] = substr($cookie[$key],0,-2);
		
		// faz segunda chamada para pegar o captcha
		$ch = curl_init($url_captcha[$key]);
		
		// continua setando parâmetros da chamada curl
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);		// headers da chamada 
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);	// dados do arquivo de cookie
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);	// dados do arquivo de cookie
		curl_setopt($ch, CURLOPT_COOKIE, $cookie[$key]);	// cookie com os dados da sessão
		curl_setopt($ch, CURLOPT_REFERER, $value);			// refer = url da chamada anterior
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);	// para consulta de CPF, necessário devido SSL (https), para CNPJ este parametro não interfere na consulta
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);	// para consulta de CPF, necessário devido SSL (https), para CNPJ este parametro não interfere na consulta
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);

	}

	// extrai resultados conforme $key
	if($key == 'cnpj')
	{$imagem_cnpj = 'data:image/png;base64,'.base64_encode($result);}
	else if($key == 'cpf')
	{

		// Pega Imagem Captcha
		$doc = new DOMDocument();
		@$doc->loadHTML($result);

		$tags = $doc->getElementsByTagName('img');
		$count = 0;
		foreach ($tags as $tag)
		{
			$count++;
				
			if($tag->getAttribute('id') == "imgCaptcha")
			{$imagem_cpf = $tag->getAttribute('src');}

		}

	}

}
?>