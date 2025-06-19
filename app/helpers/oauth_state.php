<?php

/**
 * Encode OAuth state data into a base64-encoded JSON string.
 *
 * @param  array  $data  The data to encode (e.g., workspace_id, origin_url)
 * @return string The encoded state string
 */
function oauth_state_encode(array $data): string
{
    return base64_encode(json_encode($data));
}

/**
 * Decode OAuth state string back into an array.
 *
 * @param  string  $state  The base64-encoded state string
 * @return array The decoded data array, or empty array if decoding fails
 */
function oauth_state_decode(string $state): array
{
    if (empty($state)) {
        return [];
    }

    $decoded = base64_decode($state, true);

    if ($decoded === false) {
        return [];
    }

    $data = json_decode($decoded, true);
    if (! is_array($data)) {
        return [];
    }

    return $data;
}
