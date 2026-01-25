<?php
require_once __DIR__ . "/auth.php";
session_destroy();
session_start();
$_SESSION["flash"] = ["type" => "info", "msg" => "Излезе от профила си."];
header("Location: /alumni_club/index.php");
exit;
