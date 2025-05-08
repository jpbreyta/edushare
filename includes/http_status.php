<?php
/**
 * HTTP Status Codes and Error Handling
 */

// HTTP Status Codes
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_NO_CONTENT', 204);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_METHOD_NOT_ALLOWED', 405);
define('HTTP_CONFLICT', 409);
define('HTTP_UNPROCESSABLE_ENTITY', 422);
define('HTTP_TOO_MANY_REQUESTS', 429);
define('HTTP_INTERNAL_SERVER_ERROR', 500);
define('HTTP_SERVICE_UNAVAILABLE', 503);

/**
 * Send JSON response with status code
 * 
 * @param mixed $data Response data
 * @param int $status HTTP status code
 * @return void
 */
function send_json_response($data, $status = HTTP_OK) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Send error response
 * 
 * @param string $message Error message
 * @param int $status HTTP status code
 * @param array $errors Additional error details
 * @return void
 */
function send_error_response($message, $status = HTTP_BAD_REQUEST, $errors = []) {
    $response = [
        'success' => false,
        'message' => $message
    ];
    
    if (!empty($errors)) {
        $response['errors'] = $errors;
    }
    
    send_json_response($response, $status);
}

/**
 * Send success response
 * 
 * @param mixed $data Response data
 * @param string $message Success message
 * @param int $status HTTP status code
 * @return void
 */
function send_success_response($data, $message = 'Success', $status = HTTP_OK) {
    $response = [
        'success' => true,
        'message' => $message,
        'data' => $data
    ];
    
    send_json_response($response, $status);
}

/**
 * Check if request method is allowed
 * 
 * @param array $allowed_methods Array of allowed HTTP methods
 * @return bool
 */
function is_method_allowed($allowed_methods) {
    $method = $_SERVER['REQUEST_METHOD'];
    if (!in_array($method, $allowed_methods)) {
        send_error_response(
            'Method not allowed',
            HTTP_METHOD_NOT_ALLOWED,
            ['allowed_methods' => $allowed_methods]
        );
        return false;
    }
    return true;
}

/**
 * Validate required fields
 * 
 * @param array $data Request data
 * @param array $required_fields Array of required field names
 * @return bool
 */
function validate_required_fields($data, $required_fields) {
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        send_error_response(
            'Required fields are missing',
            HTTP_UNPROCESSABLE_ENTITY,
            ['missing_fields' => $missing_fields]
        );
        return false;
    }
    return true;
}

/**
 * Rate limiting check
 * 
 * @param string $key Rate limit key (e.g., user_id or IP)
 * @param int $max_requests Maximum requests allowed
 * @param int $time_window Time window in seconds
 * @return bool
 */
function check_rate_limit($key, $max_requests = 60, $time_window = 60) {
    // Implementation would depend on your storage method (Redis, Database, etc.)
    // This is a placeholder for the actual implementation
    return true;
} 