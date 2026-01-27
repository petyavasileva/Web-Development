<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";
require_login();
csrf_verify();

$uid = current_user_id();
$club_id = (int)($_POST["club_id"] ?? 0);
if ($club_id <= 0) exit("Невалиден club_id.");

$stO = $conn->prepare("SELECT owner_id FROM clubs WHERE id = ? LIMIT 1");
$stO->bind_param("i", $club_id);
$stO->execute();
$row = $stO->get_result()->fetch_assoc();
if (!$row) exit("Клубът не е намерен.");

if ((int)$row["owner_id"] === $uid) {
    flash_set("warning", "Owner не може да напусне собствения си клуб. (Можеш да го изтриеш.)");
    header("Location: /alumni_club/clubs/view.php?id=" . $club_id);
    exit;
}

$st = $conn->prepare("DELETE FROM club_members WHERE club_id = ? AND user_id = ?");
$st->bind_param("ii", $club_id, $uid);
$st->execute();

flash_set("info", "Напусна клуба.");
header("Location: /alumni_club/clubs/view.php?id=" . $club_id);
exit;
