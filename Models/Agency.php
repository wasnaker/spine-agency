<?php

declare(strict_types=1);

namespace Modules\Agency\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Modules\Agency\Models\AgencyJurisdiction;
use Modules\Region\Models\Province;
use Modules\Region\Models\Regency;
use Spine\Traits\HasLifecycleHooks;

class Agency extends Model
{
    use HasLifecycleHooks;
    use HasUlids;
    use SoftDeletes;

    protected $table = 'agencies';

    protected $fillable = [
        'type',
        'code', 'name', 'email', 'phone',
        'address', 'province_id', 'regency_id', 'is_active', 'parent_id', 'admin_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'type' => 'agency',
    ];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function units(): HasMany
    {
        return $this->hasMany(Agency::class, 'parent_id')->where('type', 'unit');
    }

    public function jurisdictions(): HasMany
    {
        return $this->hasMany(AgencyJurisdiction::class, 'unit_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'parent_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function staffs(): HasMany
    {
        return $this->hasMany(AgencyStaff::class, 'agency_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public static function labels(): array
    {
        return [
            'type'        => 'Tipe',
            'code'        => 'Kode',
            'name'        => 'Nama',
            'email'       => 'Email',
            'phone'       => 'Telepon',
            'address'     => 'Alamat',
            'province_id' => 'Provinsi',
            'regency_id'  => 'Kota',
            'is_active'   => 'Aktif',
        ];
    }

    public function isUnit(): bool
    {
        return $this->type === 'unit';
    }

    public function scopeAgencyOnly($query)
    {
        return $query->where('type', 'agency');
    }

    public function scopeUnitOnly($query)
    {
        return $query->where('type', 'unit');
    }
}
