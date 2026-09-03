<?php

declare(strict_types=1);

namespace Modules\Agency\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Agency\Models\Agency;
use Spine\Services\ActivityLogService;

class AgencyController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function index(Request $request): JsonResponse
    {
        $query = Agency::with(['parent:id,code,name', 'admin:id,name', 'province:id,name', 'regency:id,name']);

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

        return response()->json(['data' => $query->get()]);
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

    public function show(int $id): JsonResponse
    {
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
}
