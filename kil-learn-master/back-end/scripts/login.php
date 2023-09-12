<?php
extract($_POST);

$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/MySQLDatabase.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/User.php");

$username = $_POST["username"];
$password = $_POST["password"];

if (!isset($username) || !isset($password)) {
    header("Location: ../../login");
    die();
}

$database = MySQLDatabase::getDefault();
$user = User::fromUsername($database, $username);

if (!isset($user) || !$user->authenticate($database, $password)) {
    header("Location: ../../login/index.php?error=username-password-invalid");
    die();
}

$user->createLoginSession($database);

header("Location: ../../dashboard");