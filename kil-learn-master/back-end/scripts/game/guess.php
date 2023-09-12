<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/MySQLDatabase.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/User.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/Room.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/Game.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/GameRound.php");

function invalid(): void
{
    header("Location: ../../../dashboard");
    die();
}

$roomName = $_GET["room"];
if (!isset($roomName)) {
    invalid();
}

$database = MySQLDatabase::getDefault();
$game = Game::getCurrent($database);
$round = $game?->getCurrentRound($database);

if (!isset($round)) {
    invalid();
}

if ($round->guess($roomName, $database)) {
    header("Location: ../../../play/round-over/index.php?reason=guessed");
    die();
}

header("Location: ../../../play");
