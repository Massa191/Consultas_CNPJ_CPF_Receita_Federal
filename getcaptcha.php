<?php
// Criado por Marcos Peli
// ultima atualização 01/Junho/2017 Geração dos captchas CNPJ e CPF (nova versão CPF, com captcha e token apartir de payload)
// getcaptcha.php agora não utiliza mais a biblioteca GD para geração de captchas e é incluido em index.php

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
	3 => 'Accept-Language: pt-BR,pt;q=0.8,en-US;q=0.5,en;q=0.3',
	4 => 'Connection: keep-alive',
	5 => 'Upgrade-Insecure-Requests: 1'	
);	

// urls para obtenção dos dados
$url['cnpj'] = 'http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/Cnpjreva_Solicitacao2.asp';
$url_captcha['cnpj'] = 'http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/captcha/gerarCaptcha.asp';
$host['cnpj'] = 'Host: www.receita.fazenda.gov.br';

$url['cpf'] = 'http://cpf.receita.fazenda.gov.br/situacao/';
$url_captcha['cpf'] = 'http://captcha2.servicoscorporativos.serpro.gov.br/captcha/1.0.0/imagem';
$host['cpf'] =  'Host: cpf.receita.fazenda.gov.br';

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
	
		$explodir = explode(chr(9),$conteudo);
			
		$sessionName = trim($explodir[count($explodir)-2]);
		$sessionId = trim($explodir[count($explodir)-1]);	
	
		// constroe o parâmetro de sessão que será passado no próximo curl
		$cookie = $sessionName.'='.$sessionId;
	}
	
	$ch = curl_init($value);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);		

	// trata os resultados da consulta curl
	if(!empty($result))
	{
		// pega os dados de sessão gerados nas primeiras chamadas e que estão dentro do cookie
		$file = fopen($cookieFile, 'r');
		while (!feof($file))
		{$conteudo .= fread($file, 1024);}
		fclose ($file);
		
		$explodir = explode(chr(9),$conteudo);
				
		$sessionName = trim($explodir[count($explodir)-2]);
		$sessionId = trim($explodir[count($explodir)-1]);	
		
		// constroe o parâmetro de sessão que será passado no próximo curl
		$cookie = $sessionName.'='.$sessionId;
		
		// faz segunda chamada para pegar o captcha
		$ch = curl_init($url_captcha[$key]);
		// se for para pegar o captcha e token da consulta CPF, é necessário passar payload com metodo post
		if($key == 'cpf')
		{
			// pega payload dentro da html, e posta
			$corte_inicial = 'data-clienteid="';
			$corte_final = '"></div>';
			$payload = str_replace($corte_inicial,'',str_replace(strstr(strstr($result,$corte_inicial),$corte_final),'',strstr($result,$corte_inicial)));

			curl_setopt($ch, CURLOPT_POST, true);				// seta metodo POST para envio de payload
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);		// aqui vai o payload para obter token e captcha da consulta CPF
		}
		// continua setando parâmetros da chamada curl
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);		// headers da chamada 
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);	// dados do arquivo de cookie
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);	// dados do arquivo de cookie
		curl_setopt($ch, CURLOPT_COOKIE, $cookie);			// cookie com os dados da sessão
		curl_setopt($ch, CURLOPT_REFERER, $value);			// refer = url da chamada anterior
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		
		// extrai resultados conforme $key
		if($key == 'cnpj')
		{$imagem_cnpj = 'data:image/png;base64,'.base64_encode($result);}
		else if($key == 'cpf')
		{
			// pega token e captcha
			$token_captcha = explode('@', $result);
			
			$token_cpf = $token_captcha[0];
			$imagem_cpf = 'data:image/png;base64,'.$token_captcha[1];	// esta imagem do captcha de CPF já está encodada base 64
		}
			
	}

}

?>