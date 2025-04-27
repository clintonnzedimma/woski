<?php
namespace Woski\Support;
/**
 * Validator class for validating data against rules.
 * 
 * This class provides a simple way to validate data using various rules.
 * It supports custom error messages and attributes for better readability.
 * 
 * @package Woski\Support
 */
class Validator
{
    protected $data;
    protected $rules;
    protected $customAttributes;
    protected $errors = [];

    // Default error messages; %s will be replaced by field name or parameters
    protected $messages = [
        'required'         => 'The %s field is required.',
        'nullable'         => '',
        'email'            => 'The %s must be a valid email address.',
        'numeric'          => 'The %s must be a number.',
        'integer'          => 'The %s must be an integer.',
        'boolean'          => 'The %s field must be true or false.',
        'min'              => 'The %s must be at least %s.',
        'max'              => 'The %s may not be greater than %s.',
        'between'          => 'The %s must be between %s and %s.',
        'in'               => 'The %s must be one of: %s.',
        'not_in'           => 'The %s must not be one of: %s.',
        'regex'            => 'The %s format is invalid.',
        'confirmed'        => 'The %s confirmation does not match.',
        'same'             => 'The %s and %s must match.',
        'different'        => 'The %s and %s must be different.',
        'accepted'         => 'The %s must be accepted.',
        'url'              => 'The %s must be a valid URL.',
        'date'             => 'The %s is not a valid date.',
        'date_format'      => 'The %s does not match format %s.',
        'before'           => 'The %s must be a date before %s.',
        'after'            => 'The %s must be a date after %s.',
        'json'             => 'The %s must be a valid JSON string.',
        'ip'               => 'The %s must be a valid IP address.',
        'array'            => 'The %s must be an array.',
        'present'          => 'The %s field must be present.',
        'digits'           => 'The %s must be %s digits.',
        'digits_between'   => 'The %s must be between %s and %s digits.',
        'size'             => 'The %s must be %s.',
        // Add more messages as you implement more rules...
    ];

    public function __construct(array $data, array $rules, array $customAttributes = [])
    {
        $this->data             = $data;
        $this->rules            = $rules;
        $this->customAttributes = $customAttributes;
    }

    // Factory method
    public static function make(array $data, array $rules, array $customAttributes = [])
    {
        return new self($data, $rules, $customAttributes);
    }

    // Did validation pass?
    public function passes(): bool
    {
        $this->validate();
        return empty($this->errors);
    }

    // Did validation fail?
    public function fails(): bool
    {
        return ! $this->passes();
    }

    // Get all error messages
    public function errors(): array
    {
        $this->validate();
        return $this->errors;
    }

    // Core validation logic
    protected function validate(): void
    {
        // Avoid re-running
        if (!empty($this->errors)) return;

        foreach ($this->rules as $field => $ruleSet) {
            $value  = $this->data[$field] ?? null;
            $rules  = is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet);

            foreach ($rules as $rule) {
                // Parse parameters (e.g. min:3 → ['min','3'])
                [$ruleName, $params] = array_pad(explode(':', $rule, 2), 2, null);
                $params = $params !== null ? explode(',', $params) : [];

                // Skip nullable when empty
                if ($ruleName === 'nullable' && ($value === null || $value === '')) {
                    continue 2;
                }

                // Build method name: validateRequired, validateEmail, etc.
                $method = 'validate' . ucfirst($ruleName);

                if (!method_exists($this, $method)) {
                    throw new \Exception("Validation rule {$ruleName} not implemented.");
                }

                $result = $this->{$method}($field, $value, $params);
                if (! $result) {
                    $this->addError($field, $ruleName, $params);
                }
            }
        }
    }

    protected function addError(string $field, string $rule, array $params): void
    {
        $name    = $this->customAttributes[$field] ?? str_replace('_', ' ', $field);
        $message = $this->messages[$rule];

        // Prepare message with vsprintf
        $replacements = array_merge([$name], $params);
        $this->errors[$field][] = vsprintf($message, $replacements);
    }

    // === Rule methods ===

    protected function validateRequired($field, $value, $params): bool
    {
        return ! is_null($value) && $value !== '';
    }

    protected function validateEmail($f, $v, $p): bool
    {
        return filter_var($v, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validateNumeric($f, $v, $p): bool
    {
        return is_numeric($v);
    }

    protected function validateInteger($f, $v, $p): bool
    {
        return filter_var($v, FILTER_VALIDATE_INT) !== false;
    }

    protected function validateBoolean($f, $v, $p): bool
    {
        return in_array($v, [true, false, 0, 1, '0', '1'], true);
    }

    protected function validateMin($f, $v, $p): bool
    {
        $min = (float) $p[0];
        if (is_numeric($v))    return $v >= $min;
        if (is_string($v))     return mb_strlen($v) >= $min;
        if (is_array($v))      return count($v) >= $min;
        return false;
    }

    protected function validateMax($f, $v, $p): bool
    {
        $max = (float) $p[0];
        if (is_numeric($v))    return $v <= $max;
        if (is_string($v))     return mb_strlen($v) <= $max;
        if (is_array($v))      return count($v) <= $max;
        return false;
    }

    protected function validateBetween($f, $v, $p): bool
    {
        [$min, $max] = $p;
        return $this->validateMin($f, $v, [$min])
            && $this->validateMax($f, $v, [$max]);
    }

    protected function validateIn($f, $v, $p): bool
    {
        return in_array($v, $p, true);
    }

    protected function validateNot_in($f, $v, $p): bool
    {
        return ! in_array($v, $p, true);
    }

    protected function validateRegex($f, $v, $p): bool
    {
        return preg_match($p[0], $v) === 1;
    }

    protected function validateConfirmed($f, $v, $p): bool
    {
        return isset($this->data[$f.'_confirmation'])
            && $v === $this->data[$f.'_confirmation'];
    }

    protected function validateSame($f, $v, $p): bool
    {
        $other = $p[0];
        return isset($this->data[$other]) && $v === $this->data[$other];
    }

    protected function validateDifferent($f, $v, $p): bool
    {
        $other = $p[0];
        return isset($this->data[$other]) && $v !== $this->data[$other];
    }

    protected function validateAccepted($f, $v, $p): bool
    {
        return in_array($v, ['yes','on',1,'1',true], true);
    }

    protected function validateUrl($f, $v, $p): bool
    {
        return filter_var($v, FILTER_VALIDATE_URL) !== false;
    }

    protected function validateDate($f, $v, $p): bool
    {
        return strtotime($v) !== false;
    }

    protected function validateDate_format($f, $v, $p): bool
    {
        $d = \DateTime::createFromFormat($p[0], $v);
        return $d && $d->format($p[0]) === $v;
    }

    protected function validateBefore($f, $v, $p): bool
    {
        return strtotime($v) < strtotime($p[0]);
    }

    protected function validateAfter($f, $v, $p): bool
    {
        return strtotime($v) > strtotime($p[0]);
    }

    protected function validateJson($f, $v, $p): bool
    {
        json_decode($v);
        return json_last_error() === JSON_ERROR_NONE;
    }

    protected function validateIp($f, $v, $p): bool
    {
        return filter_var($v, FILTER_VALIDATE_IP) !== false;
    }

    protected function validateArray($f, $v, $p): bool
    {
        return is_array($v);
    }

    protected function validatePresent($f, $v, $p): bool
    {
        return array_key_exists($f, $this->data);
    }

    protected function validateDigits($f, $v, $p): bool
    {
        return ctype_digit((string)$v) && strlen((string)$v) == $p[0];
    }

    protected function validateDigits_between($f, $v, $p): bool
    {
        $len = strlen((string)$v);
        return $len >= $p[0] && $len <= $p[1];
    }

    protected function validateSize($f, $v, $p): bool
    {
        $size = $p[0];
        if (is_numeric($v))    return $v == $size;
        if (is_string($v))     return mb_strlen($v) == $size;
        if (is_array($v))      return count($v) == $size;
        return false;
    }

    // Add more validateRuleName() methods as needed...
}
