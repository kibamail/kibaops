<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceMembershipProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_membership_id',
        'project_id',
    ];

    public function workspaceMembership(): BelongsTo
    {
        return $this->belongsTo(WorkspaceMembership::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
