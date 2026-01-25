<?php
require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";
require_once __DIR__ . "/../header.php";

$uid = current_user_id();
$q = trim($_GET["q"] ?? "");
$only_mine = isset($_GET["mine"]) && $_GET["mine"] === "1";

// helper: cover image per club id (Unsplash)
function club_cover(int $club_id): string {
  // различни картинки по id (без да качваш файлове)
  return "https://source.unsplash.com/featured/1200x600/?university,students&sig=" . $club_id;
}

if ($only_mine && $uid !== null) {
    $like = "%" . $q . "%";
    $st = $conn->prepare("
        SELECT c.*,
        (SELECT COUNT(*) FROM club_members cm WHERE cm.club_id = c.id) AS members_count
        FROM clubs c
        JOIN club_members cm2 ON cm2.club_id = c.id AND cm2.user_id = ?
        WHERE c.name LIKE ?
        ORDER BY c.created_at DESC
    ");
    $st->bind_param("is", $uid, $like);
    $st->execute();
    $res = $st->get_result();
} else {
    if ($q !== "") {
        $like = "%" . $q . "%";
        $st = $conn->prepare("
            SELECT c.*,
            (SELECT COUNT(*) FROM club_members cm WHERE cm.club_id = c.id) AS members_count
            FROM clubs c
            WHERE c.name LIKE ?
            ORDER BY c.created_at DESC
        ");
        $st->bind_param("s", $like);
        $st->execute();
        $res = $st->get_result();
    } else {
        $res = $conn->query("
            SELECT c.*,
            (SELECT COUNT(*) FROM club_members cm WHERE cm.club_id = c.id) AS members_count
            FROM clubs c
            ORDER BY c.created_at DESC
        ");
    }
}
?>

<div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
  <div>
    <h2 class="mb-1 fw-bold">Клубове</h2>
    <div class="card-muted">Търси клубове, виж детайли и се включи.</div>
  </div>
  <a class="btn btn-primary" href="/alumni_club/clubs/create.php">
    <i class="fa-solid fa-plus me-1"></i> Създай клуб
  </a>
</div>

<form class="card card-body mb-4" method="get">
  <div class="row g-2 align-items-center">
    <div class="col-md-7">
      <div class="input-group">
        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
        <input class="form-control" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Търси клуб по име...">
      </div>
    </div>
    <div class="col-md-3">
      <div class="form-check mt-1">
        <input class="form-check-input" type="checkbox" name="mine" value="1" id="mine"
               <?= $only_mine ? "checked" : "" ?> <?= $uid ? "" : "disabled" ?>>
        <label class="form-check-label" for="mine">Само моите клубове</label>
      </div>
      <?php if (!$uid): ?><div class="card-muted">Влез, за да активираш.</div><?php endif; ?>
    </div>
    <div class="col-md-2 d-grid">
      <button class="btn btn-soft"><i class="fa-solid fa-filter me-1"></i> Филтър</button>
    </div>
  </div>
</form>

<div class="row g-3">
<?php while ($c = $res->fetch_assoc()): ?>
  <div class="col-md-6">
    <div class="card card-hover h-100">
      <div class="cover">
        <img src="<?= htmlspecialchars(club_cover((int)$c["id"])) ?>" alt="club cover">
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between gap-2">
          <div>
            <h5 class="mb-1 fw-bold"><?= htmlspecialchars($c["name"]) ?></h5>
            <div class="card-muted">
              <i class="fa-solid fa-users me-1"></i> <?= (int)$c["members_count"] ?> членове
              <span class="mx-2">•</span>
              <i class="fa-regular fa-clock me-1"></i> <?= htmlspecialchars($c["created_at"]) ?>
            </div>
          </div>
          <a class="btn btn-primary btn-sm align-self-start" href="/alumni_club/clubs/view.php?id=<?= (int)$c["id"] ?>">
            Отвори <i class="fa-solid fa-arrow-right ms-1"></i>
          </a>
        </div>

        <hr>
        <div class="card-muted"><?= nl2br(htmlspecialchars($c["description"] ?? "")) ?></div>
      </div>
    </div>
  </div>
<?php endwhile; ?>
</div>

<?php require_once __DIR__ . "/../footer.php"; ?>
