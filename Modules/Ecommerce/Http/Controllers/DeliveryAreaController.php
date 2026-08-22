<?php

namespace Modules\Ecommerce\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Ecommerce\Entities\DeliveryArea;

class DeliveryAreaController extends Controller
{
    // ── Index ────────────────────────────────────────────────────────────────
    public function index()
    {
        $areas = DeliveryArea::orderBy('city')->orderBy('name')->get();
        return view('ecommerce::backend.delivery-area.index', compact('areas'));
    }

    // ── Store (single or bulk) ───────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'city'            => 'nullable|string|max:255',
            'zone'            => 'nullable|string|max:255',
            'delivery_charge' => 'required|numeric|min:0',
            'estimated_days'  => 'required|integer|min:1',
            'is_active'       => 'nullable|boolean',
            'note'            => 'nullable|string',
        ]);

        DeliveryArea::create([
            'name'            => $request->name,
            'city'            => $request->city,
            'zone'            => $request->zone,
            'delivery_charge' => $request->delivery_charge,
            'estimated_days'  => $request->estimated_days,
            'is_active'       => $request->has('is_active') ? 1 : 0,
            'note'            => $request->note,
        ]);

        return response()->json(['success' => true, 'message' => 'Delivery area created successfully.']);
    }

    // ── Edit (fetch single for modal) ────────────────────────────────────────
    public function edit($id)
    {
        $area = DeliveryArea::findOrFail($id);
        return response()->json($area);
    }

    // ── Update single ────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'city'            => 'nullable|string|max:255',
            'zone'            => 'nullable|string|max:255',
            'delivery_charge' => 'required|numeric|min:0',
            'estimated_days'  => 'required|integer|min:1',
            'is_active'       => 'nullable|boolean',
            'note'            => 'nullable|string',
        ]);

        $area = DeliveryArea::findOrFail($id);
        $area->update([
            'name'            => $request->name,
            'city'            => $request->city,
            'zone'            => $request->zone,
            'delivery_charge' => $request->delivery_charge,
            'estimated_days'  => $request->estimated_days,
            'is_active'       => $request->has('is_active') ? 1 : 0,
            'note'            => $request->note,
        ]);

        return response()->json(['success' => true, 'message' => 'Delivery area updated successfully.']);
    }

    // ── Bulk Update ──────────────────────────────────────────────────────────
    // Handles both existing rows (with id) and new rows (id = null / new_*)
    public function bulkUpdate(Request $request)
    {
        $rows = $request->input('rows', []);

        foreach ($rows as $row) {
            if (!empty($row['id']) && is_numeric($row['id'])) {
                // Update existing
                $area = DeliveryArea::find($row['id']);
                if ($area) {
                    $area->update([
                        'name'            => $row['name']            ?? $area->name,
                        'city'            => $row['city']            ?? null,
                        'zone'            => $row['zone']            ?? null,
                        'delivery_charge' => $row['delivery_charge'] ?? 0,
                        'estimated_days'  => $row['estimated_days']  ?? 1,
                        'is_active'       => isset($row['is_active']) ? (int)$row['is_active'] : 0,
                        'note'            => $row['note']            ?? null,
                    ]);
                }
            } else {
                // New row
                if (!empty($row['name'])) {
                    DeliveryArea::create([
                        'name'            => $row['name'],
                        'city'            => $row['city']            ?? null,
                        'zone'            => $row['zone']            ?? null,
                        'delivery_charge' => $row['delivery_charge'] ?? 0,
                        'estimated_days'  => $row['estimated_days']  ?? 1,
                        'is_active'       => isset($row['is_active']) ? (int)$row['is_active'] : 1,
                        'note'            => $row['note']            ?? null,
                    ]);
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'All changes saved successfully.']);
    }

    // ── Toggle Status ────────────────────────────────────────────────────────
    public function toggleStatus($id)
    {
        $area = DeliveryArea::findOrFail($id);
        $area->update(['is_active' => !$area->is_active]);
        return response()->json([
            'success'   => true,
            'is_active' => $area->is_active,
            'message'   => 'Status updated.',
        ]);
    }

    // ── Destroy ──────────────────────────────────────────────────────────────
    public function destroy($id)
    {
        DeliveryArea::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Delivery area deleted.']);
    }

    // ── API: active areas for checkout ──────────────────────────────────────
    public function activeAreas()
    {
        $areas = DeliveryArea::active()->orderBy('city')->orderBy('name')
            ->select('id', 'name', 'city', 'zone', 'delivery_charge', 'estimated_days')
            ->get();
        return response()->json($areas);
    }
}
