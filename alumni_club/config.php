<?php
declare(strict_types=1);


$__app_dir = realpath(__DIR__) ?: __DIR__;
$__doc_root = isset($_SERVER['DOCUMENT_ROOT'])
    ? (realpath((string)$_SERVER['DOCUMENT_ROOT']) ?: (string)$_SERVER['DOCUMENT_ROOT'])
    : null;


$APP_BASE = '';
if ($__doc_root !== null) {
    $app_dir_norm = str_replace('\\', '/', $__app_dir);
    $doc_root_norm = rtrim(str_replace('\\', '/', $__doc_root), '/');

    if (str_starts_with($app_dir_norm, $doc_root_norm)) {
        $rel = substr($app_dir_norm, strlen($doc_root_norm));
        $APP_BASE = rtrim($rel, '/');
    }
}


function app_url(string $path = ''): string {
    global $APP_BASE;
    $path = ltrim($path, '/');

    if ($APP_BASE === '') {
        return '/' . $path;
    }
    return $APP_BASE . '/' . $path;
}

function redirect(string $path): void {
    header('Location: ' . app_url($path));
    exit;
}
