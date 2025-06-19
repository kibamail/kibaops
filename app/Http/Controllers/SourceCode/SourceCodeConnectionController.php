<?php

namespace App\Http\Controllers\SourceCode;

use App\Enums\SourceCodeProviderType;
use App\Http\Controllers\Controller;
use App\Services\SourceCode\SourceCodeProviderFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SourceCodeConnectionController extends Controller
{
    public function initiate(Request $request, string $provider): RedirectResponse
    {
        $activeWorkspaceId = $this->getActiveWorkspaceId();

        if (! $activeWorkspaceId) {
            return redirect()->back()->with('error', 'No workspace found');
        }

        $providerType = $this->getProviderType($provider);

        if (! $providerType) {
            return redirect()->back()->with('error', 'Unsupported provider');
        }

        $providerService = SourceCodeProviderFactory::create($providerType);

        $response = $providerService->connection()->initiate([], oauth_state_encode([
            'workspace_id' => $activeWorkspaceId,
            'origin_url' => $request->headers->get('referer') ?: route('dashboard'),
        ]));

        if ($response->success) {
            return redirect($response->metadata['redirect_url']);
        }

        return redirect()->back()->with('error', $response->error);
    }

    /**
     * Handle source code provider connection callback
     *
     * Processes the callback from the provider after user completes
     * the authorization flow. Exchanges authorization code/installation
     * for access credentials and stores connection details.
     */
    public function callback(Request $request, string $provider): RedirectResponse
    {
        $providerType = $this->getProviderType($provider);

        $workspace = $this->getActiveWorkspace();

        if ($workspace == null) {
            return redirect()->route('dashboard')->with('error', 'You do not have an active workspace to complete this connection.');
        }

        if (! $providerType) {
            return redirect()->route('dashboard')->with('error', 'Unsupported provider');
        }

        $providerService = SourceCodeProviderFactory::create($providerType);

        $state = oauth_state_decode($request->get('state'));

        $response = $providerService->connection()->complete(
            $request->get('installation_id') ?? $request->get('code'),
            $state
        );

        $origin = $state['origin_url'] ?? route('dashboard');

        if (! $response->success) {
            return redirect($origin)->with('error', $response->error);
        }

        $connection = $workspace->sourceCodeConnections()->create(
            $response->metadata['connection']
        );

        $connection->repositories()->createMany($response->metadata['repositories']);

        $providerName = ucfirst($provider);

        return redirect($origin)->with('success', "{$providerName} connection established successfully.");
    }

    /**
     * Convert provider string to SourceCodeProviderType enum
     *
     * Maps URL provider parameter to the corresponding enum value.
     * Returns null for unsupported providers.
     */
    private function getProviderType(string $provider): ?SourceCodeProviderType
    {
        return match (strtolower($provider)) {
            'github' => SourceCodeProviderType::GITHUB,
            default => null,
        };
    }
}
