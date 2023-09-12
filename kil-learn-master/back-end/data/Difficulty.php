<?php

enum Difficulty
{

    case EASY;
    case MEDIUM;
    case HARD;
    case VERY_HARD;

    public static function fromName(string $name): Difficulty
    {
        foreach (Difficulty::cases() as $difficulty) {
            if ($difficulty->name == $name) {
                return $difficulty;
            }
        }
        throw new ValueError("$name not a difficulty option.");
    }

    public static function getMaxTime(Difficulty $difficulty): int
    {
        return match ($difficulty) {
            Difficulty::EASY => 60,
            Difficulty::MEDIUM => 45,
            Difficulty::HARD => 30,
            Difficulty::VERY_HARD => 15
        };
    }

}