# USER_GUIDE.md

# Guia do Utilizador

## Introdução

O **Sistema de Horários** foi desenvolvido para facilitar a gestão e a consulta dos horários das salas de uma instituição de ensino.

Este guia descreve as principais funcionalidades da aplicação e explica, passo a passo, como utilizá-las corretamente.

---

# Iniciar Sessão

Para aceder às funcionalidades de administração é necessário efetuar o login.

### Passos

1. Abra a aplicação no navegador.
2. Introduza o nome de utilizador.
3. Introduza a palavra-passe.
4. Clique no botão **Iniciar Sessão**.

Após a autenticação, será apresentada a área de administração da aplicação.

---

# Importação de Horários

A importação permite adicionar ou atualizar os horários através de um ficheiro CSV.

### Passos

1. Aceda à área de administração.
2. Abra a página **Upload**.
3. Clique em **Selecionar ficheiro**.
4. Escolha um ficheiro no formato **CSV**.
5. Clique em **Importar**.

Se a importação for concluída com sucesso, os horários serão automaticamente guardados na base de dados SQLite.

---

# Consultar Horários

Depois de importar os dados, é possível consultar os horários das salas.

### Passos

1. Aceda à página **Salas**.
2. Selecione a sala pretendida.
3. O sistema apresenta automaticamente o respetivo horário.

As informações apresentadas correspondem aos dados existentes na base de dados.

---

# Gerar QR Codes

Cada sala possui um QR Code que permite aceder rapidamente ao respetivo horário através de um dispositivo móvel.

### Passos

1. Abra a página da sala desejada.
2. Clique no botão **Gerar QR Code**.
3. Utilize a câmara do telemóvel ou uma aplicação de leitura de QR Codes para digitalizar o código.

Após a leitura, o navegador abrirá automaticamente a página correspondente ao horário da sala.

---

# Recomendações de Utilização

Para garantir o correto funcionamento da aplicação, recomenda-se que:

* Utilize apenas ficheiros no formato **CSV**;
* Não altere a estrutura ou a ordem das colunas do ficheiro;
* Verifique se a importação foi concluída com sucesso antes de consultar os horários;
* Mantenha uma cópia de segurança dos ficheiros CSV utilizados;
* Utilize um navegador atualizado para obter o melhor desempenho.

---

# Boas Práticas

Durante a utilização da aplicação, tenha em consideração as seguintes recomendações:

* Verifique os dados antes de importar um novo ficheiro;
* Evite importar o mesmo ficheiro várias vezes sem necessidade;
* Confirme que os horários apresentados correspondem aos dados importados;
* Utilize os QR Codes para facilitar o acesso aos horários por parte de alunos, professores e funcionários.

---

# Dicas Úteis

* Caso ocorra algum erro durante a importação, consulte o ficheiro **TROUBLESHOOTING.md**.
* Se os horários não forem apresentados, confirme primeiro se o ficheiro CSV foi importado corretamente.
* Sempre que forem efetuadas alterações aos horários, importe um novo ficheiro CSV para atualizar a base de dados.

---

# Conclusão

O **Sistema de Horários** foi concebido para tornar a gestão e a consulta dos horários simples, rápida e intuitiva. Seguindo os procedimentos descritos neste guia, qualquer utilizador conseguirá importar horários, consultar salas e utilizar os QR Codes de forma eficiente e segura.
