<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/header.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $pass = $_POST["password"] ?? "";

    $st = $conn->prepare("SELECT id, password FROM users WHERE email = ? LIMIT 1");
    $st->bind_param("s", $email);
    $st->execute();
    $user = $st->get_result()->fetch_assoc();

    if (!$user || !password_verify($pass, $user["password"])) {
        $error = "Грешен email или парола.";
    } else {
        $_SESSION["user_id"] = (int)$user["id"];
        flash_set("success", "Успешен вход!");
        header("Location: /alumni_club/clubs/index.php");
        exit;
    }
}
?>

<h2 class="page-title mb-3">Вход</h2>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" class="card card-body" style="max-width:520px;">
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input class="form-control" name="email" type="email" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Парола</label>
    <input class="form-control" name="password" type="password" required>
  </div>

  <button class="btn btn-primary">Вход</button>
</form>

<?php require_once __DIR__ . "/footer.php"; ?>
