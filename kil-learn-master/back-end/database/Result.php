<?php

class Result
{

    protected mysqli_result|null $result;
    private int|null $errorNumber;
    private string|null $errorMessage;
    private int $affectedRows;

    protected function __construct(mysqli_result|null $result, int|null $errorNumber, string|null $errorMessage, int $affectedRows)
    {
        $this->result = $result;
        $this->errorNumber = $errorNumber;
        $this->errorMessage = $errorMessage;
        $this->affectedRows = $affectedRows;
    }

    public function isSuccess(): bool
    {
        return $this->errorNumber == null;
    }

    public function isError(): bool
    {
        return $this->errorNumber != null;
    }

    public function isServerError(): bool
    {
        return !isset($this->result) || !$this->result;
    }

    /**
     * @return mysqli_result|null result object, or null if this is an erroneous result
     */
    public function getResult(): mysqli_result|null
    {
        return $this->result;
    }

    /**
     * @return int|null the error number, or null if there was no error
     */
    public function getErrorNumber(): ?int
    {
        return $this->errorNumber;
    }

    /**
     * @return string|null the error message, or null if there was no error
     */
    public function getErrorMessage(): string|null
    {
        return $this->errorMessage;
    }

    public function getAffectedRows(): int
    {
        return $this->affectedRows;
    }

    public static function success(mysqli_result $result, int $affectedRows): Result
    {
        return new Result($result, null, null, $affectedRows);
    }

    public static function error(int $errorNumber, string $errorMessage, int $affectedRows): Result
    {
        return new Result(null, $errorNumber, $errorMessage, $affectedRows);
    }


}