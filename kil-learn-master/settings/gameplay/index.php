<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/MySQLDatabase.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/User.php");

// Try to get logged in data from session or cookie data
$user = User::getLoggedInUser(MySQLDatabase::getDefault());
// Redirect to login page if not logged in
if (!isset($user)) {
    header("Location: ../../login");
    die();
}

$difficulty = $user->getPreferredDifficulty()->name;

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gameplay Settings | Kil-Learn</title>
    <link rel="stylesheet" type="text/css" href="../../resources/stylesheets/default.css">
    <style>
        body {
            background-image: url(../../resources/images/settings/gameplay-background.jpg);
        }
    </style>
</head>
<body>
<header>
    <h1>Kil-Learn</h1>
</header>
<main>
    <h1>Gameplay Settings</h1>
    <hr>
    <form class="styled-form">
        <h2>Difficulty</h2>
        <hr>

        <label>
            <input type="radio" name="difficulty" value="EASY" id="EASY" checked>Easy
        </label>
        <p>1 minute per round</p>
        <label>
            <input type="radio" name="difficulty" value="MEDIUM" id="MEDIUM">Medium
        </label>
        <p>45 seconds per round</p>
        <label>
            <input type="radio" name="difficulty" value="HARD" id="HARD">Hard
        </label>
        <p>30 seconds per round</p>
        <label>
            <input type="radio" name="difficulty" value="VERY_HARD" id="VERY_HARD">Very Hard
        </label>
        <p>15 seconds per round</p>

        <a href="../"><button type="Button">Back</button></a>
        <button type="button" id="save-changes-btn" onclick="saveChanges()">Save Changes</button>

        <script>
            document.getElementById("<?php echo $difficulty ?>").checked = true;

            function saveChanges() {
                let selection = document.querySelector('input[name="difficulty"]:checked').value;
                if (selection !== <?php echo '"' . $user->getPreferredDifficulty()->name . '"' ?>) {
                    window.location.replace("../../back-end/scripts/set-preferred-difficulty.php?difficulty=" + selection)
                }
            }
        </script>
    </form>
</main>
<footer>
    <p>&copy; UoM 2023 Z13</p>
</footer>
</body>
</html>
