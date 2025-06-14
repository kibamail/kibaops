<?php

namespace App\Models;

use App\Services\Vault\VaultService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Workspace extends Model
{
    use HasFactory;

    protected VaultService $vault;

    protected $fillable = [
        'name',
        'slug',
        'user_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($workspace) {
            $slug = Str::slug($workspace->name);
            $count = static::where('slug', 'like', "{$slug}%")->count();

            if ($count > 0) {
                $workspace->slug = $slug.'-'.Str::random(6);
            } else {
                $workspace->slug = $slug;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(WorkspaceMembership::class);
    }

    public function createMemberships(array $emails, array $projectIds, string $role): array
    {
        $createdMemberships = [];

        DB::transaction(function () use ($emails, $projectIds, $role, &$createdMemberships) {
            foreach ($emails as $email) {
                $user = User::where('email', $email)->first();

                $membership = WorkspaceMembership::updateOrCreate(
                    [
                        'workspace_id' => $this->id,
                        'email' => $email,
                    ],
                    [
                        'user_id' => $user?->id,
                        'role' => $role,
                    ]
                );

                $membership->projects()->sync($projectIds);
                $createdMemberships[] = $membership->load(['user', 'projects']);
            }
        });

        return $createdMemberships;
    }

    public function vault(): VaultService {
        if (isset($this->vault)) {
            return $this->vault;
        }

        $this->vault = app(VaultService::class)->base("secrets/data/workspaces/{$this->id}");

        return $this->vault;
    }
}
