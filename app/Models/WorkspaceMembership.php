<?php

namespace App\Models;

use App\Enums\WorkspaceMembershipRole;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WorkspaceMembership extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'email',
        'role',
    ];

    protected $casts = [
        'role' => WorkspaceMembershipRole::class,
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'workspace_membership_projects')
            ->using(WorkspaceMembershipProjectPivot::class)
            ->withTimestamps();
    }
}
