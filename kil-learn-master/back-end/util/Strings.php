<?php

function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);
}

function createCode(int $length): string
{
    $bytes = random_bytes($length / 2);
    return bin2hex($bytes);
}