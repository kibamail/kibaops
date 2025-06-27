<?php

namespace App\Services;

/**
 * SSH Key Generator Service
 *
 * Generates ED25519 SSH key pairs in memory using PHP's sodium extension
 * for cloud provider authentication and verification.
 */
class SshKeyGenerator
{
    /**
     * Generate an ED25519 SSH key pair in memory.
     *
     * @param  string  $email  Email address for the key comment
     * @return array Array containing 'public_key' and 'private_key'
     */
    public static function ed25519pair(string $email = 'cloud@kibaops.com'): array
    {
        $keyPair = sodium_crypto_sign_keypair();
        $privateKey = sodium_crypto_sign_secretkey($keyPair);
        $publicKey = sodium_crypto_sign_publickey($keyPair);

        $sshPublicKey = 'ssh-ed25519 ' . base64_encode(
            pack('N', 11) . 'ssh-ed25519' .
            pack('N', 32) . $publicKey
        ) . ' ' . $email;

        $sshPrivateKey = self::formatPrivateKey($privateKey);

        return [
            'public_key' => $sshPublicKey,
            'private_key' => $sshPrivateKey,
        ];
    }

    /**
     * Format raw private key bytes into OpenSSH private key format.
     *
     * @param  string  $privateKeyBytes  Raw private key bytes
     * @return string Formatted OpenSSH private key
     */
    private static function formatPrivateKey(string $privateKeyBytes): string
    {
        return '-----BEGIN OPENSSH PRIVATE KEY-----' . "\n" .
               chunk_split(base64_encode($privateKeyBytes), 70, "\n") .
               '-----END OPENSSH PRIVATE KEY-----' . "\n";
    }

    /**
     * Generate an ED25519 SSH public key.
     *
     * @param  string  $email  Email address for the key comment
     * @return string SSH public key in OpenSSH format
     */
    public static function publicKey(string $email = 'cloud@kibaops.com'): string
    {
        return self::ed25519pair($email)['public_key'];
    }
}
