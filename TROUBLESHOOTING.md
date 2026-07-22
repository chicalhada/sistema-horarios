# TROUBLESHOOTING.md

# Resolução de Problemas

## Introdução

Este documento reúne os problemas mais comuns que podem ocorrer durante a instalação ou utilização do **Sistema de Horários**, apresentando as respetivas causas e soluções recomendadas.

Caso o problema persista após seguir estas indicações, recomenda-se o reporte do erro ao responsável pela aplicação.

---

# Não consigo iniciar sessão

## Possíveis causas

* Nome de utilizador incorreto;
* Palavra-passe incorreta;
* Utilizador não registado;
* Problema de ligação à base de dados.

## Solução

* Confirme que introduziu corretamente o nome de utilizador e a palavra-passe.
* Verifique se não existem espaços antes ou depois dos dados introduzidos.
* Certifique-se de que a base de dados foi criada corretamente.
* Reinicie o servidor Apache e tente novamente.

---

# O ficheiro CSV não é importado

## Possíveis causas

* O ficheiro encontra-se vazio;
* O ficheiro não está no formato CSV;
* Existem colunas obrigatórias em falta;
* O ficheiro possui um formato incorreto;
* Existem dados inválidos.

## Solução

* Confirme que o ficheiro possui a extensão **.csv**.
* Verifique se todas as colunas obrigatórias estão presentes.
* Certifique-se de que o ficheiro contém dados válidos.
* Tente importar novamente após corrigir o ficheiro.

---

# Não aparecem salas

## Possíveis causas

* O ficheiro CSV ainda não foi importado;
* A importação falhou;
* A base de dados encontra-se vazia.

## Solução

* Verifique se a importação foi concluída com sucesso.
* Confirme que os dados foram guardados na base de dados SQLite.
* Importe novamente o ficheiro CSV, se necessário.

---

# Erro na Base de Dados

## Possíveis causas

* A pasta **data** não possui permissões de escrita;
* A base de dados foi eliminada ou encontra-se corrompida;
* O PHP não possui suporte para SQLite.

## Solução

* Verifique as permissões da pasta **data**.
* Confirme que o ficheiro da base de dados existe.
* Certifique-se de que a extensão SQLite está ativa no PHP.
* Reinicie o servidor Apache.

---

# O QR Code não é apresentado

## Possíveis causas

* A pasta destinada aos QR Codes não existe;
* O QR Code ainda não foi gerado;
* O ficheiro foi eliminado.

## Solução

Confirme que existe a seguinte pasta:

```text id="tchm2o"
public/qr-codes
```

Caso não exista, crie-a novamente ou gere um novo QR Code através da aplicação.

---

# A aplicação não abre

## Possíveis causas

* O Apache não está em execução;
* O projeto não foi colocado na pasta correta;
* O endereço utilizado está incorreto.

## Solução

* Abra o **XAMPP Control Panel** e inicie o serviço **Apache**.
* Confirme que o projeto se encontra em:

```text id="5jvdrn"
C:\xampp\htdocs\sistema-horarios
```

* Aceda ao seguinte endereço no navegador:

```text id="rfdxgd"
http://localhost/sistema-horarios/public
```

---

# Problemas de Compatibilidade

A aplicação foi testada com os seguintes navegadores:

* Google Chrome
* Microsoft Edge
* Mozilla Firefox

Caso utilize outro navegador e encontre problemas de visualização, recomenda-se testar num dos navegadores acima indicados.

---

# Como Reportar um Erro

Se encontrar um problema que não esteja descrito neste documento, envie as seguintes informações:

* Data e hora em que ocorreu o erro;
* Descrição detalhada do problema;
* Passos necessários para reproduzir a situação;
* Resultado esperado;
* Resultado obtido;
* Navegador utilizado;
* Sistema operativo;
* Capturas de ecrã (se aplicável).

Estas informações facilitam a identificação e resolução do problema.

---

# Conclusão

A maioria dos problemas pode ser resolvida verificando a instalação da aplicação, a configuração do servidor Apache, a existência da base de dados SQLite e a correta importação dos ficheiros CSV.

Caso o erro persista após seguir todas as recomendações apresentadas neste documento, recomenda-se contactar o responsável pelo projeto para obter suporte adicional.
