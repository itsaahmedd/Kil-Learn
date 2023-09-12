<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/MySQLDatabase.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/User.php");

try {
    $difficulty = Difficulty::fromName($_GET["difficulty"] ?? "");
} catch (ValueError $e) {
    header("Location: ../../");
    die();
}

$database = MySQLDatabase::getDefault();
// Try to get logged in data from cookie data
$user = User::getLoggedInUser($database);
// Redirect to home page if not logged in
if (!isset($user)) {
    header("Location: ../../");
    die();
}

$user->setPreferredDifficulty($database, $difficulty);
header("Location: " . $_SERVER["HTTP_REFERER"]);