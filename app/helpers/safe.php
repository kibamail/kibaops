<?php

/**
 * Safe Helper Functions
 *
 * This file contains the safe() helper function and dedicated exception handlers
 * for clean, maintainable error handling throughout the application.
 *
 * Usage Examples:
 *
 * // Basic usage
 * $result = safe(function () {
 *     return ['user_id' => 123, 'name' => 'John Doe'];
 * });
 *
 * if ($result['error'] === null) {
 *     // Success - use $result['data']
 * } else {
 *     // Handle error - $result['error'] contains message
 * }
 *
 * // HTTP API call
 * $result = safe(function () {
 *     $client = new \GuzzleHttp\Client();
 *     $response = $client->get('https://api.example.com/users/123');
 *     return json_decode($response->getBody(), true);
 * });
 *
 * // Database operation
 * $result = safe(function () {
 *     return \App\Models\User::findOrFail(999);
 * });
 *
 * // Cloud provider operation
 * $result = safe(function () {
 *     $provider = app(\App\Services\CloudProviders\CloudProviderFactory::class)
 *         ->create(\App\Enums\CloudProviderType::HETZNER);
 *     return $provider->verify(['token']);
 * });
 */

if (! function_exists('safe')) {
    /**
     * Safely execute a callable and return standardized response.
     *
     * Catches exceptions using dedicated handler methods and attempts to parse
     * them into clear error messages. Always returns an array with 'data' and
     * 'error' keys, where one is null.
     *
     * @param callable $callback The function to execute safely
     * @return array{data: mixed, error: string|null}
     */
    function safe(callable $callback): array
    {
        try {
            $result = $callback();
            
            return [
                'data' => $result,
                'error' => null,
            ];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return handleGuzzleClientException($e);
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            return handleGuzzleServerException($e);
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            return handleGuzzleConnectException($e);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return handleGuzzleRequestException($e);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return handleValidationException($e);
        } catch (\Illuminate\Database\QueryException $e) {
            return handleDatabaseException($e);
        } catch (\InvalidArgumentException $e) {
            return handleInvalidArgumentException($e);
        } catch (\Exception $e) {
            return handleGenericException($e);
        } catch (\Throwable $e) {
            return handleThrowable($e);
        }
    }
}

if (! function_exists('handleGuzzleClientException')) {
    /**
     * Handle GuzzleHttp ClientException (4xx errors).
     * 
     * @param \GuzzleHttp\Exception\ClientException $e
     * @return array{data: null, error: string}
     */
    function handleGuzzleClientException(\GuzzleHttp\Exception\ClientException $e): array
    {
        $error = 'HTTP Client Error';
        
        if ($e->hasResponse()) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            
            $error = parseHttpResponseError($body, $statusCode, 'Error');
        }
        
        return [
            'data' => null,
            'error' => $error,
        ];
    }
}

if (! function_exists('handleGuzzleServerException')) {
    /**
     * Handle GuzzleHttp ServerException (5xx errors).
     * 
     * @param \GuzzleHttp\Exception\ServerException $e
     * @return array{data: null, error: string}
     */
    function handleGuzzleServerException(\GuzzleHttp\Exception\ServerException $e): array
    {
        $error = 'HTTP Server Error';
        
        if ($e->hasResponse()) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            
            $error = parseHttpResponseError($body, $statusCode, 'Server Error');
        }
        
        return [
            'data' => null,
            'error' => $error,
        ];
    }
}

if (! function_exists('handleGuzzleConnectException')) {
    /**
     * Handle GuzzleHttp ConnectException (connection failures).
     * 
     * @param \GuzzleHttp\Exception\ConnectException $e
     * @return array{data: null, error: string}
     */
    function handleGuzzleConnectException(\GuzzleHttp\Exception\ConnectException $e): array
    {
        return [
            'data' => null,
            'error' => 'Connection failed: ' . $e->getMessage(),
        ];
    }
}

if (! function_exists('handleGuzzleRequestException')) {
    /**
     * Handle GuzzleHttp RequestException (general request failures).
     * 
     * @param \GuzzleHttp\Exception\RequestException $e
     * @return array{data: null, error: string}
     */
    function handleGuzzleRequestException(\GuzzleHttp\Exception\RequestException $e): array
    {
        $error = 'Request failed';
        
        if ($e->hasResponse()) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            
            $error = parseHttpResponseError($body, $statusCode, 'Request Error');
        } else {
            $error = $e->getMessage();
        }
        
        return [
            'data' => null,
            'error' => $error,
        ];
    }
}

if (! function_exists('handleValidationException')) {
    /**
     * Handle Laravel ValidationException.
     * 
     * @param \Illuminate\Validation\ValidationException $e
     * @return array{data: null, error: string}
     */
    function handleValidationException(\Illuminate\Validation\ValidationException $e): array
    {
        $errors = $e->errors();
        $firstError = reset($errors);
        $errorMessage = is_array($firstError) ? reset($firstError) : $firstError;
        
        return [
            'data' => null,
            'error' => $errorMessage ?: 'Validation failed',
        ];
    }
}

if (! function_exists('handleDatabaseException')) {
    /**
     * Handle Laravel Database QueryException.
     * 
     * @param \Illuminate\Database\QueryException $e
     * @return array{data: null, error: string}
     */
    function handleDatabaseException(\Illuminate\Database\QueryException $e): array
    {
        $error = 'Database error occurred';
        
        if (app()->environment('local', 'testing')) {
            $error = $e->getMessage();
        }
        
        return [
            'data' => null,
            'error' => $error,
        ];
    }
}

if (! function_exists('handleInvalidArgumentException')) {
    /**
     * Handle InvalidArgumentException.
     * 
     * @param \InvalidArgumentException $e
     * @return array{data: null, error: string}
     */
    function handleInvalidArgumentException(\InvalidArgumentException $e): array
    {
        return [
            'data' => null,
            'error' => 'Invalid argument: ' . $e->getMessage(),
        ];
    }
}

if (! function_exists('handleGenericException')) {
    /**
     * Handle generic Exception.
     * 
     * @param \Exception $e
     * @return array{data: null, error: string}
     */
    function handleGenericException(\Exception $e): array
    {
        $error = 'An unexpected error occurred';
        
        if (app()->environment('local', 'testing')) {
            $error = $e->getMessage();
        }
        
        return [
            'data' => null,
            'error' => $error,
        ];
    }
}

if (! function_exists('handleThrowable')) {
    /**
     * Handle Throwable (critical errors).
     * 
     * @param \Throwable $e
     * @return array{data: null, error: string}
     */
    function handleThrowable(\Throwable $e): array
    {
        $error = 'A critical error occurred';
        
        if (app()->environment('local', 'testing')) {
            $error = $e->getMessage();
        }
        
        return [
            'data' => null,
            'error' => $error,
        ];
    }
}

if (! function_exists('parseHttpResponseError')) {
    /**
     * Parse HTTP response body to extract error message.
     * 
     * @param string $body Response body
     * @param int $statusCode HTTP status code
     * @param string $fallbackType Fallback error type
     * @return string Parsed error message
     */
    function parseHttpResponseError(string $body, int $statusCode, string $fallbackType): string
    {
        // Try to decode JSON response
        $decoded = json_decode($body, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($decoded['message'])) {
                return $decoded['message'];
            }
            
            if (isset($decoded['error'])) {
                return $decoded['error'];
            }
        }
        
        // Use raw body if not empty
        if (!empty($body)) {
            return $body;
        }
        
        // Fallback to status code message
        return "HTTP {$statusCode} {$fallbackType}";
    }
}
