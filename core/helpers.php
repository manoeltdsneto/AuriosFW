<?php

/**
 * Registra um arquivo CSS para ser renderizado no layout.
 * Ordem será: bootstrap.min.css, depois os customizados.
 */
function addCss(string $path): void
{
    $GLOBALS['framework_css'][] = $path;
}

/**
 * Registra um arquivo JS para ser renderizado no layout.
 * Ordem será: jquery.min.js, bootstrap.bundle.min.js, depois os customizados.
 */
function addJs(string $path): void
{
    $GLOBALS['framework_js'][] = $path;
}

/**
 * Renderiza as tags <link> com CSS, na ordem correta.
 */
function renderCss(): void
{
    $css = array_merge(['bootstrap.min'], $GLOBALS['framework_css'] ?? []);
    $css = array_unique($css); // evita duplicatas

    foreach ($css as $file) {
        $path = "public/css/{$file}.css";
        $ver = file_exists($path) ? filemtime($path) : time();
        echo "<link rel=\"stylesheet\" href=\"/css/{$file}.css?v={$ver}\">\n";
    }
}

/**
 * Renderiza as tags <script> com JS, na ordem correta.
 */
function renderJs(): void
{
    $js = array_merge(['jquery.min', 'bootstrap.bundle.min'], $GLOBALS['framework_js'] ?? []);
    $js = array_unique($js); // evita duplicatas

    foreach ($js as $file) {
        $path = "public/js/{$file}.js";
        $ver = file_exists($path) ? filemtime($path) : time();
        echo "<script src=\"/js/{$file}.js?v={$ver}\"></script>\n";
    }
}
