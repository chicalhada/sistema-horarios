# TESTES.md

# Plano de Testes

## Objetivo

Este documento apresenta os testes realizados ao **Sistema de Horários**, com o objetivo de verificar o correto funcionamento das suas funcionalidades, identificar possíveis erros e garantir a estabilidade e fiabilidade da aplicação.

Os testes foram efetuados em diferentes cenários de utilização, abrangendo tanto situações normais como casos de erro, permitindo validar o comportamento do sistema perante diferentes condições.

---

# Teste 1 – Importação de Ficheiros CSV

### Objetivo

Verificar se o sistema importa corretamente um ficheiro CSV contendo os horários das salas.

### Procedimento

1. Efetuar o login na área de administração.
2. Selecionar um ficheiro CSV válido.
3. Executar a importação.
4. Confirmar que os dados foram guardados.

### Resultado Esperado

* O ficheiro deve ser importado sem erros.
* Os dados devem ser armazenados corretamente na base de dados SQLite.
* Deve ser apresentada uma mensagem de sucesso ao utilizador.

### Resultado Obtido

✅ Teste concluído com sucesso.

---

# Teste 2 – Consulta de Horários

### Objetivo

Confirmar que os horários apresentados correspondem exatamente aos dados importados.

### Procedimento

1. Selecionar uma sala existente.
2. Consultar o respetivo horário.
3. Comparar a informação apresentada com os dados do ficheiro CSV.

### Resultado Esperado

* O horário apresentado deve estar correto.
* Todas as aulas devem surgir na ordem prevista.
* Não devem existir informações em falta ou duplicadas.

### Resultado Obtido

✅ Teste concluído com sucesso.

---

# Teste 3 – Geração de QR Codes

### Objetivo

Verificar se o sistema gera corretamente os QR Codes associados a cada sala.

### Procedimento

1. Selecionar uma sala.
2. Gerar o respetivo QR Code.
3. Ler o QR Code utilizando um smartphone.

### Resultado Esperado

* O QR Code deve ser gerado corretamente.
* A leitura do código deve abrir diretamente a página correspondente ao horário da sala.

### Resultado Obtido

✅ Teste concluído com sucesso.

---

# Teste 4 – Validação de Dados

### Objetivo

Verificar a capacidade do sistema para identificar dados inválidos.

### Casos Testados

* Upload de ficheiro CSV vazio;
* Upload de ficheiro com formato inválido;
* Ficheiro com colunas incorretas;
* Dados duplicados;
* Campos obrigatórios em falta;
* Caracteres especiais;
* Consulta de uma sala inexistente.

### Resultado Esperado

O sistema deve impedir a importação de dados inválidos e apresentar mensagens de erro claras e informativas.

### Resultado Obtido

✅ O sistema respondeu corretamente em todos os casos testados.

---

# Compatibilidade

Foram realizados testes em diferentes navegadores para garantir a compatibilidade da aplicação.

| Navegador       | Resultado               |
| --------------- | ----------------------- |
| Google Chrome   | ✅ Funcionamento correto |
| Microsoft Edge  | ✅ Funcionamento correto |
| Mozilla Firefox | ✅ Funcionamento correto |

Também foram realizados testes em dispositivos móveis, verificando que a interface se adapta corretamente a diferentes tamanhos de ecrã.

---

# Testes de Desempenho

Foram efetuados testes para avaliar o comportamento da aplicação durante a importação de ficheiros e a consulta de horários.

### Resultado

* A importação dos ficheiros foi realizada sem atrasos significativos.
* A consulta dos horários apresentou tempos de resposta reduzidos.
* Não foram observadas falhas de desempenho durante os testes realizados.

---

# Melhorias Futuras

Durante a fase de testes foram identificadas algumas melhorias que poderão ser implementadas em versões futuras da aplicação:

* Melhorar as mensagens de erro apresentadas ao utilizador;
* Adicionar uma confirmação visual após a importação dos horários;
* Disponibilizar uma barra de progresso durante o upload dos ficheiros;
* Melhorar ainda mais a adaptação da interface a dispositivos móveis;
* Implementar testes automáticos para validar novas funcionalidades.

---

# Reporte de Bugs

Sempre que for identificado um problema, recomenda-se o registo das seguintes informações:

* Data e hora do teste;
* Descrição detalhada do problema;
* Passos necessários para reproduzir o erro;
* Resultado esperado;
* Resultado obtido;
* Navegador utilizado;
* Sistema operativo;
* Capturas de ecrã (quando aplicável).

---

# Conclusão

Os testes realizados permitiram confirmar que as principais funcionalidades do **Sistema de Horários** funcionam corretamente. A aplicação demonstrou estabilidade durante a importação de ficheiros, consulta de horários e geração de QR Codes, apresentando um comportamento consistente nos diferentes navegadores e dispositivos testados.

Embora tenham sido identificadas algumas oportunidades de melhoria, o sistema encontra-se apto para utilização e cumpre os objetivos definidos para o projeto.
