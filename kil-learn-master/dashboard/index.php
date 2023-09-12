<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/MySQLDatabase.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/User.php");

// Try to get logged in data from session or cookie data
$user = User::getLoggedInUser(MySQLDatabase::getDefault());
// Redirect to login page if not logged in
if (!isset($user)) {
    header("Location: ../login");
    die();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Kil-Learn</title>
    <link rel="stylesheet" type="text/css" href="../resources/stylesheets/default.css">
</head>
<body>
<header>
    <h1>Kil-Learn</h1>
</header>
<main>
    <div class="centred container">
        <h2>Explore the World of CompSci Students!</h2>
        <div class="buttons">
            <a href="../back-end/scripts/game/start.php"><button class="start-button">Start Game</button></a>
            <a href="../how-to-play"><button class="start-button">How to Play</button></a>
            <a href="../leaderboard"><button class="start-button">Leaderboard</button></a>
            <a href="../settings"><button class="start-button">Settings</button></a>
        </div>
    </div>
</main>
<footer>
    <p>&copy; UoM 2023 Z13</p>
</footer>
</body>
</html>