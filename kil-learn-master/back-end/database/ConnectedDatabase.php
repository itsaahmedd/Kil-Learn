<?php

$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/Database.php");

class ConnectedDatabase implements Database
{

    public static function from(Database $database): Database
    {
        return $database instanceof ConnectedDatabase ? $database : new ConnectedDatabase($database);
    }

    private Database $database;

    private function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function connect(): void
    {
    }

    public function disconnect(): bool
    {
        return true;
    }

    public function call(string $name, array $args): Result
    {
        return $this->database->call($name, $args);
    }

    public function selectAll(string $table, SQLCondition $condition): SelectionResult
    {
        return $this->database->selectAll($table, $condition);
    }

    public function select(string $table, array $columns, SQLCondition $condition): SelectionResult
    {
        return $this->database->select($table, $columns, $condition);
    }

    public function selectSum(string $table, string $column, SQLCondition $condition): SelectionResult
    {
        return $this->database->selectSum($table, $column, $condition);
    }

    public function insert(string $table, array $values): Result
    {
        return $this->database->insert($table, $values);
    }

    public function exists(string $table, SQLCondition $condition): ExistenceCheckResult
    {
        return $this->database->exists($table, $condition);
    }

    function updateAll(string $table, string $column, $value): Result
    {
        return $this->database->updateAll($table, $column, $value);
    }

    function update(string $table, string $column, $value, SQLCondition $condition): Result
    {
        return $this->database->update($table, $column, $value, $condition);
    }

    public function delete(string $table, SQLCondition $condition): Result
    {
        return $this->database->delete($table, $condition);
    }


}