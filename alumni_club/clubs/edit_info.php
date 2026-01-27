<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";
require_login();

$uid = current_user_id();
$user_name = current_user_name($conn);

$club_id = (int)($_GET["id"] ?? 0);
if ($club_id <= 0) exit("Невалиден id.");

$st = $conn->prepare("SELECT * FROM clubs WHERE id = ? LIMIT 1");
$st->bind_param("i", $club_id);
$st->execute();
$club = $st->get_result()->fetch_assoc();
if (!$club) exit("Клубът не е намерен.");

if ((int)$club["owner_id"] !== $uid) {
    http_response_code(403);
    exit("Нямаш право да редактираш този клуб.");
}

$error = "";
$name = (string)$club["name"];
$description = (string)$club["description"];
$info = (string)($club["info"] ?? "");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();

    $name = trim((string)($_POST["name"] ?? ""));
    $description = trim((string)($_POST["description"] ?? ""));
    $info = trim((string)($_POST["info"] ?? ""));

    if ($name === "" || $description === "") {
        $error = "Име и описание са задължителни.";
    } else {
        $st2 = $conn->prepare("UPDATE clubs SET name = ?, description = ?, info = ? WHERE id = ?");
        $st2->bind_param("sssi", $name, $description, $info, $club_id);
        $st2->execute();

        flash_set("success", "Клубът е обновен.");
        header("Location: /alumni_club/clubs/view.php?id=" . $club_id);
        exit;
    }
}

layout_header("Редакция – " . e($club["name"]), $uid, $user_name);
?>

<a class="btn btn--sm" href="/alumni_club/clubs/view.php?id=<?= (int)$club_id ?>">← Назад</a>
<div style="height:10px;"></div>

<h1 class="page__title">Редакция на клуба</h1>

<?php if ($error): ?>
  <div class="toast toast--danger">
    <div class="toast__dot" aria-hidden="true"></div>
    <div class="toast__msg"><?= e($error) ?></div>
  </div>
<?php endif; ?>

<form method="post" class="card" style="max-width:820px;">
  <div class="card__pad">
    <?= csrf_field() ?>

    <div class="field">
      <label for="name">Име</label>
      <input class="input" id="name" name="name" maxlength="150" value="<?= e($name) ?>" required>
    </div>

    <div style="height:12px;"></div>

    <div class="field">
      <label for="description">Описание</label>
      <textarea id="description" name="description" required><?= e($description) ?></textarea>
    </div>

    <div style="height:12px;"></div>

    <div class="field">
      <label for="info">Официална информация</label>
      <textarea id="info" name="info"><?= e($info) ?></textarea>
    </div>

    <div style="height:16px;"></div>

    <button class="btn btn--primary" type="submit">Запази</button>
    <a class="btn" href="/alumni_club/clubs/view.php?id=<?= (int)$club_id ?>" style="margin-left:8px;">Отказ</a>
  </div>
</form>

<?php layout_footer(); ?>
