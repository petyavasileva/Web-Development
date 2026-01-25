<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";
require_login();
require_once __DIR__ . "/../header.php";

$uid = current_user_id();
$club_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($club_id <= 0) die("Невалиден id.");

$st = $conn->prepare("SELECT * FROM clubs WHERE id = ?");
$st->bind_param("i", $club_id);
$st->execute();
$club = $st->get_result()->fetch_assoc();
if (!$club) die("Клубът не е намерен.");

if ((int)$club["owner_id"] !== $uid) {
    http_response_code(403);
    die("Нямаш право да редактираш този клуб.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $info = trim($_POST["info"] ?? "");
    $st2 = $conn->prepare("UPDATE clubs SET info = ? WHERE id = ?");
    $st2->bind_param("si", $info, $club_id);
    $st2->execute();

    flash_set("success", "Информацията е обновена.");
    header("Location: /alumni_club/clubs/view.php?id=" . $club_id);
    exit;
}
?>

<h2 class="page-title mb-3">Редакция на информация (owner)</h2>

<form method="post" class="card card-body" style="max-width:900px;">
  <div class="mb-3">
    <label class="form-label">Официална информация</label>
    <textarea class="form-control" name="info" rows="8"><?= htmlspecialchars($club["info"] ?? "") ?></textarea>
  </div>
  <button class="btn btn-warning">Запази</button>
  <a class="btn btn-link" href="/alumni_club/clubs/view.php?id=<?= (int)$club_id ?>">Отказ</a>
</form>

<?php require_once __DIR__ . "/../footer.php"; ?>
