<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";
require_once __DIR__ . "/../header.php";

$club_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($club_id <= 0) { http_response_code(400); die("Невалиден id."); }

$uid = current_user_id();

function club_cover(int $club_id): string {
  return "https://source.unsplash.com/featured/1400x700/?sofia,university,campus&sig=" . $club_id;
}

$st = $conn->prepare("SELECT * FROM clubs WHERE id = ?");
$st->bind_param("i", $club_id);
$st->execute();
$club = $st->get_result()->fetch_assoc();
if (!$club) { http_response_code(404); die("Клубът не е намерен."); }

$is_owner = ($uid !== null && (int)$club["owner_id"] === $uid);

// member?
$is_member = false;
if ($uid !== null) {
    $st2 = $conn->prepare("SELECT 1 FROM club_members WHERE club_id = ? AND user_id = ? LIMIT 1");
    $st2->bind_param("ii", $club_id, $uid);
    $st2->execute();
    $is_member = (bool)$st2->get_result()->fetch_row();
}

// members count + last 10
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

// posts pagination
$page = max(1, (int)($_GET["page"] ?? 1));
$per_page = 8;
$offset = ($page - 1) * $per_page;

$stPC = $conn->prepare("SELECT COUNT(*) AS cnt FROM posts WHERE club_id = ?");
$stPC->bind_param("i", $club_id);
$stPC->execute();
$total_posts = (int)($stPC->get_result()->fetch_assoc()["cnt"] ?? 0);
$total_pages = max(1, (int)ceil($total_posts / $per_page));

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
?>

<a class="btn btn-link px-0" href="/alumni_club/clubs/index.php">
  <i class="fa-solid fa-arrow-left me-1"></i> Всички клубове
</a>

<div class="cover mb-3">
  <img src="<?= htmlspecialchars(club_cover($club_id)) ?>" alt="club cover">
</div>

<div class="card mb-3">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
      <div>
        <h2 class="fw-bold mb-1"><?= htmlspecialchars($club["name"]) ?></h2>
        <div class="card-muted">
          <i class="fa-regular fa-calendar me-1"></i> Създаден: <?= htmlspecialchars($club["created_at"]) ?>
        </div>
      </div>

      <div class="d-flex gap-2 flex-wrap">
        <?php if ($uid === null): ?>
          <span class="badge bg-secondary"><i class="fa-solid fa-lock me-1"></i> Влез за Join/Постове</span>
        <?php else: ?>
          <?php if ($is_member): ?>
            <form method="post" action="/alumni_club/clubs/leave.php" class="m-0">
              <input type="hidden" name="club_id" value="<?= (int)$club_id ?>">
              <button class="btn btn-outline-danger btn-sm">
                <i class="fa-solid fa-door-open me-1"></i> Leave
              </button>
            </form>
          <?php else: ?>
            <form method="post" action="/alumni_club/clubs/join.php" class="m-0">
              <input type="hidden" name="club_id" value="<?= (int)$club_id ?>">
              <button class="btn btn-primary btn-sm">
                <i class="fa-solid fa-right-to-bracket me-1"></i> Join
              </button>
            </form>
          <?php endif; ?>
        <?php endif; ?>

        <?php if ($is_owner): ?>
          <a class="btn btn-soft btn-sm" href="/alumni_club/clubs/edit_info.php?id=<?= (int)$club_id ?>">
            <i class="fa-solid fa-pen me-1"></i> Edit info
          </a>
          <form method="post" action="/alumni_club/clubs/delete.php" class="m-0"
                onsubmit="return confirm('Сигурна ли си, че искаш да изтриеш клуба?');">
            <input type="hidden" name="club_id" value="<?= (int)$club_id ?>">
            <button class="btn btn-outline-dark btn-sm">
              <i class="fa-solid fa-trash me-1"></i> Delete
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <div class="row g-2 mt-3">
      <div class="col-md-3">
        <div class="kpi">
          <div class="num"><?= $members_count ?></div>
          <div class="lbl">Членове</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="kpi">
          <div class="num"><?= $total_posts ?></div>
          <div class="lbl">Постове</div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="kpi">
          <div class="num"><i class="fa-solid fa-circle-info me-1"></i> Официално</div>
          <div class="lbl">Информацията се редактира само от owner.</div>
        </div>
      </div>
    </div>

    <hr>

    <h6 class="fw-bold">Описание</h6>
    <div class="card-muted mb-3"><?= nl2br(htmlspecialchars($club["description"] ?? "")) ?></div>

    <h6 class="fw-bold">Официална информация</h6>
    <div class="card-muted mb-3"><?= nl2br(htmlspecialchars($club["info"] ?? "")) ?></div>

    <h6 class="fw-bold">Последни членове</h6>
    <div class="d-flex flex-wrap gap-2">
      <?php while($m = $members->fetch_assoc()): ?>
        <span class="badge badge-soft"><?= htmlspecialchars($m["name"]) ?></span>
      <?php endwhile; ?>
      <?php if ($members_count === 0): ?>
        <span class="card-muted">Още няма членове.</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-2">
  <h4 class="fw-bold mb-0">Постове</h4>
  <?php if ($uid !== null && $is_member): ?>
    <a class="btn btn-primary btn-sm" href="/alumni_club/posts/create.php?club_id=<?= (int)$club_id ?>">
      <i class="fa-solid fa-plus me-1"></i> Нов пост
    </a>
  <?php endif; ?>
</div>

<?php if ($uid !== null && !$is_member): ?>
  <div class="alert alert-info">Трябва да си член, за да публикуваш постове.</div>
<?php endif; ?>

<?php while ($p = $posts->fetch_assoc()): ?>
  <div class="card card-hover mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between flex-wrap gap-2">
        <div>
          <h5 class="fw-bold mb-1"><?= htmlspecialchars($p["title"]) ?></h5>
          <div class="card-muted">
            <i class="fa-regular fa-user me-1"></i> <?= htmlspecialchars($p["author_name"]) ?>
            <span class="mx-2">•</span>
            <i class="fa-regular fa-clock me-1"></i> <?= htmlspecialchars($p["created_at"]) ?>
          </div>
        </div>

        <?php if ($uid !== null && (int)$p["author_id"] === $uid): ?>
          <form method="post" action="/alumni_club/posts/delete.php" class="m-0"
                onsubmit="return confirm('Да изтрия ли поста?');">
            <input type="hidden" name="post_id" value="<?= (int)$p["id"] ?>">
            <input type="hidden" name="club_id" value="<?= (int)$club_id ?>">
            <button class="btn btn-outline-danger btn-sm">
              <i class="fa-solid fa-trash me-1"></i> Изтрий
            </button>
          </form>
        <?php endif; ?>
      </div>

      <hr>
      <div class="post-content"><?= htmlspecialchars($p["content"]) ?></div>
    </div>
  </div>
<?php endwhile; ?>

<?php if ($total_pages > 1): ?>
  <nav class="mt-3">
    <ul class="pagination">
      <?php for($i=1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?= ($i === $page) ? "active" : "" ?>">
          <a class="page-link" href="/alumni_club/clubs/view.php?id=<?= (int)$club_id ?>&page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
    </ul>
  </nav>
<?php endif; ?>

<?php require_once __DIR__ . "/../footer.php"; ?>
