<?php

declare(strict_types=1);

namespace Modules\Agency\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Agency\Models\Agency;
use Modules\Agency\Models\AgencyJurisdiction;
use Modules\Agency\Models\AgencySurveyorRegistration;
use Modules\Customer\Models\Customer;
use Modules\Region\Models\Regency;
use Modules\Surveyor\Models\Surveyor;
use Spine\Services\ActivityLogService;

class AgencyController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    /**
     * Resolve entity surveyor user (HO di-resolve ke row induknya) — by admin_id.
     */
    private function surveyorHo(?int $userId): ?Surveyor
    {
        if (! $userId) {
            return null;
        }
        $entity = Surveyor::where('admin_id', $userId)->first();

        return $entity?->type === 'branch' ? Surveyor::find($entity->parent_id) : $entity;
    }

    /**
     * Resolve entity agency user (row agency/unit miliknya, by admin_id).
     */
    private function agencyOf(?int $userId): ?Agency
    {
        if (! $userId) {
            return null;
        }

        return Agency::where('admin_id', $userId)->first();
    }

    /**
     * Registrasi surveyor (HO) ke agency ini — kerja lintas dinas.
     * 1 baris per (agency, HO): cabang otomatis tercakup saat approved.
     */
    public function register(int $id, Request $request): JsonResponse
    {
        $agency = Agency::find($id);
        if (! $agency) {
            return response()->json(['message' => 'Agency not found'], 404);
        }
        if ($agency->type !== 'agency') {
            return response()->json(['message' => 'Registrasi hanya ke Disnaker (agency).'], 422);
        }
        if (! $agency->is_active) {
            return response()->json(['message' => 'Disnaker non-aktif tidak menerima registrasi.'], 422);
        }

        $ho = $this->surveyorHo($request->user()?->id);
        if (! $ho) {
            return response()->json(['message' => 'Akun tidak terikat ke entity surveyor.'], 403);
        }

        $reg = AgencySurveyorRegistration::where('agency_id', $agency->id)
            ->where('surveyor_id', $ho->id)
            ->first();

        if ($reg && in_array($reg->status, ['pending', 'review'], true)) {
            return response()->json(['message' => 'Registrasi sedang menunggu review.'], 409);
        }
        if ($reg && $reg->status === 'approved') {
            return response()->json(['message' => 'Sudah terdaftar di Disnaker ini.'], 409);
        }

        if ($reg) { // rejected -> daftar ulang
            $reg->update([
                'status'       => 'pending',
                'requested_by' => $request->user()->id,
                'processed_by' => null,
                'processed_at' => null,
                'note'         => null,
            ]);
        } else {
            $reg = AgencySurveyorRegistration::create([
                'agency_id'    => $agency->id,
                'surveyor_id'  => $ho->id,
                'status'       => 'pending',
                'requested_by' => $request->user()->id,
            ]);
        }

        Log::info('[AgencySurveyor] registered', [
            'agency_id' => $agency->id, 'surveyor_id' => $ho->id, 'by' => $request->user()->id,
        ]);

        return response()->json($reg->fresh(), 201);
    }

    /**
     * Daftar registrasi surveyor utk agency ini (agency-admin / surveyor).
     * Agency-admin (pemilik) & platform lihat SEMUA baris; surveyor (register
     * lintas dinas) hanya melihat baris milik HO-nya sendiri.
     */
    public function registrations(int $id, Request $request): JsonResponse
    {
        $agency = Agency::find($id);
        if (! $agency) {
            return response()->json(['message' => 'Agency not found'], 404);
        }

        $query = AgencySurveyorRegistration::with([
            'surveyor:id,code,name,email,phone,address,is_active,type,parent_id,admin_id',
            'surveyor.admin:id,name,email',
            'surveyor.province:id,name', 'surveyor.regency:id,name',
            'requestedBy:id,name', 'processedBy:id,name',
        ])->where('agency_id', $agency->id);

        // Caller surveyor (bukan approver): hanya baris registrasi milik HO-nya.
        if (! $request->user()->can('agency:approve-surveyor-registration')) {
            $ho = $this->surveyorHo($request->user()?->id);
            if (! $ho) {
                return response()->json(['message' => 'Akun tidak terikat ke entity surveyor.'], 403);
            }
            $query->where('surveyor_id', $ho->id);
        }

        $rows = $query
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'review' THEN 1 WHEN 'approved' THEN 2 ELSE 3 END")
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $rows]);
    }

    /**
     * Decide registrasi: approved | rejected | review (agency-admin / platform).
     */
    public function decide(int $id, int $regId, Request $request): JsonResponse
    {
        $action = $request->validate([
            'action' => ['required', 'string', 'in:approved,rejected,review'],
            'note'   => ['nullable', 'string', 'max:1024'],
        ])['action'];
        $note = $request->string('note')->trim()->toString() ?: null;

        $reg = AgencySurveyorRegistration::where('agency_id', $id)->find($regId);
        if (! $reg) {
            return response()->json(['message' => 'Registrasi tidak ditemukan.'], 404);
        }

        // Otorisasi dulu: hanya super-admin (role admin) ATAU admin agency PEMILIK row.
        $isSuper = $request->user()->hasRole('admin');
        $me = $this->agencyOf($request->user()?->id);
        if (! $isSuper && (! $me || $me->id !== $id)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (! in_array($reg->status, ['pending', 'review'], true)) {
            return response()->json(['message' => 'Registrasi sudah diputus (' . $reg->status . ').'], 422);
        }
        if ($action === 'rejected' && ! $note) {
            return response()->json(['message' => 'Alasan wajib diisi saat menolak.'], 422);
        }

        $reg->update([
            'status'       => $action,
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
            'note'         => $note,
        ]);

        Log::info('[AgencySurveyor] decided', [
            'reg_id' => $reg->id, 'agency_id' => $id, 'action' => $action, 'by' => $request->user()->id,
        ]);

        return response()->json($reg->fresh());
    }

    public function index(Request $request): JsonResponse
    {
        $query = Agency::with(['parent:id,code,name', 'admin:id,name', 'province:id,name', 'regency:id,name']);

        // Caller surveyor (agency:surveyor-register tanpa agency:view): hanya
        // Disnaker (type=agency) yang tampil, plus status registrasinya.
        $surveyorView = ! $request->user()->can('agency:view');
        if ($surveyorView) {
            $query->where('type', 'agency');
        }

        if ($request->filled('q')) {
            $term = $request->string('q');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->has('type')) {
            $type = $request->query('type');
            if ($type === 'agency') {
                $query->where('type', 'agency');
            } elseif ($type === 'unit') {
                $query->where('type', 'unit');
            }
        }

        $query->orderByRaw("CASE WHEN type = 'agency' THEN 0 ELSE 1 END")->orderByDesc('id');

        $items = $query->get();

        // Caller surveyor: lampirkan status registrasi HO-nya per Disnaker.
        if ($surveyorView) {
            $ho = $this->surveyorHo($request->user()?->id);
            $statuses = $ho
                ? AgencySurveyorRegistration::where('surveyor_id', $ho->id)
                    ->whereIn('agency_id', $items->pluck('id'))
                    ->pluck('status', 'agency_id')
                : collect();
            $items->each(function ($a) use ($statuses) {
                $a->registration_status = $statuses[$a->id] ?? null;
            });
        }

        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'       => ['sometimes', 'string', 'in:agency,unit'],
            'code'       => ['required', 'string', 'max:64'],
            'name'       => ['required', 'string', 'max:190'],
            'email'      => ['nullable', 'string', 'email', 'max:190'],
            'phone'      => ['nullable', 'string', 'max:32'],
            'address'    => ['nullable', 'string'],
            'province_id'=> ['nullable', 'integer', 'exists:provinces,id'],
            'regency_id' => ['nullable', 'integer', 'exists:regencies,id'],
            'is_active'  => ['sometimes', 'boolean'],
            'parent_id'  => ['nullable', 'integer'],
        ]);

        $type = $validated['type'] ?? 'agency';
        $parent = $validated['parent_id'] ?? null;

        if (! in_array($type, ['agency', 'unit'])) {
            $type = 'agency';
        }
        if ($type === 'unit' && ! $parent) {
            return response()->json(['message' => 'Unit harus memiliki parent.'], 422);
        }
        if ($type === 'agency' && $parent) {
            return response()->json(['message' => 'Agency HO tidak boleh memiliki parent.'], 422);
        }

        $code = $validated['code'];
        $check = Agency::where('parent_id', $parent)
            ->where('code', $code)
            ->where('deleted_at', null)
            ->first();
        if ($check) {
            return response()->json(['message' => "Code {$code} sudah ada untuk parent ini."], 422);
        }

        $entity = Agency::create(array_merge($validated, [
            'type'     => $type,
            'parent_id'=> $parent,
        ]));

        Log::info('[Agency] created', ['id' => $entity->id, 'code' => $entity->code, 'type' => $type]);

        return response()->json($entity, 201);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        // Surveyor (tanpa agency:view): hanya bisa buka Disnaker yang
        // registrasinya SUDAH approved (akses data saat terhubung).
        if (! $request->user()->can('agency:view')) {
            $ho = $this->surveyorHo($request->user()?->id);
            $ok = $ho && AgencySurveyorRegistration::where('agency_id', $id)
                ->where('surveyor_id', $ho->id)
                ->where('status', 'approved')
                ->exists();
            if (! $ok) {
                return response()->json(['message' => 'Agency not found'], 404);
            }
        }

        $entity = Agency::with(['units.parent:id,code,name', 'units.admin:id,name', 'units.province:id,name', 'units.regency:id,name', 'parent:id,code,name', 'admin:id,name', 'province:id,name', 'regency:id,name'])->find($id);

        if (! $entity) {
            return response()->json(['message' => 'Agency not found'], 404);
        }

        return response()->json($entity);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $entity = Agency::find($id);

        if (! $entity) {
            return response()->json(['message' => 'Agency not found'], 404);
        }

        $validated = $request->validate([
            'type'       => ['sometimes', 'string', 'in:agency,unit'],
            'code'       => ['sometimes', 'string', 'max:64'],
            'name'       => ['sometimes', 'string', 'max:190'],
            'email'      => ['nullable', 'string', 'email', 'max:190'],
            'phone'      => ['nullable', 'string', 'max:32'],
            'address'    => ['nullable', 'string'],
            'province_id'=> ['nullable', 'integer', 'exists:provinces,id'],
            'regency_id' => ['nullable', 'integer', 'exists:regencies,id'],
            'is_active'  => ['sometimes', 'boolean'],
            'parent_id'  => ['nullable', 'integer'],
        ]);

        if (array_key_exists('type', $validated) && $validated['type'] !== $entity->type) {
            return response()->json(['message' => 'Tidak boleh mengubah type setelah row dibuat.'], 422);
        }
        if (array_key_exists('parent_id', $validated) && $validated['parent_id'] !== $entity->parent_id) {
            return response()->json(['message' => 'Tidak boleh mengubah parent setelah row dibuat.'], 422);
        }
        if (array_key_exists('code', $validated)) {
            $newCode = $validated['code'];
            $dup = Agency::where('id', '!=', $entity->id)
                ->where('parent_id', $entity->parent_id)
                ->where('code', $newCode)
                ->where('deleted_at', null)
                ->first();
            if ($dup) {
                return response()->json(['message' => "Code {$newCode} sudah ada untuk parent ini."], 422);
            }
        }

        $entity->update($validated);

        Log::info('[Agency] updated', ['id' => $entity->id, 'code' => $entity->code, 'type' => $entity->type]);

        return response()->json($entity);
    }

    public function destroy(int $id): JsonResponse
    {
        $entity = Agency::find($id);

        if (! $entity) {
            return response()->json(['message' => 'Agency not found'], 404);
        }

        $entity->delete();

        return response()->json(['message' => 'Agency deleted']);
    }

    public function units(int $id): JsonResponse
    {
        $parent = Agency::find($id);

        if (! $parent) {
            return response()->json(['message' => 'Agency not found'], 404);
        }

        return response()->json(['data' => $parent->units()->with(['parent:id,code,name', 'admin:id,name', 'province:id,name', 'regency:id,name'])->get()]);
    }

    public function companies(int $id): JsonResponse
    {
        $agency = Agency::find($id);

        if (! $agency) {
            return response()->json(['message' => 'Agency not found'], 404);
        }

        // Read-only cross-module lookup: no writes to the customers table.
        $query = Customer::whereIn('type', ['customer', 'branch'])
            ->with(['province:id,name', 'regency:id,name'])
            ->orderBy('type')->orderBy('name');

        if ($agency->type === 'unit') {
            // Unit: semua kab/kota yang menjadi jurisdiction unit ini.
            $jurisdictions = AgencyJurisdiction::where('unit_id', $agency->id)->pluck('regency_id');

            if ($jurisdictions->isEmpty()) {
                return response()->json(['data' => []]);
            }

            $query->whereIn('regency_id', $jurisdictions);
        } elseif ($agency->province_id) {
            // Disnaker (level provinsi): semua company di provinsi tsb.
            $query->where('province_id', $agency->province_id);
        } else {
            return response()->json(['data' => []]);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function activityLogs(int $id): JsonResponse
    {
        if (! Agency::find($id)) {
            return response()->json(['message' => 'Agency not found'], 404);
        }

        $logs = $this->activityLog
            ->query()
            ->where('subject_type', Agency::class)
            ->where('subject_id', $id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($log) => [
                'id'         => $log->id,
                'description'=> $log->description,
                'causer'     => $log->causer?->name ?? 'System',
                'properties' => $log->properties,
                'at'         => $log->created_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $logs]);
    }

    // ────────────────────────────────────────────────────────────────────
    // JURISDICTIONS (wilayah kerja unit)
    // 1 regency = 1 unit (UNIQUE regency_id di tabel agency_jurisdictions).
    // ────────────────────────────────────────────────────────────────────

    /**
     * Daftar wilayah kerja sebuah unit.
     * Dengan ?available=1 → daftar kab/kota yang BELUM dipakai unit mana pun.
     */
    public function jurisdictions(int $id, Request $request): JsonResponse
    {
        $entity = Agency::find($id);

        if (! $entity) {
            return response()->json(['message' => 'Agency not found'], 404);
        }

        if ($request->boolean('available')) {
            $taken = AgencyJurisdiction::pluck('regency_id');

            // Kab/kota yang bisa ditambah: satu provinsi dengan unit
            // (provinsi unit -> provinsi Disnaker induk -> provinsi jurisdiction pertama).
            $provinceId = $entity->province_id
                ?? $entity->parent?->province_id
                ?? $entity->jurisdictions()->with('regency:id,province_id')->first()?->regency?->province_id;

            $query = Regency::with('province:id,name');
            if ($provinceId) {
                $query->where('province_id', $provinceId);
            }

            return response()->json([
                'data' => $query->whereNotIn('id', $taken)->orderBy('name')->get(['id', 'name', 'province_id']),
            ]);
        }

        return response()->json([
            'data' => $entity->jurisdictions()
                ->with('regency:id,name,province_id', 'regency.province:id,name')
                ->orderBy('id')
                ->get(),
        ]);
    }

    /**
     * Attach satu/lebih regency ke unit. Tolak yang sudah dipakai unit lain.
     * Body: { regency_ids: int[] }
     */
    public function storeJurisdictions(int $id, Request $request): JsonResponse
    {
        $entity = Agency::find($id);

        if (! $entity) {
            return response()->json(['message' => 'Agency not found'], 404);
        }
        if ($entity->type !== 'unit') {
            return response()->json(['message' => 'Jurisdiction hanya bisa dimiliki Unit.'], 422);
        }

        $validated = $request->validate([
            'regency_ids'   => ['required', 'array', 'min:1'],
            'regency_ids.*' => ['integer'],
        ]);

        $taken = AgencyJurisdiction::whereIn('regency_id', $validated['regency_ids'])->pluck('regency_id');
        if ($taken->isNotEmpty()) {
            return response()->json([
                'message'     => 'Ada wilayah yang sudah menjadi jurisdiction unit lain.',
                'regency_ids' => $taken->values(),
            ], 422);
        }

        // Wilayah harus satu provinsi dengan unit (fallback provinsi induk).
        $provinceId = $entity->province_id ?? $entity->parent?->province_id;
        if ($provinceId) {
            $foreign = Regency::whereIn('id', $validated['regency_ids'])
                ->where('province_id', '!=', $provinceId)
                ->pluck('name');
            if ($foreign->isNotEmpty()) {
                return response()->json([
                    'message' => 'Wilayah dari provinsi lain: '.$foreign->implode(', ').'. Wilayah kerja unit harus satu provinsi.',
                ], 422);
            }
        }

        $rows = array_map(fn ($rid) => ['unit_id' => $entity->id, 'regency_id' => $rid, 'created_at' => now(), 'updated_at' => now()], $validated['regency_ids']);
        AgencyJurisdiction::insert($rows);

        return response()->json(['data' => AgencyJurisdiction::where('unit_id', $entity->id)->with('regency:id,name,province_id')->get()], 201);
    }

    /** Detach satu regency dari unit (wilayah kembali bebas). */
    public function destroyJurisdiction(int $id, int $regencyId): JsonResponse
    {
        $entity = Agency::find($id);

        if (! $entity) {
            return response()->json(['message' => 'Agency not found'], 404);
        }

        $row = AgencyJurisdiction::where('unit_id', $entity->id)->where('regency_id', $regencyId)->first();
        if (! $row) {
            return response()->json(['message' => 'Wilayah bukan jurisdiction unit ini.'], 404);
        }

        $row->delete();

        return response()->json(['message' => 'Jurisdiction removed']);
    }

    /**
     * Pindahkan wilayah dari unit asal ke unit tujuan (atomik).
     * Body: { regency_id, from_unit_id, to_unit_id }
     */
    public function moveJurisdiction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'regency_id'   => ['required', 'integer'],
            'from_unit_id' => ['required', 'integer'],
            'to_unit_id'   => ['required', 'integer', 'different:from_unit_id'],
        ]);

        $row = AgencyJurisdiction::where('unit_id', $validated['from_unit_id'])
            ->where('regency_id', $validated['regency_id'])
            ->first();
        if (! $row) {
            return response()->json(['message' => 'Wilayah bukan jurisdiction unit asal.'], 422);
        }

        $target = Agency::find($validated['to_unit_id']);
        if (! $target || $target->type !== 'unit') {
            return response()->json(['message' => 'Unit tujuan tidak valid.'], 422);
        }

        $row->update(['unit_id' => $validated['to_unit_id']]);

        return response()->json(['message' => 'Jurisdiction moved']);
    }
}
