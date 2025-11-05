<?php
require_once __DIR__ . '/app/config.php';

function h(?string $s): string { return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$pdo = null;
$db_error = null;
try {
    $pdo = db_get_pdo();
    if (!db_is_ready($pdo)) {
        $db_error = 'Banco de dados não inicializado.';
    }
} catch (Throwable $e) {
    $db_error = 'Erro ao conectar ao MySQL: ' . $e->getMessage();
}

// Coleta parâmetros (via POST para não expor na URL)
$q = trim($_POST['q'] ?? '');
$blockId = isset($_POST['block']) ? (int)$_POST['block'] : null;

// Resultados
$results = [
    'categories'    => [],
    'subcategories' => [],
    'blocks'        => [],
    'chapters'      => [],
    'morbi'         => [],
];

$LIMIT = 200; // limitar para manter a página leve

if ($pdo && !$db_error && $q !== '') {
    $like = "%$q%";
    $likeCode = "$q%"; // prefixo para código

    // Categorias (código de 3 chars ou título, busca sem acento)
    $sqlCat = "
        SELECT c.id, c.code, c.title,
               b.id AS block_id, b.title AS block_title,
               ch.id AS chapter_id, ch.numeral AS chapter_numeral, ch.title AS chapter_title
        FROM categories c
        JOIN blocks b   ON c.block_id = b.id
        JOIN chapters ch ON b.chapter_id = ch.id
        WHERE c.code COLLATE utf8mb4_unicode_ci LIKE :code
           OR c.title COLLATE utf8mb4_unicode_ci LIKE :title
        ORDER BY c.code
        LIMIT $LIMIT
    ";
    $st = $pdo->prepare($sqlCat);
    $st->execute([':code' => $likeCode, ':title' => $like]);
    $results['categories'] = $st->fetchAll();

    // Subcategorias (código com ponto, ex.: E11.9, ou título; busca sem acento)
    $sqlSub = "
        SELECT s.id, s.code_full AS code, s.title,
               c.code AS parent_code, c.title AS parent_title,
               b.id AS block_id, b.title AS block_title,
               ch.id AS chapter_id, ch.numeral AS chapter_numeral, ch.title AS chapter_title
        FROM subcategories s
        JOIN categories c ON s.category_id = c.id
        JOIN blocks b     ON c.block_id = b.id
        JOIN chapters ch  ON b.chapter_id = ch.id
        WHERE s.code_full COLLATE utf8mb4_unicode_ci LIKE :code
           OR s.title      COLLATE utf8mb4_unicode_ci LIKE :title
        ORDER BY s.code_full
        LIMIT $LIMIT
    ";
    $st = $pdo->prepare($sqlSub);
    $st->execute([':code' => $likeCode, ':title' => $like]);
    $results['subcategories'] = $st->fetchAll();

    // Capítulos/blocos por título ou faixa de código (busca sem acento)
    $sqlBlk = "
        SELECT b.id, b.code_start, b.code_end, b.title,
               ch.id AS chapter_id, ch.numeral AS chapter_numeral, ch.title AS chapter_title
        FROM blocks b
        JOIN chapters ch ON b.chapter_id = ch.id
        WHERE b.title      COLLATE utf8mb4_unicode_ci LIKE :title
           OR b.code_start COLLATE utf8mb4_unicode_ci LIKE :code1
           OR b.code_end   COLLATE utf8mb4_unicode_ci LIKE :code2
        ORDER BY ch.id, b.code_start
        LIMIT $LIMIT
    ";
    $st = $pdo->prepare($sqlBlk);
    $st->execute([':title' => $like, ':code1' => $like, ':code2' => $like]);
    $results['blocks'] = $st->fetchAll();

    $sqlChap = "
        SELECT id, numeral, code_start, code_end, title
        FROM chapters
        WHERE title      COLLATE utf8mb4_unicode_ci LIKE :title
           OR code_start COLLATE utf8mb4_unicode_ci LIKE :code1
           OR code_end   COLLATE utf8mb4_unicode_ci LIKE :code2
        ORDER BY id
        LIMIT $LIMIT
    ";
    $st = $pdo->prepare($sqlChap);
    $st->execute([':title' => $like, ':code1' => $like, ':code2' => $like]);
    $results['chapters'] = $st->fetchAll();

    // Grupos de Morbidade (DATASUS) — buscar por código de grupo, descrição ou faixas CID
    try {
        $sqlMorbi = "
            SELECT id, chapter, group_code, description, cid10_codes
            FROM morbi_groups
            WHERE group_code LIKE :gcode
               OR description COLLATE utf8mb4_unicode_ci LIKE :gdesc
               OR cid10_codes LIKE :gcid
            ORDER BY group_code
            LIMIT $LIMIT
        ";
        $stm = $pdo->prepare($sqlMorbi);
        $stm->execute([
            ':gcode' => $likeCode,
            ':gdesc' => $like,
            ':gcid'  => $like,
        ]);
        $results['morbi'] = $stm->fetchAll();
    } catch (Throwable $e) {
        // tabela pode não existir ainda; ignorar
    }
}

// Listagem hierárquica de capítulos → blocos
$chapters = [];
if ($pdo && !$db_error) {
    $chapters = $pdo->query("SELECT id, numeral, code_start, code_end, title FROM chapters ORDER BY id")->fetchAll();
}

function fetch_blocks_for(PDO $pdo, int $chapterId): array {
    $st = $pdo->prepare("SELECT id, code_start, code_end, title FROM blocks WHERE chapter_id = :id ORDER BY code_start");
    $st->execute([':id' => $chapterId]);
    return $st->fetchAll();
}

function fetch_block_details(PDO $pdo, int $blockId): array {
    // categorias do bloco
    $sql = "
        SELECT c.id, c.code, c.title
        FROM categories c
        WHERE c.block_id = :bid
        ORDER BY c.code
    ";
    $st = $pdo->prepare($sql);
    $st->execute([':bid' => $blockId]);
    $categories = $st->fetchAll();

    // subcategorias por categoria
    $subsByCat = [];
    if ($categories) {
        $sqls = $pdo->prepare("SELECT id, code_full AS code, title FROM subcategories WHERE category_id = :cid ORDER BY code_full");
        foreach ($categories as $cat) {
            $sqls->execute([':cid' => $cat['id']]);
            $subsByCat[$cat['id']] = $sqls->fetchAll();
        }
    }

    return ['categories' => $categories, 'subcategories' => $subsByCat];
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CID-10 – Pesquisa</title>
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 24px; color: #222; }
    h1 { margin: 0 0 12px; font-size: 22px; }
    form.search { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
    input[type=text] { flex: 1 1 380px; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px; }
    button, .btn { padding: 10px 14px; border: 1px solid #0d6efd; background: #0d6efd; color: #fff; border-radius: 6px; cursor: pointer; font-size: 15px; }
    button:hover, .btn:hover { background: #0b5ed7; }
    .btn-outline { background: #fff; color: #0d6efd; border-color: #0d6efd; }
    .btn-outline:hover { background: #e7f1ff; }
    .btn-sm { padding: 4px 8px; font-size: 12px; border-radius: 4px; }
    .btn-secondary { background: #6c757d; border-color: #6c757d; }
    .btn-secondary:hover { background: #5c636a; }
    .note { background: #fff3cd; border: 1px solid #ffe69c; padding: 10px 12px; border-radius: 6px; margin: 12px 0; }
    .error { background: #fde2e1; border: 1px solid #f5c2c7; padding: 10px 12px; border-radius: 6px; color: #842029; }
    .section { margin-top: 20px; }
    .result-list { list-style: none; padding-left: 0; }
    .result-item { padding: 8px 10px; border-bottom: 1px solid #eee; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .result-main { flex: 1 1 auto; }
    .row-actions { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
    .muted { color: #666; font-size: 90%; }
    .grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
    @media (min-width: 1000px) { .grid { grid-template-columns: 2fr 1fr; } }
    details { margin: 6px 0; }
    summary { cursor: pointer; }
    .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; background: #eef; border: 1px solid #ccd; font-size: 12px; color: #334; }
    #copy-toast { position: fixed; right: 16px; bottom: 16px; background: #0d6efd; color: #fff; padding: 8px 12px; border-radius: 6px; box-shadow: 0 2px 10px rgba(0,0,0,.15); opacity: 0; transform: translateY(10px); transition: all .25s ease; pointer-events: none; }
    #copy-toast.show { opacity: 1; transform: translateY(0); }
  </style>
</head>
<body>
  <h1>CID-10 – Pesquisa e Listagem</h1>
  <form class="search" method="post" action="">
    <input type="text" name="q" value="<?= h($q) ?>" placeholder="Buscar por código (ex.: E11 ou E11.9) ou descrição (ex.: diabetes)">
    <button type="submit">Buscar</button>
    <?php if ($q !== '' || $blockId): ?>
      <button type="button" class="btn btn-secondary" onclick="goBack()">Voltar</button>
      <form method="post" action="" style="display:inline;">
        <button type="submit" class="btn btn-outline">Limpar</button>
      </form>
    <?php endif; ?>
  </form>

  <?php if ($db_error): ?>
    <div class="error">
      <?= h($db_error) ?><br>
      Dica: execute <code>sql/schema.sql</code> (cria a base <code>cid10</code> e tabelas) e depois <code>sql/seed.sql</code> (povoamento completo). Ambos estão na pasta <code>sql/</code>.
    </div>
  <?php endif; ?>

  <div class="grid">
    <div>
      <div class="section">
        <h2 style="margin:0 0 8px; font-size:18px;">Resultados da busca</h2>
        <?php if ($q === ''): ?>
          <div class="note">Digite um código (ex.: E11, J45, I21.0) ou um termo (ex.: diabetes, pneumonia) para pesquisar em categorias e subcategorias.</div>
        <?php elseif ($pdo && !$db_error): ?>
          <?php
            $countCat = count($results['categories']);
            $countSub = count($results['subcategories']);
            $countBlk = count($results['blocks']);
            $countChap = count($results['chapters']);
          ?>
          <div class="muted">Exibindo até <?= (int)$LIMIT ?> resultados por seção.</div>

          <h3 style="margin:14px 0 6px;">Categorias (<?= $countCat ?>)</h3>
          <ul class="result-list">
            <?php foreach ($results['categories'] as $row): ?>
              <li class="result-item">
                <div class="result-main">
                  <strong><?= h($row['code']) ?></strong> — <?= h($row['title']) ?><br>
                  <span class="muted">Capítulo <?= h($row['chapter_numeral']) ?>: <?= h($row['chapter_title']) ?> · Bloco: <?= h($row['block_title']) ?></span>
                </div>
                <div class="row-actions">
                  <button type="button" class="btn btn-outline btn-sm btn-copy" data-copy="<?= h($row['code'].' - '.$row['title']) ?>">Copiar</button>
                </div>
              </li>
            <?php endforeach; ?>
            <?php if ($countCat === 0): ?><li class="result-item muted">Nenhuma categoria encontrada.</li><?php endif; ?>
          </ul>

          <h3 style="margin:14px 0 6px;">Subcategorias (<?= $countSub ?>)</h3>
          <ul class="result-list">
            <?php foreach ($results['subcategories'] as $row): ?>
              <li class="result-item">
                <div class="result-main">
                  <strong><?= h($row['code']) ?></strong> — <?= h($row['title']) ?><br>
                  <span class="muted">Categoria <?= h($row['parent_code']) ?> · Capítulo <?= h($row['chapter_numeral']) ?>: <?= h($row['chapter_title']) ?> · Bloco: <?= h($row['block_title']) ?></span>
                </div>
                <div class="row-actions">
                  <button type="button" class="btn btn-outline btn-sm btn-copy" data-copy="<?= h($row['code'].' - '.$row['title']) ?>">Copiar</button>
                </div>
              </li>
            <?php endforeach; ?>
            <?php if ($countSub === 0): ?><li class="result-item muted">Nenhuma subcategoria encontrada.</li><?php endif; ?>
          </ul>

          <details open>
            <summary><strong>Capítulos e Blocos relacionados</strong> (Capítulos: <?= $countChap ?>, Blocos: <?= $countBlk ?>)</summary>
            <div style="margin-top:8px;">
              <h4 style="margin:8px 0 6px;">Capítulos</h4>
              <ul class="result-list">
                <?php foreach ($results['chapters'] as $row): ?>
                  <li class="result-item">
                    <div class="result-main">
                      <span class="badge">Capítulo <?= h($row['numeral']) ?></span>
                      <?= h($row['code_start']) ?>–<?= h($row['code_end']) ?> — <?= h($row['title']) ?>
                    </div>
                    <div class="row-actions">
                      <button type="button" class="btn btn-outline btn-sm btn-copy" data-copy="<?= h('Capítulo '.$row['numeral'].' '.$row['code_start'].'-'.$row['code_end'].' - '.$row['title']) ?>">Copiar</button>
                    </div>
                  </li>
                <?php endforeach; ?>
                <?php if ($countChap === 0): ?><li class="result-item muted">Nenhum capítulo relacionado.</li><?php endif; ?>
              </ul>
              <h4 style="margin:8px 0 6px;">Blocos</h4>
              <ul class="result-list">
                <?php foreach ($results['blocks'] as $row): ?>
                  <li class="result-item">
                    <div class="result-main">
                      <span class="badge">Capítulo <?= h($row['chapter_numeral']) ?></span>
                      <?= h($row['code_start']) ?>–<?= h($row['code_end']) ?> — <?= h($row['title']) ?>
                      <form method="post" action="" class="inline-form" style="display:inline; margin-left:8px;">
                        <input type="hidden" name="block" value="<?= (int)$row['id'] ?>">
                        <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= h($q) ?>"><?php endif; ?>
                        <button type="submit" class="btn btn-outline btn-sm">Ver categorias</button>
                      </form>
                    </div>
                    <div class="row-actions">
                      <button type="button" class="btn btn-outline btn-sm btn-copy" data-copy="<?= h($row['code_start'].'-'.$row['code_end'].' - '.$row['title']) ?>">Copiar</button>
                    </div>
                  </li>
                <?php endforeach; ?>
                <?php if ($countBlk === 0): ?><li class="result-item muted">Nenhum bloco relacionado.</li><?php endif; ?>
              </ul>
            </div>
          </details>

          <?php $countMorbi = count($results['morbi']); ?>
          <h3 style="margin:14px 0 6px;">Grupos de Morbidade (DATASUS) (<?= $countMorbi ?>)</h3>
          <ul class="result-list">
            <?php foreach ($results['morbi'] as $row): ?>
              <li class="result-item">
                <div class="result-main">
                  <span class="badge">Capítulo <?= h($row['chapter']) ?></span>
                  <strong><?= h($row['group_code']) ?></strong> — <?= h($row['description']) ?>
                  <span class="muted"> · CIDs: <?= h($row['cid10_codes']) ?></span>
                </div>
                <div class="row-actions">
                  <button type="button" class="btn btn-outline btn-sm btn-copy" data-copy="<?= h($row['group_code'].' - '.$row['description'].' ('.$row['cid10_codes'].')') ?>">Copiar</button>
                </div>
              </li>
            <?php endforeach; ?>
            <?php if ($countMorbi === 0): ?><li class="result-item muted">Nenhum grupo de morbidade encontrado.</li><?php endif; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <aside>
      <div class="section">
        <h2 style="margin:0 0 8px; font-size:18px;">Capítulos e Blocos</h2>
        <?php if ($pdo && !$db_error): ?>
          <?php foreach ($chapters as $ch): ?>
            <details>
              <summary>
                <strong>Capítulo <?= h($ch['numeral']) ?></strong>
                (<?= h($ch['code_start']) ?>–<?= h($ch['code_end']) ?>) — <?= h($ch['title']) ?>
              </summary>
              <?php $blocks = fetch_blocks_for($pdo, (int)$ch['id']); ?>
              <ul class="result-list" style="margin-left:8px;">
                <?php foreach ($blocks as $b): ?>
                  <li class="result-item">
                    <div class="result-main">
                      <?= h($b['code_start']) ?>–<?= h($b['code_end']) ?> — <?= h($b['title']) ?>
                      <form method="post" action="" class="inline-form" style="display:inline; margin-left:8px;">
                        <input type="hidden" name="block" value="<?= (int)$b['id'] ?>">
                        <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= h($q) ?>"><?php endif; ?>
                        <button type="submit" class="btn btn-outline btn-sm">Ver categorias</button>
                      </form>
                    </div>
                    <div class="row-actions">
                      <button type="button" class="btn btn-outline btn-sm btn-copy" data-copy="<?= h($b['code_start'].'-'.$b['code_end'].' - '.$b['title']) ?>">Copiar</button>
                    </div>
                  </li>
                <?php endforeach; ?>
                <?php if (!$blocks): ?><li class="result-item muted">Sem blocos.</li><?php endif; ?>
              </ul>
            </details>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <?php if ($pdo && !$db_error && $blockId): ?>
        <?php
          $infoBlk = $pdo->prepare("SELECT b.id, b.code_start, b.code_end, b.title, ch.numeral AS chapter_numeral, ch.title AS chapter_title
                                     FROM blocks b JOIN chapters ch ON b.chapter_id=ch.id WHERE b.id=:id");
          $infoBlk->execute([':id' => $blockId]);
          $blk = $infoBlk->fetch();
        ?>
        <?php if ($blk): ?>
          <div class="section">
            <h2 style="margin:0 0 8px; font-size:18px;">Bloco selecionado</h2>
            <div class="note">
              <div><strong>Capítulo <?= h($blk['chapter_numeral']) ?></strong> — <?= h($blk['chapter_title']) ?></div>
              <div><strong>Bloco</strong> <?= h($blk['code_start']) ?>–<?= h($blk['code_end']) ?> — <?= h($blk['title']) ?></div>
            </div>
            <?php $details = fetch_block_details($pdo, (int)$blk['id']); ?>
            <h3 style="margin:10px 0 6px;">Categorias e subcategorias</h3>
            <?php if ($details['categories']): ?>
              <ul class="result-list">
                <?php foreach ($details['categories'] as $cat): ?>
                  <li class="result-item">
                    <div class="result-main">
                      <strong><?= h($cat['code']) ?></strong> — <?= h($cat['title']) ?>
                    </div>
                    <div class="row-actions">
                      <button type="button" class="btn btn-outline btn-sm btn-copy" data-copy="<?= h($cat['code'].' - '.$cat['title']) ?>">Copiar</button>
                    </div>
                    <?php $subs = $details['subcategories'][$cat['id']] ?? []; ?>
                    <?php if ($subs): ?>
                      <ul class="result-list" style="margin-left:16px;">
                        <?php foreach ($subs as $s): ?>
                          <li class="result-item">
                            <div class="result-main"><span class="badge">sub</span> <strong><?= h($s['code']) ?></strong> — <?= h($s['title']) ?></div>
                            <div class="row-actions">
                              <button type="button" class="btn btn-outline btn-sm btn-copy" data-copy="<?= h($s['code'].' - '.$s['title']) ?>">Copiar</button>
                            </div>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div class="muted">Sem categorias para este bloco.</div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </aside>
  </div>

  <div id="copy-toast">Copiado!</div>

  <script>
    function goBack() {
      if (window.history.length > 1) {
        window.history.back();
      } else {
        window.location.href = '?';
      }
    }

    function showToast() {
      const el = document.getElementById('copy-toast');
      if (!el) return;
      el.classList.add('show');
      clearTimeout(showToast._t);
      showToast._t = setTimeout(() => el.classList.remove('show'), 1200);
    }

    async function copyText(text) {
      try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
          await navigator.clipboard.writeText(text);
        } else {
          const ta = document.createElement('textarea');
          ta.value = text;
          ta.style.position = 'fixed';
          ta.style.left = '-9999px';
          document.body.appendChild(ta);
          ta.focus();
          ta.select();
          document.execCommand('copy');
          document.body.removeChild(ta);
        }
        showToast();
      } catch (e) {
        alert('Falha ao copiar');
      }
    }

    document.addEventListener('click', function(ev){
      const btn = ev.target.closest('.btn-copy');
      if (!btn) return;
      const text = btn.getAttribute('data-copy') || '';
      if (text) copyText(text);
    });
  </script>
</body>
</html>
