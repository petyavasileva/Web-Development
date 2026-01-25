<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

$uid = current_user_id();

// Вземаме името (ако е логнат)
$user_name = null;
if ($uid !== null) {
    $st = $conn->prepare("SELECT name FROM users WHERE id = ? LIMIT 1");
    $st->bind_param("i", $uid);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    $user_name = $row ? $row["name"] : null;
}
?>
<!doctype html>
<html lang="bg">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Alumni Club – СУ</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/alumni_club/assets/theme.css">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mb-4">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="/alumni_club/index.php">
      <span class="badge bg-light text-dark">SU</span>
      Alumni Club
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <div class="navbar-nav ms-auto align-items-lg-center gap-2 mt-3 mt-lg-0">
        <a class="btn nav-pill btn-sm" href="/alumni_club/clubs/index.php">
          <i class="fa-solid fa-people-group me-1"></i> Клубове
        </a>

        <?php if ($uid): ?>
          <div class="d-flex align-items-center gap-2">
            <span class="text-white-50 small d-none d-lg-inline">
              <i class="fa-regular fa-user me-1"></i>
              <?= htmlspecialchars($user_name ?? "Потребител") ?>
            </span>
            <a class="btn btn-warning btn-sm" href="/alumni_club/logout.php">
              <i class="fa-solid fa-right-from-bracket me-1"></i> Изход
            </a>
          </div>
        <?php else: ?>
          <a class="btn btn-soft btn-sm" href="/alumni_club/login.php">
            <i class="fa-solid fa-right-to-bracket me-1"></i> Вход
          </a>
          <a class="btn btn-primary btn-sm" href="/alumni_club/register.php">
            <i class="fa-solid fa-user-plus me-1"></i> Регистрация
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<div class="container">

<?php if ($f = flash_get()): ?>
  <div class="alert alert-<?= htmlspecialchars($f["type"]) ?> shadow-sm">
    <?= htmlspecialchars($f["msg"]) ?>
  </div>
<?php endif; ?>
