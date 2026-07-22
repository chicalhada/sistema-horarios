# INSTALL.md

# Guia de Instalação

Este documento explica, passo a passo, como instalar e executar a aplicação **Sistema de Horários** num computador com Windows.

---

# Requisitos

Antes de iniciar a instalação, confirme que o seu computador possui os seguintes requisitos:

* **PHP 8.0** ou versão superior;
* **SQLite** (incluído nas versões mais recentes do PHP);
* Um navegador Web atualizado, como **Google Chrome**, **Microsoft Edge** ou **Mozilla Firefox**.

---

# Instalação

## 1. Copiar o projeto

Copie a pasta do projeto **sistema-horarios** para a pasta **htdocs** do XAMPP.

**Exemplo:**

```text
C:\xampp\htdocs\sistema-horarios
```

---

## 2. Iniciar o servidor

1. Abra a **Pasta do projeto**.
2. Inicie um terminal dentro da mesma 
3. Utilize o comando **"php -S 0.0.0.0:8000"**

---

## 3. Executar a aplicação

Abra o navegador de Internet e introduza o seguinte endereço:

```text
http://localhost/sistema-horarios/public
```

Se a instalação tiver sido realizada corretamente, será apresentada a página inicial da aplicação.

---

# Base de Dados

A aplicação utiliza a tecnologia **SQLite** para armazenar a informação, não sendo necessária a instalação de um servidor de bases de dados.

Na primeira execução, caso a base de dados ainda não exista, esta será criada automaticamente. Todos os dados gerados pela aplicação serão guardados na pasta **data**, permitindo a sua reutilização em futuras execuções.

---

# Estrutura do Projeto

A organização das principais pastas do projeto é a seguinte:

```text
sistema-horarios/
│
├── data/         → Base de dados SQLite e ficheiros de dados
├── public/       → Ponto de entrada da aplicação
├── src/          → Código-fonte e lógica da aplicação
├── views/        → Interfaces apresentadas ao utilizador
├── uploads/      → Ficheiros enviados pelos utilizadores
├── README.md     → Informação geral sobre o projeto
├── INSTALL.md    → Guia de instalação
└── TROUBLESHOOTING.md → Resolução de problemas
```

---

# Verificar a Instalação

Depois de concluir a instalação, confirme que:

* O serviço **Apache** está em execução;
* O endereço `http://localhost/sistema-horarios/public` abre corretamente a aplicação;
* A base de dados SQLite foi criada automaticamente;
* Não são apresentadas mensagens de erro relacionadas com o PHP ou com permissões de acesso.

Se todos estes pontos forem cumpridos, a aplicação encontra-se pronta a ser utilizada.

---

# Atualizar a Aplicação

Quando existir uma nova versão da aplicação, siga os seguintes passos:

1. Pare o serviço **Apache** no XAMPP;
2. Substitua os ficheiros antigos pelos novos;
3. Mantenha a pasta **data** caso pretenda preservar a base de dados e os dados já existentes;
4. Inicie novamente o serviço **Apache**.

---

# Resolução de Problemas

Caso surja algum problema durante a instalação ou utilização da aplicação, consulte o ficheiro **TROUBLESHOOTING.md**.

Esse documento apresenta os erros mais frequentes, as respetivas causas e as soluções recomendadas, facilitando a resolução de eventuais dificuldades durante a utilização do sistema.
