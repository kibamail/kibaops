<?php

namespace Tests\Support;

use App\Http\Controllers\Controller;

/**
 * Test controller to access protected methods from base Controller
 */
class TestController extends Controller
{
    public function testGetActiveWorkspaceId(): ?string
    {
        return $this->getActiveWorkspaceId();
    }

    public function testGetActiveWorkspace(): ?\App\Models\Workspace
    {
        return $this->getActiveWorkspace();
    }
}
