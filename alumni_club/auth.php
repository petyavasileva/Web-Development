<?php
declare(strict_types=1);


if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    session_start();
}

function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        flash_set('warning', 'Трябва да влезеш в профила си.');
        header('Location: /alumni_club/login.php');
        exit;
    }
}

function current_user_id(): ?int {
    return empty($_SESSION['user_id']) ? null : (int)$_SESSION['user_id'];
}

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function flash_set(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_get(): ?array {
    if (empty($_SESSION['flash'])) return null;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['_csrf'];
}

function csrf_field(): string {
    $t = csrf_token();
    return '<input type="hidden" name="_csrf" value="' . e($t) . '">';
}

function csrf_verify(): void {
    $sent = (string)($_POST['_csrf'] ?? '');
    $good = (string)($_SESSION['_csrf'] ?? '');
    if ($sent === '' || $good === '' || !hash_equals($good, $sent)) {
        http_response_code(403);
        exit('Невалиден CSRF токен. Презареди страницата и опитай пак.');
    }
}

function current_user_name(mysqli $conn): ?string {
    $uid = current_user_id();
    if ($uid === null) return null;

    $st = $conn->prepare('SELECT name FROM users WHERE id = ? LIMIT 1');
    $st->bind_param('i', $uid);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    return $row ? (string)$row['name'] : null;
}

// -------------------- Layout helpers --------------------
function layout_header(string $title, ?int $uid = null, ?string $user_name = null, string $body_class = ''): void {
    $uid = $uid ?? current_user_id();
    $user_name = $user_name ?? '';

    $is_logged = ($uid !== null);
    $f = flash_get();
    ?>
<!doctype html>
<html lang="bg">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?></title>
  <link rel="stylesheet" href="/alumni_club/assets/theme.css">
  <link rel="stylesheet" href="/alumni_club/assets/style.css">
</head>
<body<?= $body_class !== '' ? ' class="' . e($body_class) . '"' : '' ?>>
<header class="nav">
  <div class="container nav__inner">
    <a class="brand" href="/alumni_club/index.php" aria-label="Начало">
      <span class="brand__mark" aria-hidden="true">SU</span>
      <span class="brand__text">Alumni Club</span>
    </a>

    <input class="nav__toggle" type="checkbox" id="navToggle" aria-label="Меню">
    <label class="nav__burger" for="navToggle" aria-hidden="true">
      <span></span><span></span><span></span>
    </label>

    <nav class="nav__links" aria-label="Основна навигация">
      <a class="nav__link" href="/alumni_club/clubs/index.php">Клубове</a>

      <a class="nav__link" href="/alumni_club/events/index.php">Събития</a>
      <a class="nav__link" href="/alumni_club/chat/index.php">Чат</a>

      <?php if ($is_logged): ?>
        <span class="nav__chip" title="Влязъл потребител"><?= e($user_name ?: 'Потребител') ?></span>
        <a class="btn btn--ghost" href="/alumni_club/logout.php">Изход</a>
      <?php else: ?>
        <a class="btn btn--primary" href="/alumni_club/login.php">Вход</a>
        <a class="btn btn--primary" href="/alumni_club/register.php">Регистрация</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="container page">
  <?php if ($f): ?>
    <div class="toast toast--<?= e($f['type']) ?>">
      <div class="toast__dot" aria-hidden="true"></div>
      <div class="toast__msg"><?= e($f['msg']) ?></div>
    </div>
  <?php endif; ?>
<?php
}

function layout_footer(): void {
    ?>
</main>

<footer class="footer">
  <div class="container footer__inner">
    <div class="footer__left">
      <div class="footer__brand">Alumni Club</div>
      <div class="footer__muted">Petya Vasileva &amp; Katerina Zaharieva ©2026
</div>
    </div>
    <div class="footer__right">
      <a class="footer__link" href="/alumni_club/clubs/index.php">Клубове</a>
      <a class="footer__link" href="/alumni_club/index.php">Начало</a>
    </div>
  </div>
</footer>
</body>
</html>
<?php
}
