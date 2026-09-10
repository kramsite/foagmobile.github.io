<?php
/**
 * FOAG — COMPONENTE GLOBAL
 * Injeta aparencia.js e acessibilidade.js em TODAS as páginas.
 *
 * Funciona em qualquer profundidade de pasta, sem calcular errado.
 */

// ============================================
// CALCULA O CAMINHO RELATIVO ATÉ /configuracoes/
// ============================================

$foagBase = (function () {

    // Pega a URL atual, ex: /calend/calendario.php
    $script = $_SERVER['PHP_SELF'] ?? '';

    // Normaliza barras
    $script = str_replace('\\', '/', $script);

    // Pega só o diretório, ex: /calend
    $dir = dirname($script);

    // Remove barras no começo e no fim
    // /calend  → calend
    // /a/b     → a/b
    // /        → (vazio)
    $dir = trim($dir, '/');

    // Se estiver na raiz do projeto
    if ($dir === '') {
        return 'configuracoes/';
    }

    // Conta quantas PASTAS tem (split por /)
    // calend       → ['calend']         → 1 pasta
    // a/b          → ['a', 'b']         → 2 pastas
    // calend/foo   → ['calend', 'foo']  → 2 pastas
    $pastas = explode('/', $dir);
    $pastas = array_filter($pastas, fn($p) => $p !== '');
    $niveis = count($pastas);

    return str_repeat('../', $niveis) . 'configuracoes/';
})();
?>

<!-- ============================================
     APARÊNCIA GLOBAL
============================================ -->
<script src="<?= $foagBase ?>aparencia.js?v=5"></script>

<!-- ============================================
     ACESSIBILIDADE GLOBAL (VLibras)
============================================ -->
<script src="<?= $foagBase ?>acessibilidade.js?v=25" defer></script>