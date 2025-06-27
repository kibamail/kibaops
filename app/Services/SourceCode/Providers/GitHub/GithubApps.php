<?php

namespace App\Services\SourceCode\Providers\GitHub;

use Github\Api\Apps;

class GithubApps extends Apps
{
    private function configurePreviewHeader()
    {
        $this->acceptHeaderValue = 'application/vnd.github.machine-man-preview+json';
    }

    public function allRepositories()
    {
        $this->configurePreviewHeader();

        return $this->get('/installation/repositories', [
            'per_page' => 100,
        ]);
    }
}
