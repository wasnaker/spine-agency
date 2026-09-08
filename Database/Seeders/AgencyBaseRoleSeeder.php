<?php

namespace Modules\Agency\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AgencyBaseRoleSeeder extends Seeder
{
    /**
     * Base role untuk semua staff agency. Multirole: assignRole menambah,
     * tidak menghapus role lain (admin-dinas, pengawas, dll tetap utuh).
     * Idempotent (unique model_id+role_id).
     */
    public function run(): void
    {
        $userIds = \DB::table('agency_staffs')->pluck('user_id')->unique();

        User::whereIn('id', $userIds)->get()
            ->each(fn ($user) => $user->assignRole('agency'));
    }
}
