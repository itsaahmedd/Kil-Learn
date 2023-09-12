<?php

$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/Database.php");
require_once("$docRoot/m11091ho/killearn/back-end/database/ConnectedDatabase.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/Difficulty.php");
require_once("$docRoot/m11091ho/killearn/back-end/util/Strings.php");

class User
{

    private static string $USER_ID_COOKIE_KEY = "UserID";
    private static string $SESSION_CODE_COOKIE_KEY = "SessionCode";

    /**
     * @return User|null data if logged in, or null if not logged in
     */
    public static function getLoggedInUser(Database $database): User|null
    {
        $id = $_COOKIE["UserID"];
        $sessionCode = $_COOKIE["SessionCode"];
        if (!isset($id) || !isset($sessionCode)) {
            return null;
        }
        $database->connect();
        self::clearExpiredSessions(ConnectedDatabase::from($database)); // Clear expired sessions before checking for them
        $result = $database->exists(
            "UserSession",
            SQLCondition::whereValueEquals("UserID", $id)->and()->valueEquals("Code", $sessionCode)
        );
        if ($result->isError() || !$result->exists()) {
            $database->disconnect();
            return null;
        }
        $user = self::fromId(ConnectedDatabase::from($database), $id);
        $database->disconnect();
        return $user;
    }

    private static function clearExpiredSessions(Database $database): void
    {
        $database->connect();
        $database->call("ClearExpiredUserSessions", []);
        $database->disconnect();
    }

    public static function fromId(Database $database, int $id): User|null
    {
        $database->connect();
        $user = self::fromSelection(
            $database->selectAll("User", SQLCondition::whereValueEquals("ID", $id))
        );
        $database->disconnect();
        return $user;
    }

    public static function fromUsername(Database $database, string $username): User|null
    {
        $database->connect();
        $user = self::fromSelection(
            $database->selectAll("User", SQLCondition::whereValueEquals("Username", $username))
        );
        $database->disconnect();
        return $user;
    }

    private static function fromSelection(SelectionResult $result): User|null
    {
        $row = $result->nextRowAsDict();
        if ($row && array_key_exists("Username", $row)) {
            return new User($row["ID"], $row["Username"], Difficulty::fromName($row["PreferredDifficulty"]));
        } else {
            return null;
        }
    }

    private int|null $id;
    private string $username;
    private Difficulty $preferredDifficulty;

    public function __construct(int|null $id, string $username, Difficulty $preferredDifficulty = Difficulty::MEDIUM)
    {
        $this->id = $id;
        $this->username = $username;
        $this->preferredDifficulty = $preferredDifficulty;
    }

    /**
     * @param string $password the unhashed password to authenticate
     * @return bool true if password is authentic
     */
    public function authenticate(Database $database, string $password): bool
    {
        $database->connect();
        $result = $database->select("User", ["Password"], SQLCondition::whereValueEquals("Username", $this->username));
        if ($result->isError()) {
            $database->disconnect();
            return false;
        }
        $authenticPassword = $result->nextRowAsArray()[0];
        $database->disconnect();
        return password_verify($password, $authenticPassword);
    }

    /**
     * Inserts this user into the database.
     *
     * @param string $password the unhashed password to set
     * @return bool true if data was successfully inserted
     */
    public function create(Database $database, string $password): bool
    {
        $hashedPassword = $this->hash($password);
        $database->connect();
        $result = $database->insert("User", [
            "Username" => $this->username,
            "Password" => $hashedPassword,
            "PreferredDifficulty" => $this->preferredDifficulty->name
        ]);
        if ($result->isError()) {
            $database->disconnect();
            return false;
        }
        $result = $database->select("User", ["ID"], SQLCondition::whereValueEquals("Username", $this->username));
        if ($result->isError()) {
            $database->disconnect();
            return false;
        }
        $this->id = $result->nextRowAsArray()[0];
        $database->disconnect();
        return true;
    }

    public function createLoginSession(Database $database): bool
    {
        $expiration = time() + (60 * 60 * 24) * 60; // Set session to expire after 60 days
        $code = createCode(128);
        setcookie(self::$USER_ID_COOKIE_KEY, $this->id, $expiration, "/");
        setcookie(self::$SESSION_CODE_COOKIE_KEY, $code, $expiration, "/");
        $database->connect();
        $result = $database->insert("UserSession", [
            "UserID" => $this->id,
            "Code" => $code,
            "ExpiresAt" => date_format(new DateTime("@$expiration"), "Y-m-d H:i:s")
        ]);
        $database->disconnect();
        return $result->isSuccess();
    }

    public function logout(Database $database): bool
    {
        $database->connect();
        $result = $database->delete(
            "UserSession",
            SQLCondition::whereValueEquals("UserID", $_COOKIE[self::$USER_ID_COOKIE_KEY])
                ->and()->valueEquals("Code", $_COOKIE[self::$SESSION_CODE_COOKIE_KEY])
        );
        $success = $result->isSuccess() && $result->getAffectedRows() > 0;
        if ($success) {
            setcookie(self::$USER_ID_COOKIE_KEY, null, time() - 3600);
            setcookie(self::$SESSION_CODE_COOKIE_KEY, null, time() - 3600);
        }
        $database->disconnect();
        return $success;
    }

    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getPreferredDifficulty(): Difficulty
    {
        return $this->preferredDifficulty;
    }

    public function setPreferredDifficulty(Database $database, Difficulty $preferredDifficulty): bool
    {
        $this->preferredDifficulty = $preferredDifficulty;
        return $this->updatePreferredDifficulty($database);
    }

    private function updatePreferredDifficulty(Database $database): bool
    {
        $database->connect();
        $result = $database->update(
            "User",
            "PreferredDifficulty", $this->preferredDifficulty->name,
            SQLCondition::whereValueEquals("ID", $this->id)
        );
        $database->disconnect();
        return $result->isSuccess();
    }

    public function __toString(): string
    {
        return "User{id=$this->id, username=$this->username, preferredDifficulty=$this->preferredDifficulty}";
    }

}