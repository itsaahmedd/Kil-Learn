<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/MySQLDatabase.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/User.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/Game.php");

$database = MySQLDatabase::getDefault();
$game = Game::getCurrent($database);

// If a game has not been started, or it has already finished, return
if (!isset($game) || !$game->isOngoing()) {
    header("Location: ../../dashboard");
    die();
}

$reason = $_GET["reason"];

if (!isset($reason)) {
    header("Location: ../../dashboard");
    die();
}

$round = $game->getCurrentRound($database);
$roundNumber = $game->getRoundNumber();
$score = $round?->getScore();

if ($reason == "guessed") {
    if (!$round->guessWasCorrect()) {
        header("Location: ../");
        die();
    }
    if (!$round->isOngoing()) {
        $reason = "timeout";
    }
}

if (!$game->nextRound($database)) {
    header("Location: ../game-over");
    die();
}

$reasonTitles = [
    "guessed" => "Guess correct!",
    "timeout" => "Ran out of time!"
]

?>

<!DOCTYPE html>
<html>

<head>
    <title>Round Over | Kil-Learn</title>
    <link rel="stylesheet" type="text/css" href="../../resources/stylesheets/default.css">
</head>

<body>
<header>
    <h1>Kil-Learn</h1>
</header>
<main>
    <div class="centred container">
        <h1 style="font-size: 30px;">Round <?php echo $roundNumber ?>/5 Over! <?php echo $reasonTitles[$reason] ?></h1>
        <h3>Score: <?php echo $score ?></h3>
        <div class="buttons">
            <a href="../../back-end/scripts/game/quit.php">
                <button class="quit-button">Quit</button>
            </a>
            <a href="../../play">
                <button class="login-button">Next Round</button>
            </a>
        </div>
    </div>
</main>
<footer>
    <p>&copy; UoM 2023 Z13</p>
</footer>
</body>

</html>