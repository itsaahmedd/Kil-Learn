<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/MySQLDatabase.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/Game.php");

$highScores = Game::getHighScores(MySQLDatabase::getDefault());
uasort($highScores, function ($a, $b) {
    return $b - $a;
});

?>

<!DOCTYPE html>
<html>
<head>
    <title>Leaderboard | Kil-Learn</title>
    <link rel="stylesheet" type="text/css" href="../resources/stylesheets/default.css">
    <style>
        body {
            background-image: url(../resources/images/settings/background.jpg);
        }
    </style>
</head>
<body>
<header>
    <h1>Kil-Learn</h1>
</header>
<main>
    <div class="container">
        <h1>Leaderboard</h1>
        <form class="styled-form">
            <div class="form-container">
                <table>
                    <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Username</th>
                        <th>High Score</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $rank = 1;
                    foreach ($highScores as $username => $score) {
                        echo "<tr>";
                        echo "<td>" . $rank++ . "</td>";
                        echo "<td>" . $username . "</td>";
                        echo "<td>" . $score . "</td>";
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>

        </form>
        <button style="margin-top: 15px" onclick="window.history.back()">Back</button>
    </div>
</main>
<footer>
    <p>&copy; UoM 2023 Z13</p>
</footer>
</body>
</html>
