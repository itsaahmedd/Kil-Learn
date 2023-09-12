<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/MySQLDatabase.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/User.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/Game.php");

$database = MySQLDatabase::getDefault();
// Try to get logged in data from session or cookie data
$user = User::getLoggedInUser($database);
// Redirect to login page if not logged in
if (!isset($user)) {
    header("Location: ../../login");
    die();
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Account Details | Kil-Learn</title>
    <link rel="stylesheet" type="text/css" href="../../resources/stylesheets/default.css">
    <style>
        body {
            background-image: url(../../resources/images/settings/account-background.jpg);
        }
    </style>
</head>
<body>
<header>
    <h1>Kil-Learn</h1>
</header>
<main>
    <div class="container">
        <h1>Account Details</h1>
        <form class="styled-form">
            <div class="form-container">
                <table>
                    <tr>
                        <td>Username</td>
                        <td><?php echo $user->getUsername() ?></td>
                    </tr>
                    <tr>
                        <td>High Score</td>
                        <td><?php echo Game::getHighScore($database, $user) ?></td>
                    </tr>
                </table>
            </div>
        </form>
    </div>
    <a href="../index.php"><button style="margin-top: 15px">Back</button></a>
</main>
<footer>
    <p>&copy; UoM 2023 Z13</p>
</footer>
</body>
</html>
