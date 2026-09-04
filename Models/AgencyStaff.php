<?php

namespace Modules\Agency\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class AgencyStaff extends Model
{
    protected $table = 'agency_staffs';

    protected $fillable = [
        'user_id',
        'agency_id',
        'unit_id',
        'realname',
        'nip',
        'jabatan',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'agency_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'unit_id');
    }
}
