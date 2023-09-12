<?php

$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/SQLCondition.php");
require_once("$docRoot/m11091ho/killearn/back-end/database/Result.php");
require_once("$docRoot/m11091ho/killearn/back-end/database/SelectionResult.php");
require_once("$docRoot/m11091ho/killearn/back-end/database/ExistenceCheckResult.php");

interface Database
{

    public function connect(): void;

    public function disconnect(): bool;

    public function call(string $name, array $args): Result;

    public function selectAll(string $table, SQLCondition $condition): SelectionResult;

    public function select(string $table, array $columns, SQLCondition $condition): SelectionResult;

    public function selectSum(string $table, string $column, SQLCondition $condition): SelectionResult;

    public function insert(string $table, array $values): Result;

    public function exists(string $table, SQLCondition $condition): ExistenceCheckResult;

    function updateAll(string $table, string $column, $value): Result;

    function update(string $table, string $column, $value, SQLCondition $condition): Result;

    public function delete(string $table, SQLCondition $condition): Result;

}