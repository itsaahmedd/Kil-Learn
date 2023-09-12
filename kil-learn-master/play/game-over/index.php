<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/MySQLDatabase.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/User.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/Game.php");

$database = MySQLDatabase::getDefault();
$game = Game::getCurrent($database);

// If a game has not been started, or it has not finished, return
if (!isset($game) || $game->isOngoing()) {
    header("Location: ../../dashboard");
    die();
}

// Destroy session data
session_destroy();

?>

<!DOCTYPE html>
<html>

<head>
    <title>Game Over | Kil-Learn</title>
    <link rel="stylesheet" type="text/css" href="../../resources/stylesheets/default.css">
</head>

<body>
    <header>
        <h1>Kil-Learn</h1>
    </header>
    <main>
        <div class="centred container">
            <h1 style="font-size: 45px;" style="text-decoration: underline;">Game Over!</h1>
            <h4>Total Score</h4>
            <h3><?php echo $game->getScore($database) ?></h3>
            <div class="buttons">
                <a href="../../back-end/scripts/game/start.php"><button class="login-button">Play Again</button></a>
                <a href="../../leaderboard"><button class="login-button">Leaderboard</button></a>
            </div>
        </div>
    </main>
    <footer>
        <p>&copy; UoM 2023 Z13</p>
    </footer>
</body>

</html>