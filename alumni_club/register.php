<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/header.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $pass = $_POST["password"] ?? "";

    if ($name === "" || $email === "" || $pass === "") {
        $error = "Всички полета са задължителни.";
    } elseif (strlen($pass) < 6) {
        $error = "Паролата трябва да е поне 6 символа.";
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        try {
            $st = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $st->bind_param("sss", $name, $email, $hash);
            $st->execute();

            flash_set("success", "Регистрацията е успешна! Влез в профила си.");
            header("Location: /alumni_club/login.php");
            exit;
        } catch (mysqli_sql_exception $e) {
            $error = "Този имейл вече съществува.";
        }
    }
}
?>

<h2 class="page-title mb-3">Регистрация</h2>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" class="card card-body" style="max-width:520px;">
  <div class="mb-3">
    <label class="form-label">Име</label>
    <input class="form-control" name="name" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Email</label>
    <input class="form-control" name="email" type="email" required>
  </div>

  <div class="mb-3">
    <label class="form-label">Парола (мин. 6 символа)</label>
    <input class="form-control" name="password" type="password" required>
  </div>

  <button class="btn btn-success">Регистрация</button>
</form>

<?php require_once __DIR__ . "/footer.php"; ?>
