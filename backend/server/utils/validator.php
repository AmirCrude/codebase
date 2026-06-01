<?php
class Validator {
    public static function required($data, $fields) {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                throw new Exception("The field '{$field}' is required.");
            }
        }
        return true;
    }
    
    public static function email($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }
        return true;
    }
    
    public static function minLength($value, $length, $fieldName = 'Field') {
        if (strlen($value) < $length) {
            throw new Exception("{$fieldName} must be at least {$length} characters.");
        }
        return true;
    }
    
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}
?>