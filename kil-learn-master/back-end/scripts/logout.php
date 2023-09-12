<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/MySQLDatabase.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/User.php");

$database = MySQLDatabase::getDefault();
$user = User::getLoggedInUser($database);

if ($user->logout($database)) {
    header("Location: ../../");
} else {
    header("Location: ../../dashboard");
}

