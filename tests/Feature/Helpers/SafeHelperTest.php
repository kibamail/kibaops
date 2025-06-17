<?php

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Validation\ValidationException;

test('safe helper returns data when callback succeeds', function () {
    $result = safe(function () {
        return 'success';
    });

    expect($result)->toBe([
        'data' => 'success',
        'error' => null,
    ]);
});

test('safe helper returns array data when callback succeeds', function () {
    $result = safe(function () {
        return ['key' => 'value'];
    });

    expect($result)->toBe([
        'data' => ['key' => 'value'],
        'error' => null,
    ]);
});

test('safe helper catches GuzzleHttp ClientException with JSON response', function () {
    $response = new Response(400, [], json_encode(['message' => 'Bad request error']));
    $request = new Request('GET', 'http://example.com');
    
    $result = safe(function () use ($response, $request) {
        throw new ClientException('Client error', $request, $response);
    });

    expect($result)->toBe([
        'data' => null,
        'error' => 'Bad request error',
    ]);
});

test('safe helper catches GuzzleHttp ClientException with error key in JSON', function () {
    $response = new Response(400, [], json_encode(['error' => 'Invalid credentials']));
    $request = new Request('GET', 'http://example.com');
    
    $result = safe(function () use ($response, $request) {
        throw new ClientException('Client error', $request, $response);
    });

    expect($result)->toBe([
        'data' => null,
        'error' => 'Invalid credentials',
    ]);
});

test('safe helper catches GuzzleHttp ClientException with plain text response', function () {
    $response = new Response(400, [], 'Plain text error message');
    $request = new Request('GET', 'http://example.com');
    
    $result = safe(function () use ($response, $request) {
        throw new ClientException('Client error', $request, $response);
    });

    expect($result)->toBe([
        'data' => null,
        'error' => 'Plain text error message',
    ]);
});

test('safe helper catches GuzzleHttp ServerException', function () {
    $response = new Response(500, [], json_encode(['message' => 'Internal server error']));
    $request = new Request('GET', 'http://example.com');
    
    $result = safe(function () use ($response, $request) {
        throw new ServerException('Server error', $request, $response);
    });

    expect($result)->toBe([
        'data' => null,
        'error' => 'Internal server error',
    ]);
});

test('safe helper catches GuzzleHttp ConnectException', function () {
    $request = new Request('GET', 'http://example.com');
    
    $result = safe(function () use ($request) {
        throw new ConnectException('Connection timeout', $request);
    });

    expect($result)->toBe([
        'data' => null,
        'error' => 'Connection failed: Connection timeout',
    ]);
});

test('safe helper catches ValidationException', function () {
    $result = safe(function () {
        throw ValidationException::withMessages([
            'email' => ['The email field is required.'],
        ]);
    });

    expect($result)->toBe([
        'data' => null,
        'error' => 'The email field is required.',
    ]);
});

test('safe helper catches InvalidArgumentException', function () {
    $result = safe(function () {
        throw new InvalidArgumentException('Invalid provider type');
    });

    expect($result)->toBe([
        'data' => null,
        'error' => 'Invalid argument: Invalid provider type',
    ]);
});

test('safe helper catches generic Exception in local environment', function () {
    app()->instance('env', 'local');
    
    $result = safe(function () {
        throw new Exception('Something went wrong');
    });

    expect($result)->toBe([
        'data' => null,
        'error' => 'Something went wrong',
    ]);
});

test('safe helper masks generic Exception in production environment', function () {
    app()->instance('env', 'production');

    $result = safe(function () {
        throw new Exception('Sensitive error details');
    });

    expect($result)->toBe([
        'data' => null,
        'error' => 'An unexpected error occurred',
    ]);
});

test('safe helper works with cloud provider operations', function () {
    $result = safe(function () {
        $provider = app(\App\Services\CloudProviders\CloudProviderFactory::class)
            ->create(\App\Enums\CloudProviderType::HETZNER);

        return $provider->verify(['test-token']);
    });

    expect($result['error'])->toBeNull();
    expect($result['data'])->toBeInstanceOf(\App\Services\CloudProviders\CloudProviderResponse::class);
    expect($result['data']->success)->toBeFalse(); // Should fail with test token
});
