<?php

namespace App\Handlers;

class ValidationErrors extends \Exception
{
    protected $validationErrors;

    public function __construct($validationErrors, string $message = "Please fix the following errors: ")
    {
        parent::__construct($message);
        $this->validationErrors = $validationErrors;
    }

    public function getValidationErrors()
    {
        return $this->validationErrors;
    }
}
