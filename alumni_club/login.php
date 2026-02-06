<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";

$uid = current_user_id();
$user_name = $uid ? current_user_name($conn) : null;

$error = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();

    $email = trim((string)($_POST["email"] ?? ""));
    $pass = (string)($_POST["password"] ?? "");

    $st = $conn->prepare("SELECT id, password, name FROM users WHERE email = ? LIMIT 1");
    $st->bind_param("s", $email);
    $st->execute();
    $user = $st->get_result()->fetch_assoc();

    if (!$user || !password_verify($pass, (string)$user["password"])) {
        $error = "Грешен email или парола.";
    } else {
        $_SESSION["user_id"] = (int)$user["id"];
        $_SESSION["username"] = $user["name"];
        flash_set("success", "Успешен вход!");
        redirect('clubs/index.php');
        exit;
    }
}

layout_header("Вход – Alumni Club", $uid, $user_name, 'auth');
?>

<h1 class="page__title">Вход</h1>

<?php if ($error): ?>
  <div class="toast toast--danger">
    <div class="toast__dot" aria-hidden="true"></div>
    <div class="toast__msg"><?= e($error) ?></div>
  </div>
<?php endif; ?>

<form method="post" class="card" style="max-width:560px;">
  <div class="card__pad">
    <?= csrf_field() ?>
    <div class="field">
      <label for="email">Email</label>
      <input class="input" id="email" name="email" type="email" value="<?= e($email) ?>" required>
    </div>

    <div style="height:12px;"></div>

    <div class="field">
      <label for="password">Парола</label>
      <input class="input" id="password" name="password" type="password" required>
    </div>

    <div style="height:16px;"></div>

    <button class="btn btn--primary" type="submit">Влез</button>
    <a class="btn" href="<?= app_url('register.php') ?>" style="margin-left:8px;">Регистрация</a>
  </div>
</form>

<?php layout_footer(); ?>
