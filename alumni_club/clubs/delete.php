<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";
require_login();

$uid = current_user_id();
$club_id = (int)($_POST["club_id"] ?? 0);
if ($club_id <= 0) die("Невалиден club_id.");

$st = $conn->prepare("SELECT owner_id FROM clubs WHERE id = ?");
$st->bind_param("i", $club_id);
$st->execute();
$row = $st->get_result()->fetch_assoc();
if (!$row) die("Клубът не е намерен.");

if ((int)$row["owner_id"] !== $uid) { http_response_code(403); die("Нямаш право."); }

$st2 = $conn->prepare("DELETE FROM clubs WHERE id = ?");
$st2->bind_param("i", $club_id);
$st2->execute();

header("Location: /alumni_club/clubs/index.php");
exit;
