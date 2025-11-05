# Importação do Banco (MySQL Workbench)

Este diretório contém tudo o que você precisa para criar e povoar o banco `cid10` sem ferramentas automáticas.

Arquivos:
- `schema.sql` – cria o schema do banco e todas as tabelas (capítulos, blocos, categorias, subcategorias e grupos de morbidade DATASUS).
- `seed.sql` – povoa todo o conteúdo (capítulos, 1 bloco por capítulo, categorias e subcategorias derivadas dos intervalos dos grupos DATASUS e a tabela `morbi_groups`).

Siga a sequência abaixo no MySQL Workbench (ou phpMyAdmin/CLI, se preferir):

1) Criar as tabelas
- Abra o MySQL Workbench e conecte-se ao seu servidor.
- Menu: File > Open SQL Script e selecione `sql/schema.sql`.
- Clique em "Execute" (raio) para rodar o script. Isso irá:
  - Criar a base `cid10` (se ainda não existir) com `utf8mb4`.
  - Criar as tabelas: `chapters`, `blocks`, `categories`, `subcategories`, `morbi_groups`.

2) Povoar os dados
- No Workbench, abra `sql/seed.sql`.
- Execute o script. Ele:
  - Faz `TRUNCATE` nas tabelas para garantir importação limpa.
  - Insere todos os capítulos e um bloco por capítulo.
  - Expande e insere as categorias e subcategorias com base nos intervalos listados na tabela de morbidade do DATASUS.
  - Preenche a tabela `morbi_groups` com os grupos (código 001, 007.1 etc.).

3) Checagens rápidas (opcional)
Execute no Workbench (já com o schema `cid10` em uso):
```
SELECT COUNT(*) AS chapters FROM chapters;
SELECT COUNT(*) AS blocks FROM blocks;
SELECT COUNT(*) AS categories FROM categories;
SELECT COUNT(*) AS subcategories FROM subcategories;
SELECT COUNT(*) AS morbi_groups FROM morbi_groups;
```

Notas importantes:
- Encoding: os scripts incluem `SET NAMES utf8mb4;` para preservar acentos corretamente.
- Idempotência: como há `TRUNCATE` e reset de AUTO_INCREMENT, repetir a execução do `seed.sql` repovoa do zero.
- Fonte dos dados: `seed.sql` foi gerado a partir da "Lista de Tabulação para Morbidade" do DATASUS (mxcid10lm). As categorias/subcategorias foram derivadas a partir dos intervalos de CID informados nessa lista. Se desejar utilizar a taxonomia OMS completa e oficial linha a linha, substitua o `seed.sql` por um dataset integral da OMS.

Pronto! Com as tabelas e dados importados, a interface `index.php` já consegue consultar por código (A00, E11, E11.9...) ou descrição (ex.: "diabetes") e exibir também os grupos de morbidade do DATASUS.

