<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Workspace extends Model
{
    use HasFactory;

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
                $workspace->slug = $slug . '-' . Str::random(6);
            } else {
                $workspace->slug = $slug;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
