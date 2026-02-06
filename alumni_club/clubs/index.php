<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";

$uid = current_user_id();
$user_name = $uid ? current_user_name($conn) : null;

$q = trim((string)($_GET["q"] ?? ""));
$only_mine = isset($_GET["mine"]) && $_GET["mine"] === "1";

$params = [];
$sql = "
  SELECT c.*,
         (SELECT COUNT(*) FROM club_members cm WHERE cm.club_id = c.id) AS members_cnt,
         EXISTS(SELECT 1 FROM club_members cm2 WHERE cm2.club_id = c.id AND cm2.user_id = ?) AS is_member
  FROM clubs c
  WHERE 1=1
";
$params[] = $uid ?? 0;

$types = "i";

if ($q !== "") {
    $sql .= " AND (c.name LIKE ? OR c.description LIKE ?)";
    $like = "%" . $q . "%";
    $params[] = $like;
    $params[] = $like;
    $types .= "ss";
}

if ($only_mine && $uid !== null) {
    $sql .= " AND EXISTS(SELECT 1 FROM club_members cm3 WHERE cm3.club_id = c.id AND cm3.user_id = ?)";
    $params[] = $uid;
    $types .= "i";
} elseif ($only_mine && $uid === null) {
    $only_mine = false;
}

$sql .= " ORDER BY c.created_at DESC";

$st = $conn->prepare($sql);
$st->bind_param($types, ...$params);
$st->execute();
$clubs = $st->get_result();

layout_header("Клубове – Alumni Club", $uid, $user_name);
?>

<div style="display:flex; justify-content:space-between; align-items:flex-end; gap:12px; flex-wrap:wrap;">
  <div>
    <h1 class="page__title" style="margin-bottom:6px;">Клубове</h1>
    <div class="muted">Откривай общности и се включвай с 1 клик.</div>
  </div>

  <?php if ($uid): ?>
    <a class="btn btn--primary" href="<?= app_url('clubs/create.php') ?>">+ Създай клуб</a>
  <?php endif; ?>
</div>

<div style="height:14px;"></div>

<form method="get" class="card" style="margin-bottom:14px;">
  <div class="card__pad" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
    <input class="input" name="q" placeholder="Търси по име или описание…" value="<?= e($q) ?>" style="flex:1 1 260px;">
    <?php if ($uid): ?>
      <label class="badge" style="cursor:pointer; user-select:none;">
        <input type="checkbox" name="mine" value="1" <?= $only_mine ? "checked" : "" ?> style="margin:0 8px 0 0;">
        Само моите
      </label>
    <?php endif; ?>
    <button class="btn btn--soft" type="submit">Търси</button>
    <a class="btn" href="<?= app_url('clubs/index.php') ?>">Изчисти</a>
  </div>
</form>

<div class="row">
  <?php while ($c = $clubs->fetch_assoc()): ?>
    <?php
      $is_owner = ($uid !== null && (int)$c["owner_id"] === $uid);
      $is_member = (bool)$c["is_member"];
    ?>
    <article class="card card--hover">
      <div class="card__pad">
        <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start;">
          <div>
            <h3 style="margin:0 0 6px; font-weight:900; letter-spacing:-.2px;"><?= e((string)$c["name"]) ?></h3>
            <div class="muted small"><?= e(substr((string)$c["description"], 0, 160)) ?><?= (strlen((string)$c["description"]) > 160 ? "…" : "") ?></div>
          </div>
          <div class="badge"><?= (int)$c["members_cnt"] ?> член(а)</div>
        </div>

        <div style="height:12px;"></div>

        <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
          <a class="btn btn--sm" href="<?= app_url('clubs/view.php?id=' . (int)$c['id']) ?>">Отвори</a>

          <?php if ($uid): ?>
            <?php if ($is_member): ?>
              <form method="post" action="<?= app_url('clubs/leave.php') ?>" style="margin:0;">
                <?= csrf_field() ?>
                <input type="hidden" name="club_id" value="<?= (int)$c["id"] ?>">
                <button class="btn btn--sm btn--danger" type="submit">Leave</button>
              </form>
            <?php else: ?>
              <form method="post" action="<?= app_url('clubs/join.php') ?>" style="margin:0;">
                <?= csrf_field() ?>
                <input type="hidden" name="club_id" value="<?= (int)$c["id"] ?>">
                <button class="btn btn--sm btn--primary" type="submit">Join</button>
              </form>
            <?php endif; ?>

            <?php if ($is_owner): ?>
              <span class="badge badge--warning">owner</span>
            <?php endif; ?>
          <?php else: ?>
            <span class="badge">Влез за Join</span>
          <?php endif; ?>
        </div>
      </div>
    </article>
  <?php endwhile; ?>
</div>

<?php layout_footer(); ?>
