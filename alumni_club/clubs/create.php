<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";
require_login();

$uid = current_user_id();
$user_name = current_user_name($conn);

$error = "";
$name = "";
$description = "";
$info = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();

    $name = trim((string)($_POST["name"] ?? ""));
    $description = trim((string)($_POST["description"] ?? ""));
    $info = trim((string)($_POST["info"] ?? ""));

    if ($name === "" || $description === "") {
        $error = "Име и описание са задължителни.";
    } elseif (mb_strlen($name) > 150) {
        $error = "Името е твърде дълго (макс. 150).";
    } else {
        $st = $conn->prepare("INSERT INTO clubs (name, description, owner_id, info) VALUES (?, ?, ?, ?)");
        $st->bind_param("ssis", $name, $description, $uid, $info);
        $st->execute();
        $club_id = $conn->insert_id;

        $st2 = $conn->prepare("INSERT IGNORE INTO club_members (club_id, user_id) VALUES (?, ?)");
        $st2->bind_param("ii", $club_id, $uid);
        $st2->execute();

        flash_set("success", "Клубът е създаден!");
        redirect('clubs/view.php?id=' . (int)$club_id);
    }
}

layout_header("Създай клуб – Alumni Club", $uid, $user_name);
?>

<a class="btn btn--sm" href="<?= app_url('clubs/index.php') ?>">← Назад</a>
<div style="height:10px;"></div>

<h1 class="page__title">Създай клуб</h1>

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
      <div class="help">Кратко — какво представлява клубът.</div>
    </div>

    <div style="height:12px;"></div>

    <div class="field">
      <label for="info">Официална информация (само owner може да редактира)</label>
      <textarea id="info" name="info"><?= e($info) ?></textarea>
      <div class="help">Напр. правила, контакти, линкове.</div>
    </div>

    <div style="height:16px;"></div>

    <button class="btn btn--primary" type="submit">Създай</button>
  </div>
</form>

<?php layout_footer(); ?>
