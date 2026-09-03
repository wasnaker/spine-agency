<?php

declare(strict_types=1);

namespace Modules\Agency\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Region\Models\Regency;

/**
 * AgencyJurisdiction — wilayah kerja (kab/kota) milik sebuah Unit.
 *
 * 1 Unit (type='unit') punya banyak jurisdiction; 1 jurisdiction
 * (regency) milik tepat 1 Unit — UNIQUE(regency_id) di level DB.
 */
class AgencyJurisdiction extends Model
{
    protected $table = 'agency_jurisdictions';

    protected $fillable = ['unit_id', 'regency_id'];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Agency::class, 'unit_id');
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }
}
