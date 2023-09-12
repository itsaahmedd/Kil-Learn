<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/MySQLDatabase.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/User.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/Game.php");

$database = MySQLDatabase::getDefault();
$user = User::getLoggedInUser($database);

if (!isset($user)) {
    header("Location: ../../../login");
    die();
}

$game = new Game(null, $user, 0, new DateTime(), null);
$game->create($database);
$game->saveSessionData();
header("Location: ../../../play");
