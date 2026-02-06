<?php
require_once __DIR__ . "/auth.php";
session_destroy();
session_start();
flash_set("info", "Излезе от профила си.");
redirect('index.php');
exit;
