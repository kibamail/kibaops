<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\Pivot;

class WorkspaceMembershipProjectPivot extends Pivot
{
    use HasUuid;

    protected $table = 'workspace_membership_projects';
}
