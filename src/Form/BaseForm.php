<?php

namespace WebDevProject\Form;

abstract class BaseForm
{
    /** @var array<string, mixed> Valid form data */
    protected array $data = [];

    /** @var string[] Validation error messages */
    protected array $errors = [];

    /**
     * Load raw input (e.g. $_POST) into form data
     */
    abstract public function formLoad(array $input): void;

    /**
     * Validate the loaded data, populate \$errors, return true if no errors
     */
    abstract public function formValidate(): bool;

    /**
     * Render the HTML form, including errors and CSRF token
     */
    abstract public function formRender(): string;

    /**
     * Get all validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get a sanitized value from the form data
     */
    public function getValue(string $field, string $default = ''): string
    {
        return isset($this->data[$field]) ? (string)$this->data[$field] : $default;
    }

    /**
     * Add a validation error message
     */
    public function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    /**
     * Check if form has any validation errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
