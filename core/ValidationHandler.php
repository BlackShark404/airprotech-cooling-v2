<?php

/**
 * DataValidator - A class for validating and sanitizing data
 * 
 * This class uses HTMLPurifier for HTML sanitization and Respect\Validation
 * for data validation with standardized JSON responses.
 */
class DataValidator
{
    /** @var \HTMLPurifier */
    protected $purifier;
    
    /** @var array */
    protected $errors = [];
    
    /**
     * Constructor
     * 
     * Initializes the HTMLPurifier with default configuration
     */
    public function __construct()
    {
        // Set up HTMLPurifier with default configuration
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,b,i,u,strong,em,a[href],ul,ol,li,br,span[style],div[class],h1,h2,h3,h4,h5,h6');
        $config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,text-decoration,color,background-color');
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.Nofollow', true);
        $config->set('URI.DisableExternalResources', true);
        $this->purifier = new \HTMLPurifier($config);
    }

    /**
     * Sanitize HTML content
     * 
     * @param string $html HTML content to sanitize
     * @return string Sanitized HTML content
     */
    public function sanitizeHtml($html)
    {
        return $this->purifier->purify($html);
    }
    
    /**
     * Sanitize a plain text string
     * 
     * @param string $text Text to sanitize
     * @return string Sanitized text
     */
    public function sanitizeText($text)
    {
        return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate an email address
     * 
     * @param string $email Email to validate
     * @return bool True if valid, false otherwise
     */
    public function validateEmail($email)
    {
        try {
            return \Respect\Validation\Validator::email()->validate($email);
        } catch (\Exception $e) {
            $this->errors[] = "Invalid email format: {$email}";
            return false;
        }
    }
    
    /**
     * Validate a URL
     * 
     * @param string $url URL to validate
     * @return bool True if valid, false otherwise
     */
    public function validateUrl($url)
    {
        try {
            return \Respect\Validation\Validator::url()->validate($url);
        } catch (\Exception $e) {
            $this->errors[] = "Invalid URL format: {$url}";
            return false;
        }
    }
    
    /**
     * Validate an integer value within range
     * 
     * @param mixed $value Value to validate
     * @param int $min Minimum allowed value
     * @param int $max Maximum allowed value
     * @return bool True if valid, false otherwise
     */
    public function validateInteger($value, $min = null, $max = null)
    {
        $validator = \Respect\Validation\Validator::intVal();
        
        if ($min !== null) {
            $validator = $validator->min($min);
        }
        
        if ($max !== null) {
            $validator = $validator->max($max);
        }
        
        try {
            return $validator->validate($value);
        } catch (\Exception $e) {
            $range = ($min !== null && $max !== null) ? " between {$min} and {$max}" : 
                    ($min !== null ? " greater than or equal to {$min}" : 
                    ($max !== null ? " less than or equal to {$max}" : ""));
            $this->errors[] = "Value must be an integer{$range}";
            return false;
        }
    }
    
    /**
     * Validate string length
     * 
     * @param string $value String to validate
     * @param int $min Minimum length
     * @param int $max Maximum length
     * @return bool True if valid, false otherwise
     */
    public function validateLength($value, $min = null, $max = null)
    {
        $validator = \Respect\Validation\Validator::stringType();
        
        if ($min !== null) {
            $validator = $validator->length($min);
        }
        
        if ($max !== null) {
            $validator = $validator->length($min, $max);
        }
        
        try {
            return $validator->validate($value);
        } catch (\Exception $e) {
            $range = ($min !== null && $max !== null) ? " between {$min} and {$max} characters" : 
                    ($min !== null ? " at least {$min} characters" : 
                    ($max !== null ? " at most {$max} characters" : ""));
            $this->errors[] = "String must be{$range}";
            return false;
        }
    }
    
    /**
     * Validate a date string
     * 
     * @param string $date Date string to validate
     * @param string $format Expected date format (default: Y-m-d)
     * @return bool True if valid, false otherwise
     */
    public function validateDate($date, $format = 'Y-m-d')
    {
        try {
            return \Respect\Validation\Validator::date($format)->validate($date);
        } catch (\Exception $e) {
            $this->errors[] = "Invalid date format. Expected format: {$format}";
            return false;
        }
    }
    
    /**
     * Validate a phone number
     * 
     * @param string $phone Phone number to validate
     * @return bool True if valid, false otherwise
     */
    public function validatePhone($phone)
    {
        try {
            // Basic phone validation - can be adjusted based on requirements
            return \Respect\Validation\Validator::phone()->validate($phone);
        } catch (\Exception $e) {
            $this->errors[] = "Invalid phone number format";
            return false;
        }
    }
    
    /**
     * Validate against a regular expression pattern
     * 
     * @param string $value Value to validate
     * @param string $pattern Regular expression pattern
     * @param string $errorMessage Custom error message
     * @return bool True if valid, false otherwise
     */
    public function validatePattern($value, $pattern, $errorMessage = "Value doesn't match required pattern")
    {
        try {
            return \Respect\Validation\Validator::regex($pattern)->validate($value);
        } catch (\Exception $e) {
            $this->errors[] = $errorMessage;
            return false;
        }
    }
    
    /**
     * Validate required fields in an array
     * 
     * @param array $data Data array to validate
     * @param array $requiredFields List of required field names
     * @return bool True if all required fields are present and not empty
     */
    public function validateRequired(array $data, array $requiredFields)
    {
        $valid = true;
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->errors[] = "Field '{$field}' is required";
                $valid = false;
            }
        }
        
        return $valid;
    }
    
    /**
     * Validate a complete data set with rules
     * 
     * @param array $data Data to validate
     * @param array $rules Validation rules
     * @return bool True if valid, false otherwise
     */
    public function validate(array $data, array $rules)
    {
        $valid = true;
        $this->errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            // Skip if field is not required and not present
            if (!isset($data[$field]) && (!isset($fieldRules['required']) || $fieldRules['required'] === false)) {
                continue;
            }
            
            // Check if required
            if (isset($fieldRules['required']) && $fieldRules['required'] === true) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    $this->errors[] = "Field '{$field}' is required";
                    $valid = false;
                    continue;
                }
            }
            
            // Skip further validation if the field is not present
            if (!isset($data[$field])) {
                continue;
            }
            
            $value = $data[$field];
            
            // Validate by type
            if (isset($fieldRules['type'])) {
                switch ($fieldRules['type']) {
                    case 'email':
                        if (!$this->validateEmail($value)) {
                            $valid = false;
                        }
                        break;
                    case 'url':
                        if (!$this->validateUrl($value)) {
                            $valid = false;
                        }
                        break;
                    case 'integer':
                        $min = $fieldRules['min'] ?? null;
                        $max = $fieldRules['max'] ?? null;
                        if (!$this->validateInteger($value, $min, $max)) {
                            $valid = false;
                        }
                        break;
                    case 'string':
                        $min = $fieldRules['minLength'] ?? null;
                        $max = $fieldRules['maxLength'] ?? null;
                        if (!$this->validateLength($value, $min, $max)) {
                            $valid = false;
                        }
                        break;
                    case 'date':
                        $format = $fieldRules['format'] ?? 'Y-m-d';
                        if (!$this->validateDate($value, $format)) {
                            $valid = false;
                        }
                        break;
                    case 'phone':
                        if (!$this->validatePhone($value)) {
                            $valid = false;
                        }
                        break;
                    case 'pattern':
                        if (isset($fieldRules['pattern'])) {
                            $message = $fieldRules['message'] ?? "Field '{$field}' has invalid format";
                            if (!$this->validatePattern($value, $fieldRules['pattern'], $message)) {
                                $valid = false;
                            }
                        }
                        break;
                }
            }
            
            // Custom validation function
            if (isset($fieldRules['custom']) && is_callable($fieldRules['custom'])) {
                $result = call_user_func($fieldRules['custom'], $value, $data);
                if ($result !== true) {
                    $this->errors[] = is_string($result) ? $result : "Field '{$field}' failed custom validation";
                    $valid = false;
                }
            }
        }
        
        return $valid;
    }
    
    /**
     * Get validation errors
     * 
     * @return array Array of validation error messages
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * Sanitize a complete data set based on types
     * 
     * @param array $data Data to sanitize
     * @param array $rules Sanitization rules by field
     * @return array Sanitized data
     */
    public function sanitize(array $data, array $rules)
    {
        $sanitized = [];
        
        foreach ($data as $field => $value) {
            if (!isset($rules[$field])) {
                // No rules defined for this field, apply basic sanitization
                $sanitized[$field] = $this->sanitizeText($value);
                continue;
            }
            
            $fieldRules = $rules[$field];
            $type = $fieldRules['type'] ?? 'text';
            
            switch ($type) {
                case 'html':
                    $sanitized[$field] = $this->sanitizeHtml($value);
                    break;
                case 'email':
                    $sanitized[$field] = filter_var($value, FILTER_SANITIZE_EMAIL);
                    break;
                case 'url':
                    $sanitized[$field] = filter_var($value, FILTER_SANITIZE_URL);
                    break;
                case 'integer':
                    $sanitized[$field] = (int)$value;
                    break;
                case 'float':
                    $sanitized[$field] = (float)$value;
                    break;
                case 'boolean':
                    $sanitized[$field] = (bool)$value;
                    break;
                case 'text':
                default:
                    $sanitized[$field] = $this->sanitizeText($value);
                    break;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Process data by validating and sanitizing
     * 
     * @param array $data Input data
     * @param array $rules Validation and sanitization rules
     * @return array|false Sanitized data if valid, false otherwise
     */
    public function process(array $data, array $rules)
    {
        if (!$this->validate($data, $rules)) {
            return false;
        }
        
        return $this->sanitize($data, $rules);
    }
    
    // ✅ Respond with JSON (generic)
    protected function json($data = [], $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // ✅ Respond with JSON error (standardized)
    protected function jsonError($message = 'An error occurred', $statusCode = 400, $data = []) {
        $this->json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
    
    /**
     * Validate request data and return JSON response
     * 
     * @param array $data Input data
     * @param array $rules Validation and sanitization rules
     * @return array|void Sanitized data if valid, JSON error response otherwise
     */
    public function validateRequest(array $data, array $rules)
    {
        if (!$this->validate($data, $rules)) {
            $this->jsonError('Validation failed', 400, [
                'errors' => $this->getErrors()
            ]);
        }
        
        return $this->sanitize($data, $rules);
    }
}