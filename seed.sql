-- seed.sql
-- Semear CID-10: capítulos → blocos → categorias → subcategorias
-- Execução idempotente gentil: limpa e repovoa mantendo integridade.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Limpeza (opcional; com cuidado em produção)
TRUNCATE TABLE subcategories;
TRUNCATE TABLE categories;
TRUNCATE TABLE blocks;
TRUNCATE TABLE chapters;

ALTER TABLE chapters     AUTO_INCREMENT = 1;
ALTER TABLE blocks       AUTO_INCREMENT = 1;
ALTER TABLE categories   AUTO_INCREMENT = 1;
ALTER TABLE subcategories AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

START TRANSACTION;

-- =========================
-- 1) CAPÍTULOS (IDs fixos)
-- =========================
-- id, numeral, code_start, code_end, title
INSERT INTO chapters (id, numeral, code_start, code_end, title) VALUES
(1 , 'I'   , 'A00', 'B99', 'Algumas doenças infecciosas e parasitárias'),
(2 , 'II'  , 'C00', 'D48', 'Neoplasias'),
(3 , 'III' , 'D50', 'D89', 'Doenças do sangue e dos órgãos hematopoéticos e alguns transtornos imunitários'),
(4 , 'IV'  , 'E00', 'E90', 'Doenças endócrinas, nutricionais e metabólicas'),
(5 , 'V'   , 'F00', 'F99', 'Transtornos mentais e comportamentais'),
(6 , 'VI'  , 'G00', 'G99', 'Doenças do sistema nervoso'),
(7 , 'VII' , 'H00', 'H59', 'Doenças do olho e anexos'),
(8 , 'VIII', 'H60', 'H95', 'Doenças do ouvido e da apófise mastoide'),
(9 , 'IX'  , 'I00', 'I99', 'Doenças do aparelho circulatório'),
(10, 'X'   , 'J00', 'J99', 'Doenças do aparelho respiratório'),
(11, 'XI'  , 'K00', 'K93', 'Doenças do aparelho digestivo'),
(12, 'XII' , 'L00', 'L99', 'Doenças da pele e do tecido subcutâneo'),
(13, 'XIII', 'M00', 'M99', 'Doenças do sistema osteomuscular e do tecido conjuntivo'),
(14, 'XIV' , 'N00', 'N99', 'Doenças do aparelho geniturinário'),
(15, 'XV'  , 'O00', 'O99', 'Gravidez, parto e puerpério'),
(16, 'XVI' , 'P00', 'P96', 'Algumas afecções originadas no período perinatal'),
(17, 'XVII', 'Q00', 'Q99', 'Malformações congênitas, deformidades e anomalias cromossômicas'),
(18, 'XVIII','R00','R99', 'Sintomas, sinais e achados anormais de exames clínicos e laboratoriais, não classificados em outra parte'),
(19, 'XIX' , 'S00', 'T98', 'Lesões, envenenamentos e algumas outras consequências de causas externas'),
(20, 'XX'  , 'V01', 'Y98', 'Causas externas de morbidade e de mortalidade'),
(21, 'XXI' , 'Z00', 'Z99', 'Fatores que influenciam o estado de saúde e o contato com os serviços de saúde'),
(22, 'XXII', 'U00', 'U85', 'Códigos para propósitos especiais');

-- =========================
-- 2) BLOCOS (IDs fixos)
-- =========================
-- id, chapter_id, code_start, code_end, title

-- Cap. I (1)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(1 , 1, 'A00', 'A09', 'Doenças infecciosas intestinais'),
(2 , 1, 'A15', 'A19', 'Tuberculose'),
(3 , 1, 'B20', 'B24', 'Doença pelo HIV');

-- Cap. II (2)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(4 , 2, 'C00', 'C14', 'Neoplasias malignas do lábio, cavidade oral e faringe'),
(5 , 2, 'C15', 'C26', 'Neoplasias malignas dos órgãos digestivos'),
(6 , 2, 'D37', 'D48', 'Neoplasias de comportamento incerto ou desconhecido');

-- Cap. III (3)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(7 , 3, 'D50', 'D53', 'Anemias por deficiências nutricionais'),
(8 , 3, 'D55', 'D59', 'Anemias hemolíticas'),
(9 , 3, 'D80', 'D89', 'Alguns transtornos envolvendo o mecanismo imunitário');

-- Cap. IV (4)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(10, 4, 'E00', 'E07', 'Transtornos da tireoide'),
(11, 4, 'E10', 'E14', 'Diabetes mellitus'),
(12, 4, 'E70', 'E90', 'Transtornos metabólicos');

-- Cap. V (5)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(13, 5, 'F10', 'F19', 'Transtornos mentais e comportamentais devidos ao uso de substâncias'),
(14, 5, 'F20', 'F29', 'Esquizofrenia, transtornos esquizotípicos e delirantes'),
(15, 5, 'F30', 'F39', 'Transtornos do humor (afetivos)');

-- Cap. VI (6)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(16, 6, 'G00', 'G09', 'Doenças inflamatórias do sistema nervoso central'),
(17, 6, 'G30', 'G32', 'Transtornos degenerativos do sistema nervoso'),
(18, 6, 'G40', 'G47', 'Episódios paroxísticos e distúrbios do sono');

-- Cap. VII (7)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(19, 7, 'H10', 'H13', 'Transtornos da conjuntiva'),
(20, 7, 'H40', 'H42', 'Glaucoma');

-- Cap. VIII (8)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(21, 8, 'H60', 'H62', 'Doenças do ouvido externo'),
(22, 8, 'H65', 'H75', 'Doenças do ouvido médio e da mastoide');

-- Cap. IX (9)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(23, 9, 'I10', 'I15', 'Doenças hipertensivas'),
(24, 9, 'I20', 'I25', 'Doenças isquêmicas do coração'),
(25, 9, 'I60', 'I69', 'Doenças cerebrovasculares');

-- Cap. X (10)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(26, 10, 'J00', 'J06', 'Infecções agudas das vias aéreas superiores'),
(27, 10, 'J09', 'J18', 'Influenza (gripe) e pneumonia'),
(28, 10, 'J40', 'J47', 'Doenças pulmonares obstrutivas e asma');

-- Cap. XI (11)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(29, 11, 'K20', 'K31', 'Doenças do esôfago, estômago e duodeno'),
(30, 11, 'K35', 'K38', 'Apendicite'),
(31, 11, 'K70', 'K77', 'Doenças do fígado');

-- Cap. XII (12)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(32, 12, 'L00', 'L08', 'Infecções da pele e do tecido subcutâneo'),
(33, 12, 'L40', 'L45', 'Transtornos papuloescamosos');

-- Cap. XIII (13)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(34, 13, 'M00', 'M25', 'Artropatias'),
(35, 13, 'M40', 'M54', 'Dorsopatias'),
(36, 13, 'M70', 'M79', 'Transtornos dos tecidos moles');

-- Cap. XIV (14)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(37, 14, 'N00', 'N08', 'Doenças glomerulares'),
(38, 14, 'N10', 'N16', 'Doenças túbulo-intersticiais'),
(39, 14, 'N40', 'N51', 'Doenças dos órgãos genitais masculinos');

-- Cap. XV (15)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(40, 15, 'O10', 'O16', 'Transtornos hipertensivos na gravidez'),
(41, 15, 'O80', 'O84', 'Parto');

-- Cap. XVI (16)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(42, 16, 'P00', 'P04', 'Feto e recém-nascido afetados por fatores maternos e complicações'),
(43, 16, 'P70', 'P74', 'Distúrbios metabólicos do período perinatal');

-- Cap. XVII (17)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(44, 17, 'Q00', 'Q07', 'Malformações congênitas do sistema nervoso'),
(45, 17, 'Q90', 'Q99', 'Anomalias cromossômicas, não classificadas em outra parte');

-- Cap. XVIII (18)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(46, 18, 'R00', 'R09', 'Sintomas e sinais relativos aos aparelhos circulatório e respiratório'),
(47, 18, 'R50', 'R69', 'Sintomas e sinais gerais');

-- Cap. XIX (19)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(48, 19, 'S00', 'S99', 'Traumatismos dos locais específicos do corpo'),
(49, 19, 'T36', 'T50', 'Intoxicação por fármacos, medicamentos e substâncias biológicas');

-- Cap. XX (20)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(50, 20, 'V01', 'V99', 'Acidentes de transporte'),
(51, 20, 'W00', 'X59', 'Outras causas externas de lesões acidentais'),
(52, 20, 'X60', 'Y09', 'Agressões e autoagressões');

-- Cap. XXI (21)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(53, 21, 'Z00', 'Z13', 'Exames e investigações'),
(54, 21, 'Z20', 'Z29', 'Potencial exposição a riscos e necessidade de profilaxia'),
(55, 21, 'Z70', 'Z76', 'Pessoas em contato com os serviços de saúde por outras circunstâncias');

-- Cap. XXII (22)
INSERT INTO blocks (id, chapter_id, code_start, code_end, title) VALUES
(56, 22, 'U00', 'U49', 'Códigos temporários para emergências de saúde pública'),
(57, 22, 'U80', 'U85', 'Resistência a antimicrobianos');

-- =========================
-- 3) CATEGORIAS (IDs fixos)
-- =========================
-- id, block_id, code, title

-- Exemplos Cap. I
INSERT INTO categories (id, block_id, code, title) VALUES
(1 , 1, 'A00', 'Cólera'),
(2 , 1, 'A01', 'Febres tifóide e paratifóide'),
(3 , 2, 'A15', 'Tuberculose respiratória, confirmação bacteriológica e histológica'),
(4 , 3, 'B20', 'Doença pelo HIV resultando em doenças infecciosas e parasitárias'),
(5 , 3, 'B24', 'Doença pelo HIV, não especificada');

-- Exemplos Cap. II
INSERT INTO categories (id, block_id, code, title) VALUES
(6 , 4, 'C02', 'Neoplasia maligna da língua'),
(7 , 5, 'C16', 'Neoplasia maligna do estômago'),
(8 , 5, 'C18', 'Neoplasia maligna do cólon'),
(9 , 6, 'D48', 'Neoplasia de comportamento incerto ou desconhecido');

-- Exemplos Cap. IV (Diabetes)
INSERT INTO categories (id, block_id, code, title) VALUES
(10, 11, 'E10', 'Diabetes mellitus insulinodependente (tipo 1)'),
(11, 11, 'E11', 'Diabetes mellitus não-insulinodependente (tipo 2)');

-- Exemplos Cap. IX (Circulatório)
INSERT INTO categories (id, block_id, code, title) VALUES
(12, 23, 'I10', 'Hipertensão essencial (primária)'),
(13, 24, 'I21', 'Infarto agudo do miocárdio'),
(14, 25, 'I63', 'Infarto cerebral');

-- Exemplos Cap. X (Respiratório)
INSERT INTO categories (id, block_id, code, title) VALUES
(15, 27, 'J10', 'Influenza devida a vírus da gripe identificado'),
(16, 27, 'J18', 'Pneumonia por micro-organismo não especificado'),
(17, 28, 'J44', 'Outras doenças pulmonares obstrutivas crônicas'),
(18, 28, 'J45', 'Asma');

-- Exemplos Cap. XI (Digestivo)
INSERT INTO categories (id, block_id, code, title) VALUES
(19, 29, 'K20', 'Esofagite'),
(20, 31, 'K70', 'Doença alcoólica do fígado');

-- Exemplos Cap. XII (Pele)
INSERT INTO categories (id, block_id, code, title) VALUES
(21, 33, 'L40', 'Psoríase');

-- Exemplos Cap. XIII (Osteomuscular)
INSERT INTO categories (id, block_id, code, title) VALUES
(22, 34, 'M17', 'Gonartrose (artrose do joelho)');

-- Exemplos Cap. XIV (Geniturinário)
INSERT INTO categories (id, block_id, code, title) VALUES
(23, 37, 'N03', 'Síndrome nefrítica recorrente'),
(24, 38, 'N12', 'Nefrite túbulo-intersticial, não especificada');

-- Exemplos Cap. XV (Obstetrícia)
INSERT INTO categories (id, block_id, code, title) VALUES
(25, 40, 'O14', 'Pré-eclâmpsia');

-- Exemplos Cap. XVI (Perinatal)
INSERT INTO categories (id, block_id, code, title) VALUES
(26, 42, 'P02', 'Feto e recém-nascido afetados por complicações da placenta');

-- Exemplos Cap. XVII (Congênitas)
INSERT INTO categories (id, block_id, code, title) VALUES
(27, 45, 'Q90', 'Síndrome de Down');

-- Exemplos Cap. XVIII (Sintomas)
INSERT INTO categories (id, block_id, code, title) VALUES
(28, 46, 'R06', 'Anormalidades da respiração'),
(29, 47, 'R50', 'Febre de origem desconhecida');

-- Exemplos Cap. XIX (Lesões/Intox.)
INSERT INTO categories (id, block_id, code, title) VALUES
(30, 48, 'S06', 'Traumatismo intracraniano'),
(31, 49, 'T40', 'Intoxicação por narcóticos e psicodislépticos [alucinógenos]');

-- Exemplos Cap. XX (Causas externas)
INSERT INTO categories (id, block_id, code, title) VALUES
(32, 50, 'V89', 'Acidente de transporte por veículo a motor, tipo não especificado'),
(33, 52, 'X70', 'Lesão autoprovocada intencionalmente por enforcamento');

-- Exemplos Cap. XXI (Fatores influência)
INSERT INTO categories (id, block_id, code, title) VALUES
(34, 53, 'Z00', 'Exame geral e investigação de pessoas sem queixas ou diagnóstico relatado'),
(35, 54, 'Z20', 'Contato com e exposição a doenças transmissíveis'),
(36, 55, 'Z76', 'Pessoas em contato com serviços de saúde por outras circunstâncias');

-- Exemplos Cap. XXII (Especiais)
INSERT INTO categories (id, block_id, code, title) VALUES
(37, 56, 'U07', 'Códigos de uso temporário (ex.: emergências de saúde pública)'),
(38, 56, 'U07', 'Reserva geral (usar subcategorias quando aplicável)');

-- =========================
-- 4) SUBCATEGORIAS (IDs fixos)
-- =========================
-- id, category_id, code_full, title

-- Cap. I
INSERT INTO subcategories (id, category_id, code_full, title) VALUES
(1 , 1 , 'A00.0', 'Cólera devida ao Vibrio cholerae 01, biótipo cholerae'),
(2 , 2 , 'A01.0', 'Febre tifóide'),
(3 , 3 , 'A15.0', 'Tuberculose pulmonar, confirmação bacteriológica e histológica');

-- Cap. II
INSERT INTO subcategories (id, category_id, code_full, title) VALUES
(4 , 6 , 'C02.1', 'Bordo lateral da língua'),
(5 , 7 , 'C16.9', 'Neoplasia maligna do estômago, não especificado'),
(6 , 8 , 'C18.7', 'Cólon sigmoide');

-- Cap. IV (Diabetes)
INSERT INTO subcategories (id, category_id, code_full, title) VALUES
(7 , 10, 'E10.9', 'Diabetes mellitus tipo 1 sem complicações'),
(8 , 11, 'E11.2', 'Diabetes mellitus tipo 2 com complicações renais'),
(9 , 11, 'E11.9', 'Diabetes mellitus tipo 2 sem complicações');

-- Cap. IX (Circulatório)
INSERT INTO subcategories (id, category_id, code_full, title) VALUES
(10, 12, 'I10.0', 'Hipertensão essencial com complicações'),
(11, 13, 'I21.0', 'Infarto agudo do miocárdio da parede anterior'),
(12, 14, 'I63.9', 'Infarto cerebral, não especificado');

-- Cap. X (Respiratório)
INSERT INTO subcategories (id, category_id, code_full, title) VALUES
(13, 15, 'J10.1', 'Influenza com outras manifestações respiratórias'),
(14, 16, 'J18.9', 'Pneumonia, não especificada'),
(15, 18, 'J45.0', 'Asma predominantemente alérgica');

-- Cap. XI (Digestivo)
INSERT INTO subcategories (id, category_id, code_full, title) VALUES
(16, 19, 'K20.9', 'Esofagite, não especificada'),
(17, 20, 'K70.3', 'Hepatite alcoólica');

-- Cap. XII (Pele)
INSERT INTO subcategories (id, category_id, code_full, title) VALUES
(18, 21, 'L40.0', 'Psoríase vulgar');

-- Cap. XIII (Osteomuscular)
INSERT INTO subcategories (id, category_id, code_full, title) VALUES
(19, 22, 'M17.0', 'Gonartrose primária bilateral');

-- Cap. XIV (Geniturinário)
INSERT INTO subcategories (id, category_id, code_full, title) VALUES
(20, 23, 'N03.9', 'Síndrome nefrítica recorrente, não especificada'),
(21, 24, 'N12.9', 'Nefrite túbulo-intersticial, não especificada');

-- Cap. XV (Obstetrícia)
INSERT INTO subcategories (id, category_id, code_full, title) VALUES
(22, 25, 'O14.0', 'Pré-eclâmpsia moderada');

-- Cap. XVI (Perinatal)
INSERT INTO subcategories (id, category_id, code_full, title) VALUES
(23, 26, 'P02.0', 'Afecções devidas a placenta prévia');

-- Cap. XVII (Congênitas)
INSERT INTO subcategories (id, category_id, code_full, title) VALUES
(24, 27, 'Q90.9', 'Síndrome de Down, não especificada');

-- Cap. XVIII (Sintomas)
INSERT INTO subcategories (id, category_id, code_full, title) VALUES
(25, 28, 'R06.0', 'Dispneia'),
(26, 29, 'R50.9', 'Febre, não especificada');

-- Cap. XIX (Lesões/Intox.)
INSERT INTO subcategories (id, category_id, code_full, title) VALUES
(27, 30, 'S06.0', 'Concussão'),
(28, 31, 'T40.2', 'Intoxicação por outros opioides');

-- Cap. XX (Causas externas)
INSERT INTO subcategories (id, category_id, code_full, title) VALUES
(29, 32, 'V89.2', 'Outros acidentes de transporte especificados'),
(30, 33, 'X70.0', 'Lesão autoprovocada por enforcamento: residência');

-- Cap. XXI (Fatores)
INSERT INTO subcategories (id, category_id, code_full, title) VALUES
(31, 34, 'Z00.0', 'Exame geral sem queixa ou diagnóstico'),
(32, 35, 'Z20.9', 'Contato com doença transmissível, não especificada'),
(33, 36, 'Z76.9', 'Pessoa em contato com serviços de saúde, não especificada');

-- Cap. XXII (Especiais)
-- Observação: subcódigos U variam conforme diretrizes nacionais/temporais.
INSERT INTO subcategories (id, category_id, code_full, title) VALUES
(34, 37, 'U07.1', 'COVID-19, vírus identificado'),
(35, 37, 'U07.2', 'COVID-19, vírus não identificado');

COMMIT;

-- FIM DO SEED
