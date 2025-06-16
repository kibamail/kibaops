<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Delete Test SSH Key Job
 *
 * Deletes test SSH keys created during cloud provider credential
 * verification to clean up resources after verification is complete.
 */
class DeleteTestSshKeyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $token,
        public string $keyId
    ) {
    }

    public function handle(): void
    {
        $hetznerService = app('hetzner-cloud', ['token' => $this->token]);
        $hetznerService->deleteSshKey($this->keyId);
    }
}
