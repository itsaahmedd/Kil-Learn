<?php

$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/config.inc.php");
require_once("$docRoot/m11091ho/killearn/back-end/database/Database.php");
require_once("$docRoot/m11091ho/killearn/back-end/database/DatabaseCredentials.php");

class MySQLDatabase implements Database
{

    private DatabaseCredentials $credentials;
    private mysqli $connection;

    /**
     * @param DatabaseCredentials $credentials
     */
    public function __construct(DatabaseCredentials $credentials)
    {
        $this->credentials = $credentials;
    }

    public function connect(): void
    {
        $this->connection = $this->credentials->connect();
    }

    public function disconnect(): bool
    {
        return $this->connection->close();
    }

    public function call(string $name, array $args): Result
    {
        $params = sizeof($args) == 0 ? "" : $this->getParametersDeclaration($args);
        return $this->prepareAndExecute("CALL $name$params", $args);
    }

    public function selectAll(string $table, SQLCondition $condition): SelectionResult
    {
        return new SelectionResult($this->prepareAndExecute(
            "SELECT * FROM $table " . $condition->getStatement(),
            $condition->getValues()
        ));
    }

    public function select(string $table, array $columns, SQLCondition $condition): SelectionResult
    {
        $columns = join(", ", $columns);
        return new SelectionResult($this->prepareAndExecute(
            "SELECT $columns FROM $table " . $condition->getStatement(),
            $condition->getValues()
        ));
    }

    public function selectSum(string $table, string $column, SQLCondition $condition): SelectionResult
    {
        return new SelectionResult($this->prepareAndExecute(
            "SELECT SUM($column) FROM $table " . $condition->getStatement(),
            $condition->getValues()
        ));
    }

    public function insert(string $table, array $values): Result
    {
        // Gets the fields declaration for fields being inserted to
        $fields = $this->getFieldsDeclaration($values);
        // Gets the parameters declaration for the values to insert
        $parameters = $this->getParametersDeclaration($values);
        return $this->prepareAndExecute(
            "INSERT INTO `$table` $fields VALUES $parameters",
            array_values($values)
        );
    }

    private function getFieldsDeclaration(array $values): string
    {
        return "(" . join(", ", array_keys($values)) . ")";
    }

    private function getParametersDeclaration(array $values): string
    {
        return "(" . join(", ", array_fill(0, count($values), "?")) . ")";
    }

    public function exists(string $table, SQLCondition $condition): ExistenceCheckResult
    {
        return new ExistenceCheckResult($this->prepareAndExecute(
            "SELECT COUNT(*) FROM `$table` " . $condition->getStatement(),
            $condition->getValues()
        ));
    }

    function updateAll(string $table, string $column, $value): Result
    {
        return $this->prepareAndExecute(
            "UPDATE $table SET $column=?",
            [$value]
        );
    }

    function update(string $table, string $column, $value, SQLCondition $condition): Result
    {
        $values = [$value];
        array_push($values, ...$condition->getValues());
        return $this->prepareAndExecute(
            "UPDATE $table SET $column=? " . $condition->getStatement(),
            $values
        );
    }

    function delete(string $table, SQLCondition $condition): Result
    {
        return $this->prepareAndExecute(
            "DELETE FROM $table " . $condition->getStatement(),
            $condition->getValues()
        );
    }

    private function prepareAndExecute(string $query, array $values): Result
    {
        $result = $this->initAndPrepare($query);
        if ($result instanceof Result) {
            return $result;
        }
        // Bind given values as statement parameters
        if (sizeof($values) > 0) {
            $result->bind_param($this->getSqlTypes($values), ...$values);
        }
        return $this->execute($result);
    }

    protected function initAndPrepare(string $query): Result|mysqli_stmt
    {
        $statement = mysqli_stmt_init($this->connection);
        if (!$statement->prepare($query)) {
            return Result::error($statement->errno, $statement->error, 0);
        }

        return $statement;
    }

    protected function getSqlTypes(array $values): string
    {
        $valueTypes = "";
        foreach ($values as $value) {
            $valueTypes .= $this->getSqlType($value);
        }
        return $valueTypes;
    }

    protected function getSqlType($value): string
    {
        return match (gettype($value)) {
            "integer" => "i",
            "double" => "d",
            "string" => "s",
            default => "b",
        };
    }

    protected function execute(mysqli_stmt $statement): Result
    {
        if (!$statement->execute()) {
            return Result::error($statement->errno, $statement->error, $statement->affected_rows);
        }

        $result = $statement->get_result();
        $affectedRows = $statement->affected_rows;
        if (!$result) {
            return Result::error($statement->errno, $statement->errno, $statement->affected_rows);
        }

        $statement->close();
        return Result::success($result, $affectedRows);
    }

    public static function getDefault(): MySQLDatabase
    {
        return new MySQLDatabase(new DatabaseCredentials(
            $GLOBALS["database_host"],
            $GLOBALS["database_user"],
            $GLOBALS["database_pass"],
            "2022_comp10120_z13"
        ));
    }

}