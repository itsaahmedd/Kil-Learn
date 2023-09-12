<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/MySQLDatabase.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/User.php");

// Try to get logged in data from session or cookie data
$user = User::getLoggedInUser(MySQLDatabase::getDefault());
// Redirect to dashboard page if logged in
if (isset($user)) {
    header("Location: dashboard");
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Kil-Learn</title>
    <link rel="stylesheet" type="text/css" href="resources/stylesheets/default.css">
</head>

<body>
<header>
    <h1>Kil-Learn</h1>
</header>
<main>
    <div class="centred container">
        <h2>Explore the World of CompSci Students!</h2>
        <div class="buttons">
            <a href="login"><button class="login-button">Login</button></a>
            <a href="sign-up"><button class="create-account-button">Create Account</button></a>
            <a href="leaderboard"><button class="start-button">Leaderboard</button></a>
        </div>
    </div>
</main>
<footer>
    <p>&copy; UoM 2023 Z13</p>
</footer>
</body>

</html>
