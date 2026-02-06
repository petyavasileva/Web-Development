<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";

$uid = current_user_id();
$user_name = $uid ? current_user_name($conn) : null;

layout_header("Alumni Club – СУ", $uid, $user_name);

?>

<section class="hero" style="
  background:
    linear-gradient(135deg, rgba(37,99,235,.92), rgba(124,58,237,.88)),
    radial-gradient(1200px 500px at 20% -20%, rgba(255,255,255,.25), transparent 60%);
">
  <div class="hero__inner">
    <h1 class="hero__title">Alumni Club – Софийски университет</h1>
    <p class="hero__text">
      Модерен портал за алумни общността – клубове, събития и комуникация на едно място.
    </p>

    <div class="hero__actions">
      <a class="btn btn--primary" href="<?= app_url('clubs/index.php') ?>">Клубове</a>
      <a class="btn btn--primary" href="<?= app_url('events/index.php') ?>">Събития</a>
      <a class="btn btn--primary" href="<?= app_url('chat/index.php') ?>">Чат</a>

      <?php if (!$uid): ?>
        <a class="btn btn--soft" href="<?= app_url('register.php') ?>">Регистрация</a>
        <a class="btn btn--soft" href="<?= app_url('login.php') ?>">Вход</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<h2 class="section__title">Портал</h2>

<div class="row">
  <a href="<?= app_url('clubs/index.php') ?>" class="card card--hover" style="
    display:block;
    background: linear-gradient(135deg, #2563eb, #1e40af);
    color:#fff;
  ">
    <div class="card__pad">
      <div class="badge" style="background:rgba(255,255,255,.22);">Клубове</div>
      <h3 style="margin:12px 0 6px; font-weight:900;">Общности и интереси</h3>
      <p style="opacity:.9; margin:0;">
        Присъединявай се към клубове и участвай активно.
      </p>
    </div>
  </a>

  <a href="<?= app_url('events/index.php') ?>" class="card card--hover" style="
    display:block;
    background: linear-gradient(135deg, #16a34a, #15803d);
    color:#fff;
  ">
    <div class="card__pad">
      <div class="badge" style="background:rgba(255,255,255,.22);">Събития</div>
      <h3 style="margin:12px 0 6px; font-weight:900;">Календар и активности</h3>
      <p style="opacity:.9; margin:0;">
        Следи и участвай в събитията на общността.
      </p>
    </div>
  </a>

  <a href="<?= app_url('chat/index.php') ?>" class="card card--hover" style="
    display:block;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color:#fff;
  ">
    <div class="card__pad">
      <div class="badge" style="background:rgba(255,255,255,.22);">Чат</div>
      <h3 style="margin:12px 0 6px; font-weight:900;">Комуникация</h3>
      <p style="opacity:.9; margin:0;">
        Общувай с други алумни в реално време.
      </p>
    </div>
  </a>
</div>

<?php layout_footer(); ?>
