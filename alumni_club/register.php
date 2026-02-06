<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";

$uid = current_user_id();
$user_name = $uid ? current_user_name($conn) : null;

$error = "";
$name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();

    $name = trim((string)($_POST["name"] ?? ""));
    $email = trim((string)($_POST["email"] ?? ""));
    $pass = (string)($_POST["password"] ?? "");

    if ($name === "" || $email === "" || $pass === "") {
        $error = "Всички полета са задължителни.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Невалиден имейл адрес.";
    } elseif (strlen($pass) < 6) {
        $error = "Паролата трябва да е поне 6 символа.";
    } else {
        
        $chk = $conn->prepare("SELECT id FROM users WHERE name = ? LIMIT 1");
        $chk->bind_param("s", $name);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) {
            $error = "Това потребителско име вече е заето.";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);

            try {
            $st = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $st->bind_param("sss", $name, $email, $hash);
            $st->execute();

            flash_set("success", "Регистрацията е успешна! Влез в профила си.");
            redirect('login.php');
            exit;
            } catch (mysqli_sql_exception $e) {
                $error = "Този имейл вече съществува.";
            }
        }
    }
}

layout_header("Регистрация – Alumni Club", $uid, $user_name, 'auth');
?>

<h1 class="page__title">Регистрация</h1>

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
      <label for="name">Име</label>
      <input class="input" id="name" name="name" value="<?= e($name) ?>" required>
    </div>

    <div style="height:12px;"></div>

    <div class="field">
      <label for="email">Email</label>
      <input class="input" id="email" name="email" type="email" value="<?= e($email) ?>" required>
    </div>

    <div style="height:12px;"></div>

    <div class="field">
      <label for="password">Парола</label>
      <input class="input" id="password" name="password" type="password" minlength="6" required>
      <div class="help">Минимум 6 символа.</div>
    </div>

    <div style="height:16px;"></div>

    <button class="btn btn--primary" type="submit">Създай профил</button>
    <a class="btn" href="<?= app_url('login.php') ?>" style="margin-left:8px;">Имам акаунт</a>
  </div>
</form>

<?php layout_footer(); ?>
