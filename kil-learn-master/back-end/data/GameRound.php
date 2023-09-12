<?php

$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/Database.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/Game.php");
require_once("$docRoot/m11091ho/killearn/back-end/data/Room.php");

/**
 * Contains data about the round in a game. This may be ongoing or finished.
 */
class GameRound
{

    private int|null $id;
    private Room $room;
    /** @var int the maximum time, in seconds, the user has to guess the image */
    private int $maxTime;
    private int $score;
    private DateTime|null $startedAt;

    public function __construct(int|null $id, Room $room, int $maxTime, int $score, DateTime|null $startedAt)
    {
        $this->id = $id;
        $this->room = $room;
        $this->maxTime = $maxTime;
        $this->score = $score;
        $this->startedAt = $startedAt;
    }

    /**
     * Inserts this game round into the database.
     *
     * @return bool true if data was successfully inserted
     */
    public function create(Database $database, Game $parent): bool
    {
        $database->connect();
        $result = $database->insert("GameRound", [
            "GameID" => $parent->getId(),
            "RoomName" => $this->room->getName(),
            "MaxTime" => $this->maxTime,
            "Score" => $this->score,
            "StartedAt" => isset($this->startedAt) ? $this->startedAt->format("Y-m-d H:i:s") : null
        ]);
        if ($result->isError()) {
            $database->disconnect();
            return false;
        }
        $result = $database->select(
            "GameRound", ["ID"],
            SQLCondition::whereValueEquals("GameID", $parent->getId())
                ->and()->valueEquals("RoomName", $this->room->getName())
        );
        if ($result->isError()) {
            $database->disconnect();
            return false;
        }
        $this->id = $result->nextRowAsArray()[0];
        $database->disconnect();
        return true;
    }

    public function start(Database $database): bool
    {
        // If the game has already started, don't restart it
        if ($this->startedAt != null) {
            return false;
        }
        $this->startedAt = new DateTime();
        $database->connect();
        $result = $database->update(
            "GameRound",
            "StartedAt", $this->startedAt->format("Y-m-d H:i:s"),
            SQLCondition::whereValueEquals("ID", $this->id)
        );
        $database->disconnect();
        return $result->isSuccess();
    }

    /**
     * @return bool true if the round has now ended
     */
    public function guess(string $guess, Database $database): bool
    {
        $guessedAt = time();
        // Invalid guess
        if ($guessedAt > $this->getMaxTimestamp()) {
            return true;
        }

        // Update score
        $correct = $guess == $this->room->getName();
        if ($correct) {
            $this->score += $this->calculateScore($guessedAt);
        } else {
            $this->wrongGuess($guess);
        }
        $this->updateScore($database);

        return $correct;
    }

    public function wrongGuess(string $guess): void
    {
        $this->score -= 5;
        session_start();
        $wrongSelections = $_SESSION[Game::$GAME_ROUND_WRONG_SELECTIONS_SESSION_KEY];
        if (isset($wrongSelections)) {
            $_SESSION[Game::$GAME_ROUND_WRONG_SELECTIONS_SESSION_KEY] .= "&$guess";
        } else {
            $_SESSION[Game::$GAME_ROUND_WRONG_SELECTIONS_SESSION_KEY] = $guess;
        }
    }

    public function guessWasCorrect(): bool
    {
        return $this->score > 0;
    }

    public function updateScore(Database $database): void
    {
        $database->connect();
        $database->update(
            "GameRound",
            "Score", $this->score,
            SQLCondition::whereValueEquals("ID", $this->id)
        );
        $database->disconnect();
    }

    private function calculateScore(int $finishedAt): int
    {
        return $this->getMaxTimestamp() - $finishedAt + (Difficulty::getMaxTime(Difficulty::EASY) - $this->maxTime);
    }

    public function getRoom(): Room
    {
        return $this->room;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function hasStarted(): bool
    {
        return $this->startedAt != null;
    }

    public function isOngoing(): bool
    {
        return (time() - $this->startedAt->getTimestamp()) < $this->maxTime || $this->score <= 0;
    }

    public function getTimeLeft(): int
    {
        return $this->maxTime - (time() - $this->startedAt->getTimestamp());
    }

    public function getStartedAt(): DateTime|null
    {
        return $this->startedAt;
    }

    public function getMaxTimestamp(): int
    {
        return $this->startedAt->getTimestamp() + $this->maxTime;
    }

    public static function fromId(Database $database, int $id): GameRound|null
    {
        $database->connect();
        $round = self::fromSelection(
            $database->selectAll("GameRound", SQLCondition::whereValueEquals("ID", $id))
        );
        $database->disconnect();
        return $round;
    }

    public static function fromSelection(SelectionResult $result): GameRound|null
    {
        $row = $result->nextRowAsDict();
        if ($row && array_key_exists("RoomName", $row)) {
            return new GameRound(
                $row["ID"],
                Room::get($row["RoomName"]),
                $row["MaxTime"],
                $row["Score"],
                isset($row["StartedAt"]) ? new DateTime($row["StartedAt"]) : null
            );
        } else {
            return null;
        }
    }

}