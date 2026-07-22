# Plano de Testes

Este documento descreve os testes realizados ao sistema para verificar o correto funcionamento das suas funcionalidades.

## Teste de Upload do CSV

Objetivo:

Verificar se o sistema importa corretamente um ficheiro CSV.

Resultado esperado:

Os dados devem ser guardados na base de dados sem apresentar erros.

---

## Teste de Consulta de Horários

Objetivo:

Confirmar que o horário apresentado corresponde aos dados importados.

Resultado esperado:

A informação deve ser apresentada corretamente para cada sala.

---

## Teste de Geração de QR Codes

Objetivo:

Verificar se o QR Code é criado corretamente.

Resultado esperado:

O QR Code deve abrir a página correspondente ao horário da sala.

---

## Compatibilidade

Foram realizados testes nos seguintes navegadores:

- Google Chrome
- Microsoft Edge
- Mozilla Firefox

Também foram realizados testes em dispositivos móveis para verificar a adaptação da interface.

---

## Testes Adicionais

Foram ainda testados os seguintes casos:

- Upload de ficheiro vazio.
- Upload de ficheiro com formato inválido.
- Consulta de uma sala inexistente.
- Dados duplicados.
- Caracteres especiais.

---

## Melhorias Futuras

Durante os testes foram identificadas algumas possíveis melhorias:

- Melhorar as mensagens de erro.
- Tornar a interface mais intuitiva.
- Adicionar confirmação após o upload.
- Melhorar a adaptação a dispositivos móveis.

---

## Reporte de Bugs

Sempre que for encontrado um erro deverão ser registadas as seguintes informações:

- descrição do problema;
- passos para reproduzir;
- resultado esperado;
- resultado obtido;
- navegador utilizado;
- data do teste.