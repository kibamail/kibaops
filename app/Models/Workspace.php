<?php

namespace App\Models;

use App\Services\Vault\VaultService;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Workspace extends Model
{
    use HasFactory, HasUuid;

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

    /**
     * Get the cloud providers for the workspace.
     */
    public function cloudProviders(): HasMany
    {
        return $this->hasMany(CloudProvider::class);
    }

    /**
     * Create or update memberships for a set of emails and associate them with projects.
     * This method efficiently handles bulk operations using upsert and eager loading
     * to minimize database queries while maintaining data consistency.
     */
    public function createMemberships(array $emails, array $projectIds, string $role): \Illuminate\Database\Eloquent\Collection
    {
        $usersByEmail = User::whereIn('email', $emails)->get()->keyBy('email');

        DB::transaction(function () use ($emails, $usersByEmail, $projectIds, $role) {
            foreach ($emails as $email) {
                $membership = WorkspaceMembership::updateOrCreate(
                    ['workspace_id' => $this->id, 'email' => $email],
                    ['user_id' => $usersByEmail->get($email)?->id, 'role' => $role]
                );

                $membership->projects()->sync($projectIds);
            }
        });

        $createdMemberships = $this->memberships()
            ->whereIn('email', $emails)
            ->with(['user', 'projects'])
            ->get();

        $this->sendInvitationNotifications($createdMemberships);

        return $createdMemberships;
    }

    /**
     * Create a new cloud provider for this workspace with secure credential storage.
     * The provider data is stored in the database while credentials are securely
     * stored in Vault using the provider's unique key path.
     */
    public function createCloudProvider(array $data, array $credentials): CloudProvider
    {
        $cloudProvider = $this->cloudProviders()->create($data);

        $this->vault()->writes()->store($cloudProvider->vault_key, $credentials);

        return $cloudProvider;
    }

    /**
     * Update an existing cloud provider with new data and/or credentials.
     * If credentials are provided, they overwrite the existing ones in Vault.
     * Database fields are only updated if data is provided.
     */
    public function updateCloudProvider(CloudProvider $cloudProvider, array $data, ?array $credentials = null): CloudProvider
    {
        if (! empty($data)) {
            $cloudProvider->update($data);
        }

        if ($credentials !== null) {
            $this->vault()->writes()->store($cloudProvider->vault_key, $credentials);
        }

        return $cloudProvider->fresh();
    }

    /**
     * Delete a cloud provider and clean up its credentials from Vault.
     * This performs a soft delete on the database record and attempts to
     * remove the credentials from Vault for complete cleanup.
     */
    public function deleteCloudProvider(CloudProvider $cloudProvider): bool
    {
        try {
            $this->vault()->reads()->secret($cloudProvider->vault_key);
            $this->vault()->writes()->remove($cloudProvider->vault_key);
        } catch (\Exception) {
        }

        return $cloudProvider->delete();
    }

    /**
     * Send invitation notifications to users who have been added to the workspace.
     * Only sends notifications to users who already exist in the system and
     * have a user_id associated with their membership.
     */
    private function sendInvitationNotifications(\Illuminate\Database\Eloquent\Collection $memberships): void
    {
        $memberships->filter(fn ($membership) => $membership->user_id !== null)
            ->each(function ($membership) {
                $membership->user->notify(new \App\Notifications\WorkspaceInvitation($membership, $this));
            });
    }

    public function vault(): VaultService
    {
        if (isset($this->vault)) {
            return $this->vault;
        }

        $this->vault = app(VaultService::class)->base("/secrets/data/workspaces/{$this->id}");

        return $this->vault;
    }
}
