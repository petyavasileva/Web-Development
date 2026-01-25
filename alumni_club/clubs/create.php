<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";
require_login();
require_once __DIR__ . "/../header.php";

$uid = current_user_id();
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $desc = trim($_POST["description"] ?? "");

    if ($name === "") {
        $error = "Името е задължително.";
    } else {
        $st = $conn->prepare("INSERT INTO clubs (name, description, owner_id) VALUES (?, ?, ?)");
        $st->bind_param("ssi", $name, $desc, $uid);
        $st->execute();

        $club_id = $conn->insert_id;

        // Owner става член автоматично
        $st2 = $conn->prepare("INSERT IGNORE INTO club_members (club_id, user_id) VALUES (?, ?)");
        $st2->bind_param("ii", $club_id, $uid);
        $st2->execute();

        flash_set("success", "Клубът е създаден успешно!");
        header("Location: /alumni_club/clubs/view.php?id=" . $club_id);
        exit;
    }
}
?>

<h2 class="page-title mb-3">Създай клуб</h2>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" class="card card-body" style="max-width:800px;">
  <div class="mb-3">
    <label class="form-label">Име на клуба</label>
    <input class="form-control" name="name" maxlength="150" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Описание</label>
    <textarea class="form-control" name="description" rows="4"></textarea>
  </div>

  <button class="btn btn-success">Създай</button>
  <a class="btn btn-link" href="/alumni_club/clubs/index.php">Назад</a>
</form>

<?php require_once __DIR__ . "/../footer.php"; ?>
