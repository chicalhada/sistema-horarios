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

Clone o projeto a partir do GitHub



---

## 2. Iniciar o servidor

1. Dentro da pasta do prjeto, abra a pastas **public**.
2. Inicie um terminal dentro da mesma 
3. Utilize o comando **"php -S 0.0.0.0:8000"**

---

## 3. Executar a aplicação

Abra o navegador de Internet e introduza o seguintes endereço:


http://localhost:8000/admin.php -> Página de adiministrador
http://localhost:8000/room_list.php -> Lista das salas, a partir dela navega-se para os links das salas individuais




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

# Resolução de Problemas

Caso surja algum problema durante a instalação ou utilização da aplicação, consulte o ficheiro **TROUBLESHOOTING.md**.

Esse documento apresenta os erros mais frequentes, as respetivas causas e as soluções recomendadas, facilitando a resolução de eventuais dificuldades durante a utilização do sistema.
