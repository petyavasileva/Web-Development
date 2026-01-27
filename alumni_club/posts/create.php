<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";
require_login();

$uid = current_user_id();
$user_name = current_user_name($conn);

$club_id = (int)($_GET["club_id"] ?? 0);
if ($club_id <= 0) exit("Невалиден club_id.");

$stClub = $conn->prepare("SELECT id, name FROM clubs WHERE id = ? LIMIT 1");
$stClub->bind_param("i", $club_id);
$stClub->execute();
$club = $stClub->get_result()->fetch_assoc();
if (!$club) exit("Клубът не е намерен.");

$st = $conn->prepare("SELECT 1 FROM club_members WHERE club_id = ? AND user_id = ? LIMIT 1");
$st->bind_param("ii", $club_id, $uid);
$st->execute();
$is_member = (bool)$st->get_result()->fetch_row();

if (!$is_member) {
    http_response_code(403);
    exit("Трябва да си член, за да публикуваш.");
}

$error = "";
$title = "";
$content = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();

    $title = trim((string)($_POST["title"] ?? ""));
    $content = trim((string)($_POST["content"] ?? ""));

    if ($title === "" || $content === "") {
        $error = "Заглавие и съдържание са задължителни.";
    } elseif (mb_strlen($title) > 200) {
        $error = "Заглавието е твърде дълго (макс. 200).";
    } else {
        $st2 = $conn->prepare("INSERT INTO posts (club_id, author_id, title, content) VALUES (?, ?, ?, ?)");
        $st2->bind_param("iiss", $club_id, $uid, $title, $content);
        $st2->execute();

        flash_set("success", "Постът е публикуван!");
        header("Location: /alumni_club/clubs/view.php?id=" . $club_id);
        exit;
    }
}

layout_header("Нов пост – " . e((string)$club["name"]), $uid, $user_name);
?>

<a class="btn btn--sm" href="/alumni_club/clubs/view.php?id=<?= (int)$club_id ?>">← Назад</a>
<div style="height:10px;"></div>

<h1 class="page__title">Нов пост</h1>
<div class="muted">Клуб: <strong><?= e((string)$club["name"]) ?></strong></div>

<div style="height:12px;"></div>

<?php if ($error): ?>
  <div class="toast toast--danger">
    <div class="toast__dot" aria-hidden="true"></div>
    <div class="toast__msg"><?= e($error) ?></div>
  </div>
<?php endif; ?>

<form method="post" class="card" style="max-width:900px;">
  <div class="card__pad">
    <?= csrf_field() ?>

    <div class="field">
      <label for="title">Заглавие</label>
      <input class="input" id="title" name="title" maxlength="200" value="<?= e($title) ?>" required>
    </div>

    <div style="height:12px;"></div>

    <div class="field">
      <label for="content">Съдържание</label>
      <textarea id="content" name="content" rows="10" required><?= e($content) ?></textarea>
    </div>

    <div style="height:16px;"></div>

    <button class="btn btn--primary" type="submit">Публикувай</button>
  </div>
</form>

<?php layout_footer(); ?>
