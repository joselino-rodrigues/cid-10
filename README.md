# CID-10

## Plataforma de Pesquisa do CID-10 – Organização Mundial da Saúde (OMS)

Este projeto tem como objetivo disponibilizar uma ferramenta simples e funcional para consulta rápida da Classificação Internacional de Doenças (CID-10). A aplicação pode ser executada localmente ou hospedada em um servidor institucional, integrando-se facilmente a outras plataformas desenvolvidas em PHP.

## Estrutura do Projeto

- Raiz (somente): `index.php`, `LICENSE`, `README.md`
- `app/` – Configuração e utilitários de aplicação
  - `app/config.php` – Conexão PDO (MySQL)
- `sql/` – Arquivos SQL organizados
  - `schema.sql` – Criação do banco `cid10` e tabelas (CID-10 + Grupos de Morbidade)
  - `seed.sql` – Povoamento completo (capítulos, 1 bloco por capítulo, categorias, subcategorias e grupos de morbidade do DATASUS)
 

## Ambiente de Execução

Para executar o projeto localmente, recomenda-se o uso de:

XAMPP – Um pacote de software livre que integra servidor Apache, PHP e MySQL, permitindo rodar aplicações web em ambiente local de forma prática.

MySQL Workbench – Ferramenta gráfica que facilita a administração, modelagem e manutenção do banco de dados, ideal para quem deseja visualizar e editar as tabelas do CID-10 com maior controle.

Essas ferramentas simulam o ambiente de um servidor web, permitindo o desenvolvimento, testes e ajustes antes da publicação definitiva.

## Finalidade e Aplicações

A plataforma foi idealizada para servir tanto como ferramenta de consulta clínica quanto como módulo auxiliar em projetos institucionais que demandem integração com a base oficial da CID-10.
Pode ser incorporada, por exemplo, a sistemas de prontuário eletrônico, plataformas de telemedicina ou ambientes de ensino médico.

## Como inicializar o banco

1. Execute o esquema: `sql/schema.sql` (cria `cid10` e tabelas)
2. Importe o seed:
   - Abra `sql/seed.sql` no MySQL Workbench e execute para popular os dados completos.

## Observação
O projeto é de uso livre para fins acadêmicos e institucionais, desde que citada a fonte original da classificação (OMS) e os agrupamentos (DATASUS).
A estrutura proposta busca simplicidade, portabilidade e compatibilidade com futuras expansões em PHP e MySQL.

Observação: a lista do DATASUS é uma estrutura de agrupamento para tabulação de morbidade e, neste projeto, foi utilizada para compor o `seed.sql` completo (capítulos, 1 bloco por capítulo, categorias/subcategorias derivadas dos intervalos e grupos DATASUS).
