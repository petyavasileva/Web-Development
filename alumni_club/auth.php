<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login(): void {
    if (empty($_SESSION["user_id"])) {
        $_SESSION["flash"] = ["type" => "warning", "msg" => "Трябва да влезеш в профила си."];
        header("Location: /alumni_club/login.php");
        exit;
    }
}

function current_user_id(): ?int {
    return empty($_SESSION["user_id"]) ? null : (int)$_SESSION["user_id"];
}

function flash_set(string $type, string $msg): void {
    $_SESSION["flash"] = ["type" => $type, "msg" => $msg];
}

function flash_get(): ?array {
    if (empty($_SESSION["flash"])) return null;
    $f = $_SESSION["flash"];
    unset($_SESSION["flash"]);
    return $f;
}
