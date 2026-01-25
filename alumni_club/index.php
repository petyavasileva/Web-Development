<?php require_once __DIR__ . "/header.php"; ?>

<div class="hero mb-4">
  <div class="hero-inner">
    <h1 class="mb-2">Alumni Club – Софийски университет</h1>
    <p class="mb-4">
      Професионална платформа за алумни общността: клубове, членство, официална информация и постове.
    </p>

    <div class="d-flex flex-wrap gap-2">
      <a class="btn btn-primary" href="/alumni_club/clubs/index.php">
        <i class="fa-solid fa-compass me-1"></i> Разгледай клубовете
      </a>

      <?php if (!$uid): ?>
        <a class="btn btn-soft" href="/alumni_club/register.php">
          <i class="fa-solid fa-user-plus me-1"></i> Създай акаунт
        </a>
        <a class="btn nav-pill text-white" href="/alumni_club/login.php">
          <i class="fa-solid fa-right-to-bracket me-1"></i> Вход
        </a>
      <?php else: ?>
        <a class="btn btn-soft" href="/alumni_club/clubs/create.php">
          <i class="fa-solid fa-plus me-1"></i> Създай клуб
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card card-hover">
      <div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-2">
          <span class="badge bg-primary"><i class="fa-solid fa-people-group"></i></span>
          <h5 class="mb-0">Клубове</h5>
        </div>
        <div class="card-muted">Създавай и откривай клубове по интереси. Присъединяване с 1 клик.</div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card card-hover">
      <div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-2">
          <span class="badge bg-success"><i class="fa-solid fa-bullhorn"></i></span>
          <h5 class="mb-0">Постове</h5>
        </div>
        <div class="card-muted">Публикувай новини и инициативи. Само членове могат да пишат.</div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card card-hover">
      <div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-2">
          <span class="badge bg-warning text-dark"><i class="fa-solid fa-circle-info"></i></span>
          <h5 class="mb-0">Официална информация</h5>
        </div>
        <div class="card-muted">Само създателят (owner) редактира официалната информация за клуба.</div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/footer.php"; ?>
