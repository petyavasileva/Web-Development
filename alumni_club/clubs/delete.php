<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";
require_login();
csrf_verify();

$uid = current_user_id();
$club_id = (int)($_POST["club_id"] ?? 0);
if ($club_id <= 0) exit("Невалиден club_id.");

$st = $conn->prepare("SELECT owner_id FROM clubs WHERE id = ? LIMIT 1");
$st->bind_param("i", $club_id);
$st->execute();
$row = $st->get_result()->fetch_assoc();
if (!$row) exit("Клубът не е намерен.");

if ((int)$row["owner_id"] !== $uid) {
    http_response_code(403);
    exit("Нямаш право да изтриеш този клуб.");
}

$conn->begin_transaction();
try {
    // delete posts
    $st1 = $conn->prepare("DELETE FROM posts WHERE club_id = ?");
    $st1->bind_param("i", $club_id);
    $st1->execute();

    // delete memberships
    $st2 = $conn->prepare("DELETE FROM club_members WHERE club_id = ?");
    $st2->bind_param("i", $club_id);
    $st2->execute();

    // delete club
    $st3 = $conn->prepare("DELETE FROM clubs WHERE id = ?");
    $st3->bind_param("i", $club_id);
    $st3->execute();

    $conn->commit();
    flash_set("info", "Клубът беше изтрит.");
    header("Location: /alumni_club/clubs/index.php");
    exit;
} catch (Throwable $e) {
    $conn->rollback();
    http_response_code(500);
    exit("Грешка при изтриване.");
}
