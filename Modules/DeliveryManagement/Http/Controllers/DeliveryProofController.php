<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\DeliveryManagement\Models\DeliveryProof;
use Modules\DeliveryManagement\Models\DeliveryManDelivery;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryProofController extends Controller
{
    public function index($delivery_id)
    {
        $lims_delivery_data = DeliveryManDelivery::findOrFail($delivery_id);
        $lims_proof_list = DeliveryProof::where('delivery_id', $delivery_id)->get();

        return view('backend.delivery_management.delivery_proof.index', compact('lims_delivery_data', 'lims_proof_list'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'delivery_id' => 'required|exists:delivery_man_deliveries,id',
            'proof_type' => 'required|max:255',
        ]);

        $data = $request->all();

        try {
            DB::beginTransaction();
            DeliveryProof::create($data);
            DB::commit();

            return redirect()->back()->with('message', __('db.Proof uploaded successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Proof upload failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Proof upload failed: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $lims_proof_data = DeliveryProof::findOrFail($id);

        $data = [
            'id' => $lims_proof_data->id,
            'delivery_id' => $lims_proof_data->delivery_id,
            'proof_type' => $lims_proof_data->proof_type,
            'file_path' => $lims_proof_data->file_path,
            'signature_data' => $lims_proof_data->signature_data,
            'otp_code' => $lims_proof_data->otp_code,
            'is_verified' => $lims_proof_data->is_verified,
            'note' => $lims_proof_data->note,
        ];

        return $data;
    }

    public function update($id)
    {
        $lims_proof_data = DeliveryProof::findOrFail($id);
        $data = request()->all();

        try {
            DB::beginTransaction();
            $lims_proof_data->update($data);
            DB::commit();

            return redirect()->back()->with('message', __('db.Proof updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Proof update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Proof update failed: ' . $e->getMessage());
        }
    }

    public function uploadPhoto(Request $request)
    {
        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $ext = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis");
            if (!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
                $image->move(public_path('images/delivery_proof'), $imageName);
            } else {
                $imageName = 'tenant_' . $imageName . '.' . $ext;
                $image->move(public_path('images/delivery_proof'), $imageName);
            }

            return response()->json(['success' => true, 'image_name' => $imageName]);
        }

        return response()->json(['success' => false, 'message' => 'No image uploaded']);
    }

    public function captureSignature(Request $request)
    {
        $signature_data = $request->signature_data;

        if ($signature_data) {
            return response()->json(['success' => true, 'signature_data' => $signature_data]);
        }

        return response()->json(['success' => false, 'message' => 'No signature captured']);
    }

    public function verifyOtp(Request $request)
    {
        $this->validate($request, [
            'delivery_id' => 'required|exists:delivery_man_deliveries,id',
            'otp_code' => 'required',
        ]);

        $proof = DeliveryProof::where('delivery_id', $request->delivery_id)
            ->where('proof_type', 'otp')
            ->first();

        if ($proof && $proof->otp_code == $request->otp_code) {
            $proof->is_verified = true;
            $proof->verified_at = now();
            $proof->save();

            return response()->json(['success' => true, 'message' => __('db.OTP verified successfully')]);
        }

        return response()->json(['success' => false, 'message' => __('db.Invalid OTP')]);
    }

    public function geofenceCheck(Request $request)
    {
        $this->validate($request, [
            'delivery_id' => 'required|exists:delivery_man_deliveries,id',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $delivery = DeliveryManDelivery::with('fieldOrder')->findOrFail($request->delivery_id);

        if ($delivery->latitude && $delivery->longitude) {
            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $delivery->latitude,
                $delivery->longitude
            );

            if ($distance <= 0.1) {
                return response()->json(['success' => true, 'message' => 'Within geofence', 'distance' => $distance]);
            } else {
                return response()->json(['success' => false, 'message' => 'Outside geofence', 'distance' => $distance]);
            }
        }

        return response()->json(['success' => false, 'message' => 'No geofence data available']);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return $miles * 1.609344;
    }
}
