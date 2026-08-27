<?php
// Caminho base para o projeto
$base_path = dirname(__DIR__);

// Detectar o caminho correto para aparencia.js
function get_aparencia_path() {
    $current = dirname($_SERVER['PHP_SELF']);
    $depth = substr_count($current, '/') - 1;
    $path = '';
    for ($i = 0; $i < $depth; $i++) {
        $path .= '../';
    }
    return $path . 'configuracoes/aparencia.js';
}
?>