<?php

namespace App\Models;

use App\Enums\CloudProviderType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CloudProvider extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'workspace_id',
    ];

    protected $casts = [
        'type' => CloudProviderType::class,
    ];

    /**
     * Get the workspace that owns the cloud provider.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the Vault key path for storing this cloud provider's credentials.
     * The key follows the pattern "providers/{id}" for easy retrieval.
     */
    public function getVaultKeyAttribute(): string
    {
        return "providers/{$this->id}";
    }
}
