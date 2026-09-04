<?php

declare(strict_types=1);

namespace Modules\Agency\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Surveyor\Models\Surveyor;

/**
 * AgencySurveyorRegistration — registrasi surveyor (HO) ke agency (Disnaker).
 *
 * 1 baris per (agency_id, surveyor_id-HO); status pending|approved|rejected|review.
 * Cabang surveyor otomatis tercakup saat approved (relasi keluarga HO).
 */
class AgencySurveyorRegistration extends Model
{
    protected $table = 'agency_surveyor_registrations';

    protected $fillable = [
        'agency_id', 'surveyor_id', 'status',
        'requested_by', 'processed_by', 'processed_at', 'note',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function surveyor(): BelongsTo
    {
        return $this->belongsTo(Surveyor::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
