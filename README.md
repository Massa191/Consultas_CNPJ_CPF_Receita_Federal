# Consultas_CNPJ_CPF_Receita_Federal
Consulta CNPJ e CPF na Receita com Captcha

##  Utilização

###  index.php

Basta digitar os campos CNPJ + Captcha , ou CPF + Data de Nascimanto + Captcha Para consulta os registros na Receita Federal

Especial atenção para a pasta cookies, pois é lá que serão salvos os cookies de sessão com a Receita Federal. A constante COOKIELOCAL deve apontar para a sua localização.

##  Recomendações

Ao Utilizar esta solução em sua aplicação, recomendo o tratamento dos campos de formulário $_GET , $_POST ,afim de evitar possiveis injections

## Autor

Marcos Peli: [facebook.com/pelimarcos][facebook]

## Licensa

Licensa [MIT][mit]. Aproveite

[facebook]: https://www.facebook.com/pelimarcos
[mit]: http://www.opensource.org/licenses/mit-license.php

