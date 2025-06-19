<?php

namespace App\Services\SourceCode\Providers\GitHub;

use Firebase\JWT\JWT;
use Github\AuthMethod as GithubAuthMethod;
use Github\Client as GithubClient;
use Github\HttpClient\Builder;

class GithubAuth
{
    public function client(string $installationId)
    {
        $jwt = JWT::encode(
            [
                'iat' => time(),
                'exp' => time() + 120,
                'iss' => config('services.github.app_id'),
            ],
            config('services.github.private_key'),
            'RS256'
        );

        $github = new GithubClient(new Builder, $installationId);

        $github->authenticate($jwt, null, GithubAuthMethod::JWT);

        /** @var \Github\Api\Apps $apps */
        $apps = $github->api('apps');

        $access = $apps->createInstallationToken($installationId);

        if (! isset($access['token'])) {
            return null;
        }

        $installation = $apps->getInstallation($installationId);

        $github->authenticate($access['token'], null, GithubAuthMethod::ACCESS_TOKEN);

        return [$github, new GithubApps($github), [
            'installation' => $installation,
        ]];
    }

    public function accessToken(string $installationId) {}
}
