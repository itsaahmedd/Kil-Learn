<?php
ini_set('display_errors', 1);
extract($_POST);

$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/MySQLDatabase.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/User.php");

$username = $_POST["username"];
$password = $_POST["password"];

if (!isset($username) || !isset($password)) {
    header("Location: ../../sign-up");
    die();
}

$database = MySQLDatabase::getDefault();
$database->connect();
$result = $database->exists("User", SQLCondition::whereValueEquals("Username", $username));
if ($result->exists()) {
    $database->disconnect();
    header("Location: ../../sign-up/index.php?error=username-taken");
    die();
}

$user = new User(null, $username);
if (!$user->create(ConnectedDatabase::from($database), $password)) {
    $database->disconnect();
    header("Location: ../../sign-up/index.php?error=server");
    die();
}

$user->createLoginSession(ConnectedDatabase::from($database));
$database->disconnect();
header("Location: ../../dashboard");