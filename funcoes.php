<?php
// Criado por Marcos Peli
// ultima atualização 29/Maio/2017, Consulta CPF. Novo array para extrair dados, novo link de consulta, novos parametros passados para consulta CPF na receita
// novo link para consulta de CPF sem https, novo referer, etc...
// o objetivo dos scripts deste repositório é integrar consultas de CNPJ e CPF diretamente da receita federal
// para dentro de aplicações web que necessitem da resposta destas consultas para proseguirem, como e-comerce e afins.

// define caminho absoluto e relativo para arquivo cookie
$pasta_cookies = 'cookies/';
define('COOKIELOCAL', str_replace('\\', '/', realpath('./')).'/'.$pasta_cookies);
define('HTTPCOOKIELOCAL',$pasta_cookies);

// inicia sessão
@session_start();

// função para pegar o que interessa
function pega_o_que_interessa($inicio,$fim,$total)
{
	$interesse = str_replace($inicio,'',str_replace(strstr(strstr($total,$inicio),$fim),'',strstr($total,$inicio)));
	return($interesse);
}


// função para pegar a resposta html da consulta pelo CPF na página da receita
function getHtmlCNPJ($cnpj, $captcha)
{
    $cookieFile = COOKIELOCAL.'cnpj_'.session_id();
	$cookieFile_fopen = HTTPCOOKIELOCAL.'cnpj_'.session_id();
    if(!file_exists($cookieFile))
    {
        return false;      
    }
	else
	{
		// pega os dados de sessão gerados na visualização do captcha dentro do cookie
		$file = fopen($cookieFile_fopen, 'r');
		while (!feof($file))
		{$conteudo .= fread($file, 1024);}
		fclose ($file);
		
		$explodir = explode(chr(9),$conteudo);
		
		$sessionName = trim($explodir[count($explodir)-2]);
		$sessionId = trim($explodir[count($explodir)-1]);
		
		// se não tem falg	1 no cookie então acrescenta
		if(!strstr($conteudo,'flag	1'))
		{
			// linha que deve ser inserida no cookie antes da consulta cnpj
			// observações argumentos separados por tab (chr(9)) e new line no final e inicio da linha (chr(10))
			// substitui dois chr(10) padrão do cookie para separar cabecario do conteudo , adicionando o conteudo $linha , que tb inicia com dois chr(10)
			$linha = chr(10).chr(10).'www.receita.fazenda.gov.br	FALSE	/pessoajuridica/cnpj/cnpjreva/	FALSE	0	flag	1'.chr(10);
			// novo cookie com o flag=1 dentro dele , antes da linha de sessionname e sessionid
			$novo_cookie = str_replace(chr(10).chr(10),$linha,$conteudo);
			
			// apaga o cookie antigo
			unlink($cookieFile);
			
			// cria o novo cookie , com a linha flag=1 inserida
			$file = fopen($cookieFile, 'w');
			fwrite($file, $novo_cookie);
			fclose($file);
		}
		
		// constroe o parâmetro de sessão que será passado no próximo curl
		$cookie = $sessionName.'='.$sessionId.';flag=1';	
	}
	
	// dados que serão submetidos a consulta por post
    $post = array
    (
		'submit1'						=> 'Consultar',
		'origem'						=> 'comprovante',
		'cnpj' 							=> $cnpj, 
		'txtTexto_captcha_serpro_gov_br'=> $captcha,
		'search_type'					=> 'cnpj'
		
    );
    
	$post = http_build_query($post, NULL, '&');
	
    $ch = curl_init('http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/valida.asp');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);		// aqui estão os campos de formulário
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);	// dados do arquivo de cookie
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);	// dados do arquivo de cookie
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0');
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);	    // dados de sessão e flag=1
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    curl_setopt($ch, CURLOPT_REFERER, 'http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/Cnpjreva_Solicitacao2.asp');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $html = curl_exec($ch);
    curl_close($ch);
    return $html;
}

// função para pegar a resposta html da consulta pelo CPF na página da receita
function getHtmlCPF($cpf, $datanascim, $captcha, $token)
{
    $url = 'http://cpf.receita.fazenda.gov.br/situacao/ConsultaSituacao.asp';	// nova URL 29/maio/2017 para consulta CPF
	
    $cookieFile = COOKIELOCAL.'cpf_'.session_id();
	$cookieFile_fopen = HTTPCOOKIELOCAL.'cpf_'.session_id();
    if(!file_exists($cookieFile))
    {
        return false;      
    }
	else
	{
		// pega os dados de sessão gerados na visualização do captcha dentro do cookie
		$file = fopen($cookieFile_fopen, 'r');
		while (!feof($file))
		{$conteudo .= fread($file, 1024);}
		fclose ($file);
		
		$explodir = explode(chr(9),$conteudo);
		
		$sessionName = trim($explodir[count($explodir)-2]);
		$sessionId = trim($explodir[count($explodir)-1]);
		
		// prepara a variavel de session
		$cookie = $sessionName.'='.$sessionId;	
	}
	
	// dados que serão submetidos a consulta por post
    $post = array
    (
		'txtToken_captcha_serpro_gov_br'		=> $token,
		'txtTexto_captcha_serpro_gov_br'		=> $captcha,
		'txtCPF'								=> $cpf,
		'txtDataNascimento'						=> $datanascim,
    );
    $post = http_build_query($post, NULL, '&');
	
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);		// aqui estão os campos de formulário
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);	// dados do arquivo de cookie
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0');
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);			// continua a sessão anterior com os dados do captcha
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
    curl_setopt($ch, CURLOPT_REFERER, 'http://cpf.receita.fazenda.gov.br/situacao/');	// Novo Referer 29/Maio/2017
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
    $html = curl_exec($ch);
    curl_close($ch);

    return $html;
}

// Função para extrair o que interessa da HTML e colocar em array
function parseHtmlCNPJ($html)
{
	// respostas que interessam
	$campos = array(
	'NÚMERO DE INSCRIÇÃO',
	'DATA DE ABERTURA',
	'NOME EMPRESARIAL',
	'TÍTULO DO ESTABELECIMENTO (NOME DE FANTASIA)',
	'CÓDIGO E DESCRIÇÃO DA ATIVIDADE ECONÔMICA PRINCIPAL',
	'CÓDIGO E DESCRIÇÃO DAS ATIVIDADES ECONÔMICAS SECUNDÁRIAS',
	'CÓDIGO E DESCRIÇÃO DA NATUREZA JURÍDICA',
	'LOGRADOURO',
	'NÚMERO',
	'COMPLEMENTO',
	'CEP',
	'BAIRRO/DISTRITO',
	'MUNICÍPIO',
	'UF',
	'ENDEREÇO ELETRÔNICO',
	'TELEFONE',
	'ENTE FEDERATIVO RESPONSÁVEL (EFR)',
	'SITUAÇÃO CADASTRAL',
	'DATA DA SITUAÇÃO CADASTRAL',
	'MOTIVO DE SITUAÇÃO CADASTRAL',
	'SITUAÇÃO ESPECIAL',
	'DATA DA SITUAÇÃO ESPECIAL');
	// caracteres que devem ser eliminados da resposta
	$caract_especiais = array(
	chr(9),
	chr(10),
	chr(13),
	'&nbsp;',
	'</b>',
	'  ',
	'<b>MATRIZ<br>',
	'<b>FILIAL<br>'
	 );
	// prepara a resposta para extrair os dados
	$html = str_replace('<br><b>','<b>',str_replace($caract_especiais,'',strip_tags($html,'<b><br>')));
	
	$html3 = $html;
	// faz a extração
	for($i=0;$i<count($campos);$i++)
	{		
		$html2 = strstr($html,utf8_decode($campos[$i]));
		$resultado[] = trim(pega_o_que_interessa(utf8_decode($campos[$i]).'<b>','<br>',$html2));
		$html=$html2;
	}
	// extrai os CNAEs secundarios , quando forem mais de um
	if(strstr($resultado[5],'<b>'))
	{
		$cnae_secundarios = explode('<b>',$resultado[5]);
		$resultado[5] = $cnae_secundarios;
		unset($cnae_secundarios);
	}
	// devolve STATUS da consulta correto
	if(!$resultado[0])
	{
		if(strstr($html3,utf8_decode('O número do CNPJ não é válido')))
		{$resultado['status'] = 'CNPJ incorreto ou não existe';}
		else
		{$resultado['status'] = 'Imagem digitada incorretamente';}
	}
	else
	{$resultado['status'] = 'OK';}
	
	return $resultado;
}
// Função para extrair o que interessa da HTML e colocar em array
function parseHtmlCPF($html)
{
	// respostas que interessam
	$campos = array(
	'N&ordm; do CPF: <span class="clBold">',
	'Nome: <span class="clBold">',
	'Data Nascimento: <span class="clBold">',
	'Situa&ccedil;&atilde;o Cadastral: <span class="clBold">',
	'Data de Inscri&ccedil;&atilde;o no CPF: <span class="clBold">'
	);

	// para utilizar na hora de devolver o status da consulta
	$html3 = $html;
	// faz a extração
	for($i=0;$i<count($campos);$i++)
	{		
		$html2 = strstr($html,utf8_decode($campos[$i]));
		$resultado[] = trim(pega_o_que_interessa(utf8_decode($campos[$i]),'</span>',$html2));
		$html=$html2;
	}
	
	// devolve STATUS da consulta correto
	if(!$resultado[0])
	{
		if(strstr($html3,'CPF incorreto'))
		{$resultado['status'] = 'CPF incorreto';}		
		else if(strstr($html3,'n&atilde;o existe em nossa base de dados'))
		{$resultado['status'] = 'CPF não existe';}
		else if(strstr($html3,'Os caracteres da imagem n&atilde;o foram preenchidos corretamente'))
		{$resultado['status'] = 'Imagem digitada incorretamente';}
		else if(strstr($html3,'Data de nascimento informada'))
		{$resultado['status'] = 'Data de Nascimento divergente';}
		else
		{$resultado['status'] = 'Receita não responde';}
	}
	else
	{$resultado['status'] = 'OK';}
	return $resultado;
}
?>