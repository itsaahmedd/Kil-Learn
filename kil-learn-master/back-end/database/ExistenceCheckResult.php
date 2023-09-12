<?php

$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/Result.php");

class ExistenceCheckResult extends Result
{

    private bool $exists;

    public function __construct(Result $result)
    {
        parent::__construct($result->getResult(), $result->getErrorNumber(), $result->getErrorMessage(), $result->getAffectedRows());
        $this->exists = $result->getResult() && $result->getResult()->fetch_array()[0] > 0;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

}