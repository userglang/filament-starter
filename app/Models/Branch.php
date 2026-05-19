<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Branch extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_number',
        'branch_name',
        'address',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    // -------------------------------------------------------------------------
    // Boot
    // -------------------------------------------------------------------------

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Branch $branch) {
            if (empty($branch->id)) {
                $branch->id = (string) Str::uuid();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function users()
    {
        return $this->hasMany(User::class, 'branch_number', 'branch_number');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Find a branch by its short code.
     */
    public static function findByCode(string $code): ?static
    {
        return static::where('code', $code)->first();
    }

    /**
     * Find a branch by its branch number.
     */
    public static function findByNumber(string $number): ?static
    {
        return static::where('branch_number', $number)->first();
    }

    public function __toString(): string
    {
        return "{$this->branch_name} ({$this->code})";
    }
}
