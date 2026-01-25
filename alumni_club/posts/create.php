<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";
require_login();
require_once __DIR__ . "/../header.php";

$uid = current_user_id();
$club_id = isset($_GET["club_id"]) ? (int)$_GET["club_id"] : 0;
if ($club_id <= 0) die("Невалиден club_id.");

// проверка дали е member
$st = $conn->prepare("SELECT 1 FROM club_members WHERE club_id = ? AND user_id = ? LIMIT 1");
$st->bind_param("ii", $club_id, $uid);
$st->execute();
$is_member = (bool)$st->get_result()->fetch_row();

if (!$is_member) {
    http_response_code(403);
    die("Трябва да си член, за да публикуваш.");
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");

    if ($title === "" || $content === "") {
        $error = "Заглавие и съдържание са задължителни.";
    } else {
        $st2 = $conn->prepare("INSERT INTO posts (club_id, author_id, title, content) VALUES (?, ?, ?, ?)");
        $st2->bind_param("iiss", $club_id, $uid, $title, $content);
        $st2->execute();

        flash_set("success", "Постът е публикуван!");
        header("Location: /alumni_club/clubs/view.php?id=" . $club_id);
        exit;
    }
}
?>

<h2 class="page-title mb-3">Нов пост</h2>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" class="card card-body" style="max-width:900px;">
  <div class="mb-3">
    <label class="form-label">Заглавие</label>
    <input class="form-control" name="title" maxlength="200" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Съдържание</label>
    <textarea class="form-control" name="content" rows="8" required></textarea>
  </div>

  <button class="btn btn-primary">Публикувай</button>
  <a class="btn btn-link" href="/alumni_club/clubs/view.php?id=<?= (int)$club_id ?>">Назад</a>
</form>

<?php require_once __DIR__ . "/../footer.php"; ?>
