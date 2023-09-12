<?php

$docRoot = $_SERVER['DOCUMENT_ROOT'];
require_once("$docRoot/m11091ho/killearn/back-end/database/Result.php");

class SelectionResult extends Result
{

    public function __construct(Result $result)
    {
        parent::__construct($result->getResult(), $result->getErrorNumber(), $result->getErrorMessage(), $result->getAffectedRows());
    }

    public function nextRowAsArray(): array
    {
        return $this->result->fetch_array();
    }

    public function nextRowAsDict(): array
    {
        return $this->result->fetch_assoc();
    }

}