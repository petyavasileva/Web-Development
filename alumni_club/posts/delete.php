<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";
require_login();
csrf_verify();

$uid = current_user_id();
$post_id = (int)($_POST["post_id"] ?? 0);
$club_id = (int)($_POST["club_id"] ?? 0);

if ($post_id <= 0 || $club_id <= 0) exit("Невалидни данни.");

$st = $conn->prepare("SELECT author_id FROM posts WHERE id = ? LIMIT 1");
$st->bind_param("i", $post_id);
$st->execute();
$row = $st->get_result()->fetch_assoc();

if (!$row) exit("Постът не е намерен.");
if ((int)$row["author_id"] !== $uid) {
    http_response_code(403);
    exit("Нямаш право да изтриеш този пост.");
}

$st2 = $conn->prepare("DELETE FROM posts WHERE id = ?");
$st2->bind_param("i", $post_id);
$st2->execute();

flash_set("info", "Постът беше изтрит.");
header("Location: /alumni_club/clubs/view.php?id=" . $club_id);
exit;
