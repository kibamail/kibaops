<?php

namespace App\Http\Controllers\SourceCode;

use App\Enums\SourceCodeProviderType;
use App\Http\Controllers\Controller;
use App\Services\SourceCode\SourceCodeProviderFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SourceCodeWebhookController extends Controller
{
    /**
     * Handle incoming webhooks from source code providers
     *
     * Validates webhook signature and processes the event payload.
     * Currently supports GitHub webhooks with plans for GitLab
     * and Bitbucket support.
     */
    public function handle(Request $request, string $provider): JsonResponse
    {
        $providerType = $this->getProviderType($provider);

        if (! $providerType) {
            return response()->json(['error' => 'Unsupported provider'], 400);
        }

        $payload = $request->getContent();
        $signature = $this->getSignatureHeader($request, $provider);
        $event = $this->getEventHeader($request, $provider);

        $providerService = SourceCodeProviderFactory::create($providerType);

        if (! $providerService->webhooks()->validateSignature($payload, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payloadData = json_decode($payload, true);

        if ($payloadData) {
            $payloadData["_{$provider}_event"] = $event;
            $providerService->webhooks()->parse(json_encode($payloadData));
        }

        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Convert provider string to SourceCodeProviderType enum
     */
    private function getProviderType(string $provider): ?SourceCodeProviderType
    {
        return match (strtolower($provider)) {
            'github' => SourceCodeProviderType::GITHUB,
            default => null,
        };
    }

    /**
     * Get the signature header based on provider
     */
    private function getSignatureHeader(Request $request, string $provider): ?string
    {
        return match (strtolower($provider)) {
            'github' => $request->header('X-Hub-Signature-256'),
            'gitlab' => $request->header('X-Gitlab-Token'),
            'bitbucket' => $request->header('X-Hub-Signature'),
            default => null,
        };
    }

    /**
     * Get the event header based on provider
     */
    private function getEventHeader(Request $request, string $provider): ?string
    {
        return match (strtolower($provider)) {
            'github' => $request->header('X-GitHub-Event'),
            'gitlab' => $request->header('X-Gitlab-Event'),
            'bitbucket' => $request->header('X-Event-Key'),
            default => null,
        };
    }
}
