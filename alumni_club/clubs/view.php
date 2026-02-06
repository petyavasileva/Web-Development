<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";

$club_id = (int)($_GET["id"] ?? 0);
if ($club_id <= 0) { http_response_code(400); exit("Невалиден id."); }

$uid = current_user_id();
$user_name = $uid ? current_user_name($conn) : null;

$st = $conn->prepare("SELECT c.*, u.name AS owner_name FROM clubs c JOIN users u ON u.id = c.owner_id WHERE c.id = ? LIMIT 1");
$st->bind_param("i", $club_id);
$st->execute();
$club = $st->get_result()->fetch_assoc();
if (!$club) { http_response_code(404); exit("Клубът не е намерен."); }

$is_owner = ($uid !== null && (int)$club["owner_id"] === $uid);

$is_member = false;
if ($uid !== null) {
    $st2 = $conn->prepare("SELECT 1 FROM club_members WHERE club_id = ? AND user_id = ? LIMIT 1");
    $st2->bind_param("ii", $club_id, $uid);
    $st2->execute();
    $is_member = (bool)$st2->get_result()->fetch_row();
}

$stMC = $conn->prepare("SELECT COUNT(*) AS cnt FROM club_members WHERE club_id = ?");
$stMC->bind_param("i", $club_id);
$stMC->execute();
$members_count = (int)($stMC->get_result()->fetch_assoc()["cnt"] ?? 0);

$stM = $conn->prepare("
  SELECT u.name
  FROM club_members cm
  JOIN users u ON u.id = cm.user_id
  WHERE cm.club_id = ?
  ORDER BY cm.joined_at DESC
  LIMIT 10
");
$stM->bind_param("i", $club_id);
$stM->execute();
$members = $stM->get_result();

$page = max(1, (int)($_GET["page"] ?? 1));
$per_page = 8;
$offset = ($page - 1) * $per_page;

$stPC = $conn->prepare("SELECT COUNT(*) AS cnt FROM posts WHERE club_id = ?");
$stPC->bind_param("i", $club_id);
$stPC->execute();
$total_posts = (int)($stPC->get_result()->fetch_assoc()["cnt"] ?? 0);
$total_pages = max(1, (int)ceil(max(1,$total_posts) / $per_page)); // avoid div0

$stP = $conn->prepare("
  SELECT p.*, u.name AS author_name
  FROM posts p
  JOIN users u ON u.id = p.author_id
  WHERE p.club_id = ?
  ORDER BY p.created_at DESC
  LIMIT ? OFFSET ?
");
$stP->bind_param("iii", $club_id, $per_page, $offset);
$stP->execute();
$posts = $stP->get_result();

layout_header(e((string)$club["name"]) . " – Alumni Club", $uid, $user_name);
?>

<a class="btn btn--sm" href="<?= app_url('clubs/index.php') ?>">← Всички клубове</a>

<div style="height:12px;"></div>

<div class="cover">
  <div class="cover__inner">
    <div class="cover__chip">
      <div style="width:38px;height:38px;border-radius:14px;background:rgba(255,255,255,.18);display:grid;place-items:center;font-weight:900;">
        <?= e(mb_strtoupper(mb_substr((string)$club["name"], 0, 1))) ?>
      </div>
      <div>
        <strong><?= e((string)$club["name"]) ?></strong><br>
        <span class="tiny" style="opacity:.86;">owner: <?= e((string)$club["owner_name"]) ?> • създаден: <?= e((string)$club["created_at"]) ?></span>
      </div>
    </div>
  </div>
</div>

<div style="height:14px;"></div>

<section class="card">
  <div class="card__pad">
    <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap;">
      <div>
        <div class="row" style="gap:10px; margin:0;">
          <div class="badge"><?= (int)$members_count ?> член(а)</div>
          <div class="badge"><?= (int)$total_posts ?> пост(а)</div>
          <?php if ($is_owner): ?><div class="badge badge--warning">owner</div><?php endif; ?>
        </div>
      </div>

      <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <?php if (!$uid): ?>
          <span class="badge">Влез за Join/Постове</span>
          <a class="btn btn--primary btn--sm" href="<?= app_url('login.php') ?>">Вход</a>
        <?php else: ?>
          <?php if ($is_member): ?>
            <form method="post" action="<?= app_url('clubs/leave.php') ?>" style="margin:0;">
              <?= csrf_field() ?>
              <input type="hidden" name="club_id" value="<?= (int)$club_id ?>">
              <button class="btn btn--danger btn--sm" type="submit">Leave</button>
            </form>
          <?php else: ?>
            <form method="post" action="<?= app_url('clubs/join.php') ?>" style="margin:0;">
              <?= csrf_field() ?>
              <input type="hidden" name="club_id" value="<?= (int)$club_id ?>">
              <button class="btn btn--primary btn--sm" type="submit">Join</button>
            </form>
          <?php endif; ?>

          <?php if ($is_owner): ?>
            <a class="btn btn--soft btn--sm" href="<?= app_url('clubs/edit_info.php?id=' . (int)$club_id) ?>">Редакция</a>
            <form method="post" action="<?= app_url('clubs/delete.php') ?>" style="margin:0;"
                  onsubmit="return confirm('Сигурен ли си, че искаш да изтриеш клуба? Това изтрива и постовете/членствата.');">
              <?= csrf_field() ?>
              <input type="hidden" name="club_id" value="<?= (int)$club_id ?>">
              <button class="btn btn--sm" type="submit">Изтрий</button>
            </form>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <hr class="hr">

    <h2 class="section__title" style="margin-top:0;">Описание</h2>
    <div class="muted" style="white-space:pre-line; line-height:1.55;"><?= e((string)$club["description"]) ?></div>

    <div style="height:12px;"></div>

    <h2 class="section__title">Официална информация</h2>
    <div class="muted" style="white-space:pre-line; line-height:1.55;"><?= e((string)($club["info"] ?? "")) ?></div>

    <div style="height:12px;"></div>

    <h2 class="section__title">Последни членове</h2>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
      <?php $has = false; while($m = $members->fetch_assoc()): $has=true; ?>
        <span class="badge"><?= e((string)$m["name"]) ?></span>
      <?php endwhile; ?>
      <?php if (!$has): ?>
        <span class="muted">Още няма членове.</span>
      <?php endif; ?>
    </div>
  </div>
</section>

<div style="height:14px;"></div>

<div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
  <h2 class="section__title" style="margin:0;">Постове</h2>
  <?php if ($uid && $is_member): ?>
    <a class="btn btn--primary btn--sm" href="<?= app_url('posts/create.php?club_id=' . (int)$club_id) ?>">+ Нов пост</a>
  <?php endif; ?>
</div>

<?php if ($uid && !$is_member): ?>
  <div style="height:10px;"></div>
  <div class="toast toast--info">
    <div class="toast__dot" aria-hidden="true"></div>
    <div class="toast__msg">Трябва да си член, за да публикуваш.</div>
  </div>
<?php endif; ?>

<div style="height:10px;"></div>

<?php $any_posts = false; while ($p = $posts->fetch_assoc()): $any_posts = true; ?>
  <article class="card card--hover" style="margin-bottom:12px;">
    <div class="card__pad">
      <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:flex-start;">
        <div>
          <h3 style="margin:0 0 6px; font-weight:900;"><?= e((string)$p["title"]) ?></h3>
          <div class="muted tiny">от <?= e((string)$p["author_name"]) ?> • <?= e((string)$p["created_at"]) ?></div>
        </div>

        <?php if ($uid !== null && (int)$p["author_id"] === $uid): ?>
          <form method="post" action="<?= app_url('posts/delete.php') ?>" style="margin:0;"
                onsubmit="return confirm('Да изтрия ли поста?');">
            <?= csrf_field() ?>
            <input type="hidden" name="post_id" value="<?= (int)$p["id"] ?>">
            <input type="hidden" name="club_id" value="<?= (int)$club_id ?>">
            <button class="btn btn--danger btn--sm" type="submit">Изтрий</button>
          </form>
        <?php endif; ?>
      </div>

      <hr class="hr">
      <div class="post-content"><?= e((string)$p["content"]) ?></div>
    </div>
  </article>
<?php endwhile; ?>

<?php if (!$any_posts): ?>
  <div class="muted">Още няма постове. <?php if ($uid && $is_member): ?>Бъди първият!<?php endif; ?></div>
<?php endif; ?>

<?php if ($total_posts > $per_page): ?>
  <nav class="pager" aria-label="Страници">
    <?php for($i=1; $i <= (int)ceil($total_posts / $per_page); $i++): ?>
      <a href="<?= app_url('clubs/view.php?id=' . (int)$club_id . '&page=' . (int)$i) ?>" <?= $i===$page ? 'aria-current="page"' : '' ?>><?= $i ?></a>
    <?php endfor; ?>
  </nav>
<?php endif; ?>

<?php layout_footer(); ?>
