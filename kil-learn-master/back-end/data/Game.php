<?php

$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/Database.php");
require_once("$docRoot/m11091ho/killearn/back-end/database/ConnectedDatabase.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/User.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/GameRound.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/Room.php");

class Game
{

    /** @var int number of rounds in each game */
    private static int $ROUNDS = 5;

    private static string $GAME_ID_SESSION_KEY = "GameID";
    private static string $GAME_ROUND_INDEX_SESSION_KEY = "GameRoundIndex";
    public static string $GAME_ROUND_WRONG_SELECTIONS_SESSION_KEY = "GameRoundWrongSelections";

    private int|null $id;
    private User $player;
    private int $score;
    private DateTime $startedAt;
    /** @var DateTime|null timestamp for when the game ended. This will be null until the game has ended. */
    private DateTime|null $endedAt;

    public function __construct(int|null $id, User $player, int $score, DateTime $startedAt, DateTime|null $endedAt)
    {
        $this->id = $id;
        $this->player = $player;
        $this->score = $score;
        $this->startedAt = $startedAt;
        $this->endedAt = $endedAt;
    }

    /**
     * Gets the game currently running from session data.
     *
     * @param Database $database the database to get the game data from
     * @return Game|null the game, or null if one is not saved to session data
     */
    public static function getCurrent(Database $database): Game|null
    {
        session_start();
        $id = $_SESSION[Game::$GAME_ID_SESSION_KEY];
        if (!isset($id)) {
            return null;
        }
        $database->connect();
        $game = self::fromSelection(
            $database, $database->selectAll("Game", SQLCondition::whereValueEquals("ID", $id))
        );
        $database->disconnect();
        return $game;
    }

    private static function fromSelection(Database $database, SelectionResult $result): Game|null
    {
        $row = $result->nextRowAsDict();
        if (array_key_exists("UserID", $row) && array_key_exists("Score", $row)
            && array_key_exists("StartedAt", $row)) {
            return new Game(
                $row["ID"],
                User::fromId(ConnectedDatabase::from($database), $row["UserID"]),
                $row["Score"],
                new DateTime($row["StartedAt"]),
                isset($row["EndedAt"]) ? new DateTime($row["EndedAt"]) : null
            );
        } else {
            return null;
        }
    }

    public static function getHighScore(Database $database, User $user): int
    {
        $database->connect();
        $result = $database->selectAll(
            "Game",
            SQLCondition::whereValueEquals("UserID", $user->getId())
        );
        if ($result->isError()) {
            $database->disconnect();
            return -1;
        }
        $highScore = -INF;
        for ($i = 0; $i < $result->getAffectedRows(); $i++) {
            $row = $result->nextRowAsDict();
            $score = $database->selectSum(
                "GameRound", "Score",
                SQLCondition::whereValueEquals("GameID", $row["ID"])
            )->nextRowAsArray()[0];
            if ($score > $highScore) {
                $highScore = $score;
            }
        }
        $database->disconnect();
        return $highScore;
    }

    public static function getHighScores(Database $database): array
    {
        $database->connect();
        $result = $database->selectAll("Game", SQLCondition::create());
        if ($result->isError()) {
            $database->disconnect();
            return [];
        }
        $scores = [];
        for ($i = 0; $i < $result->getAffectedRows(); $i++) {
            $row = $result->nextRowAsDict();
            $score = $database->selectSum(
                "GameRound", "Score",
                SQLCondition::whereValueEquals("GameID", $row["ID"])
            )->nextRowAsArray()[0];
            $username = User::fromId(ConnectedDatabase::from($database), $row["UserID"])->getUsername();
            $currentScore = $scores[$username];
            if (!isset($currentScore) || $score > $currentScore) {
                $scores[$username] = $score;
            }
        }
        $database->disconnect();
        return $scores;
    }

    /**
     * Inserts this game into the database.
     *
     * @return bool true if data was successfully inserted
     */
    public function create(Database $database): bool
    {
        $database->connect();
        $userId = $this->player->getId();
        $startedAt = $this->startedAt->format("Y-m-d H:i:s");
        $result = $database->insert("Game", [
            "UserID" => $userId,
            "Score" => $this->score,
            "StartedAt" => $startedAt
        ]);
        if ($result->isError()) {
            $database->disconnect();
            return false;
        }
        $result = $database->select(
            "Game", ["ID"],
            SQLCondition::whereValueEquals("UserID", $userId)
                ->and()->valueEquals("StartedAt", $startedAt)
        );
        if ($result->isError()) {
            $database->disconnect();
            return false;
        }
        $this->id = $result->nextRowAsArray()[0];
        if (!$this->createRounds(ConnectedDatabase::from($database))) {
            return false;
        }
        $database->disconnect();
        return true;
    }

    /**
     * Saves game data for a new game to session.
     *
     * @return bool true if the session was successfully started/resumed
     */
    public function saveSessionData(): bool
    {
        $started = session_start();
        $_SESSION[Game::$GAME_ID_SESSION_KEY] = $this->id;
        $_SESSION[Game::$GAME_ROUND_INDEX_SESSION_KEY] = 0;
        $_SESSION[Game::$GAME_ROUND_WRONG_SELECTIONS_SESSION_KEY] = null;
        return $started;
    }

    private function createRounds(Database $database): bool
    {
        $rooms = Room::getRooms();
        for ($i = 0; $i < self::$ROUNDS; $i++) {
            // Select a random room
            $roomIndex = rand(0, sizeof($rooms) - 1);
            $room = $rooms[$roomIndex];
            // Remove selected room so we don't select the same room twice
            array_splice($rooms, $roomIndex, 1);

            // Create round and add to the database
            $success = (new GameRound(null, $room, Difficulty::getMaxTime($this->player->getPreferredDifficulty()), 0, null))
                ->create($database, $this);
            if (!$success) {
                return false;
            }
        }
        return true;
    }

    public function getCurrentRound(Database $database): GameRound|null
    {
        if (!$this->isOngoing()) {
            return null;
        }
        session_start();
        return $this->getRounds($database)[$_SESSION[self::$GAME_ROUND_INDEX_SESSION_KEY]];
    }

    /**
     * @return array an array of all rounds associated with this game
     */
    public function getRounds(Database $database): array
    {
        $database->connect();
        $result = $database->selectAll(
            "GameRound",
            SQLCondition::whereValueEquals("GameID", $this->id)
        );
        $rounds = [];
        for ($i = 0; $i < $result->getResult()->num_rows; $i++) {
            $rounds[] = GameRound::fromSelection($result);
        }
        $database->disconnect();
        return $rounds;
    }

    public function getRoundNumber(): int
    {
        session_start();
        return $_SESSION[self::$GAME_ROUND_INDEX_SESSION_KEY] + 1;
    }

    /**
     * @return bool true if there is a next round, false if the game is over
     */
    public function nextRound(Database $database): bool
    {
        session_start();
        $_SESSION[self::$GAME_ROUND_WRONG_SELECTIONS_SESSION_KEY] = null; // Reset wrong selections list
        $index = $_SESSION[self::$GAME_ROUND_INDEX_SESSION_KEY]++;
        if ($index >= self::$ROUNDS - 1) {
            $this->end($database);
            return false;
        }
        return true;
    }

    private function end(Database $database): void
    {
        $this->endedAt = new DateTime();
        $database->connect();
        $database->update(
            "Game",
            "EndedAt", $this->endedAt->format("Y-m-d H:i:s"),
            SQLCondition::whereValueEquals("ID", $this->id)
        );
        $database->disconnect();
    }

    public function isOngoing(): bool
    {
        return $this->endedAt == null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlayer(): User
    {
        return $this->player;
    }

    public function getScore(Database $database): int
    {
        $database->connect();
        $result = $database->selectSum(
            "GameRound",
            "Score",
            SQLCondition::whereValueEquals("GameID", $this->id)
        );
        if ($result->isError()) {
            $database->disconnect();
            return -1;
        }
        $score = $result->nextRowAsArray()[0];
        $database->disconnect();
        return $score;
    }

    public function getStartedAt(): DateTime
    {
        return $this->startedAt;
    }

    public function quit(Database $database): bool
    {
        if (!$this->isOngoing()) {
            return false;
        }
        $database->connect();
        $roundResult = $database->delete(
            "GameRound",
            SQLCondition::whereValueEquals("GameID", $this->id)
        );
        $gameResult = $database->delete(
            "Game",
            SQLCondition::whereValueEquals("ID", $this->id)
        );
        $database->disconnect();
        return $roundResult->isSuccess() && $gameResult->isSuccess();
    }

    public function getEndedAt(): DateTime|null
    {
        return $this->endedAt;
    }


}