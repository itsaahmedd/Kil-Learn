<?php

class SQLCondition {

    private string $statement = "";
    private array $values = [];

    public function where(): SQLCondition
    {
        $this->statement = "WHERE " . $this->statement;
        return $this;
    }

    public function in(array $values): SQLCondition
    {
        $this->statement = "IN (" . join(", ", $values) . ")";
        return $this;
    }

    public function valueEquals(string $column, $value): SQLCondition
    {
        $this->append("$column=?");
        $this->appendValue($value);
        return $this;
    }

    public function valueEqualsIgnoreCase(string $column, $value): SQLCondition
    {
        $this->append("UPPER($column)=UPPER(?)");
        $this->appendValue($value);
        return $this;
    }

    public function valueLike(string $column, $value): SQLCondition
    {
        $this->append("$column LIKE ?");
        $this->appendValue($value);
        return $this;
    }

    public function valueLikeIgnoreCase(string $column, $value): SQLCondition
    {
        $this->append("UPPER($column) LIKE UPPER(?)");
        $this->appendValue($value);
        return $this;
    }

    public function valueNull(string $column): SQLCondition
    {
        $this->append("$column IS NULL");
        return $this;
    }

    private function appendValue($value): void
    {
        array_push($this->values, $value);
    }

    public function not(): SQLCondition
    {
        $this->append(" NOT ");
        return $this;
    }

    public function and(): SQLCondition
    {
        $this->append(" AND ");
        return $this;
    }

    public function or(): SQLCondition
    {
        $this->append(" OR ");
        return $this;
    }

    private function append(string $string): void
    {
        $this->statement .= $string;
    }

    public function getStatement(): string
    {
        return $this->statement;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public static function create(): SQLCondition
    {
        return new SQLCondition();
    }

    public static function whereValueEquals(string $column, $value): SQLCondition
    {
        return (new SQLCondition())->where()->valueEquals($column, $value);
    }

}