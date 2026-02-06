<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";
require_login();
csrf_verify();

$uid = current_user_id();
$club_id = (int)($_POST["club_id"] ?? 0);
if ($club_id <= 0) exit("Невалиден club_id.");

$st = $conn->prepare("INSERT IGNORE INTO club_members (club_id, user_id) VALUES (?, ?)");
$st->bind_param("ii", $club_id, $uid);
$st->execute();

flash_set("success", "Успешно се включи в клуба!");
redirect('clubs/view.php?id=' . $club_id);
