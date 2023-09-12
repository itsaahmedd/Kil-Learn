<?php
$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/MySQLDatabase.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/User.php");

// Try to get logged in data from session or cookie data
$user = User::getLoggedInUser(MySQLDatabase::getDefault());
// Redirect to dashboard if logged in
if (isset($user)) {
    header("Location: ../dashboard");
    die();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | Kil-Learn</title>
    <link rel="stylesheet" type="text/css" href="../resources/stylesheets/default.css">
</head>
<body>
<header>
    <h1>Kil-Learn</h1>
</header>
<div class="centred container">
    <h2>Enter Your Username and Password</h2>
    <form class="centred-form" action="../back-end/scripts/login.php" method="POST">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <?php
        $errors = [
            "username-password-invalid" => "Username or password invalid.",
            "server" => "Unknown server error."
        ];
        $error = $_GET["error"];
        if (isset($error)) {
            echo "<p>" . $errors[$error] . "</p>";
        }
        ?>
        <div class="buttons">
            <button type="submit" class="login-button">Login</button>
        </div>
    </form>
</div>
<footer>
    <p>&copy; UoM 2023 Z13</p>
</footer>
</body>
</html>