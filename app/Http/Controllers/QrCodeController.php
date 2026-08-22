<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QrCode;
use App\Models\Warehouse;
use App\Models\Table;
use App\Models\GeneralSetting;
use App\Models\QrCatelogSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode as SimpleQrCode;

class QrCodeController extends Controller
{
    /**
     * Generate QR code for a warehouse or table
     */
    public function index()
    {
        $general_setting = cache()->get('general_setting');

        $qr_catelog_setting = QrCatelogSetting::latest()->first();
        if (!$qr_catelog_setting) {
            $qr_catelog_setting = new QrCatelogSetting();
            $qr_catelog_setting->show_stock_out_product = 1;
        }
      
        if(in_array('restaurant',explode(',',$general_setting->modules)))
        {
            $tables = Table::where('is_active', true)->get();
            return view('backend.qr-menu.index', compact('tables', 'general_setting', 'qr_catelog_setting'));
        }
        else
        {
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            return view('backend.qr-menu.index', compact('lims_warehouse_list', 'general_setting','qr_catelog_setting'));
        }
    }

    public function generate(Request $request, $type, $id)
    {
        $model = null;
        $url = '';
        $warehouse_name = '';

        $color = $request->input('color', '#000000');
        $showLogo = $request->input('show_logo', true);

        if ($type === 'warehouse') {
            $model = Warehouse::findOrFail($id);
            $warehouse_name = $model->name;
            $slug = Str::slug($warehouse_name);
            $url = url("/menu/{$slug}");
        } elseif ($type === 'table') {
            $model = Table::findOrFail($id);
            $floor = DB::table('floors')->where('id', $model->floor_id)->first();
            if (!$floor) {
                return response()->json(['success' => false, 'message' => 'Table floor not found'], 404);
            }
            $warehouse = Warehouse::find($floor->warehouse_id);
            if (!$warehouse) {
                return response()->json(['success' => false, 'message' => 'Warehouse not found'], 404);
            }
            $warehouse_name = $warehouse->name;
            $slug = Str::slug($warehouse_name);
            $url = url("/menu/{$slug}?table_id={$id}");
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid type'], 400);
        }

        $general_setting = cache()->get('general_setting');

        // Check if existing QR exists
        $qr = QrCode::where('qrable_id', $model->id)
                    ->where('qrable_type', get_class($model))
                    ->first();

        if (!$qr) {
            $qr = new QrCode();
            $qr->qrable_id = $model->id;
            $qr->qrable_type = get_class($model);
            $qr->code = (string) Str::uuid();
        }

        $qr->url = $url;
        $qr->is_active = true;
        $qr->save();

        $redirectUrl = url('/q/' . $qr->code);

        // Ensure directory exists
        $directory = public_path('images/qrcodes');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Check if Imagick is available
        $hasImagick = extension_loaded('imagick') && class_exists('Imagick');

        $qrGenerator = null;
        $fileExtension = null;

        // If Imagick exists → PNG with logo
        if ($hasImagick) {
            $qrGenerator = SimpleQrCode::format('png')
                            ->size(400)
                            ->errorCorrection('H')
                            ->color(...sscanf($color, "#%02x%02x%02x"));

            $fileExtension = 'png';

            // Add logo if exists
            if ($showLogo && $general_setting && $general_setting->site_logo) {
                $logoPath = public_path('logo/' . $general_setting->site_logo);

                if (file_exists($logoPath)) {
                    $qrGenerator = $qrGenerator->merge($logoPath, 0.2, true);
                }
            }

        } else {
            // Fallback → SVG (no logo)
            $qrGenerator = SimpleQrCode::format('svg')
                ->size(400)
                ->errorCorrection('H');

            $fileExtension = 'svg';
        }

        // File naming
        $fileName = 'qr_' . $type . '_' . $id . '.' . $fileExtension;
        $fullPath = $directory . '/' . $fileName;
        $relativePath = 'images/qrcodes/' . $fileName;

        $qrImage = $qrGenerator->generate($redirectUrl);

        file_put_contents($fullPath, $qrImage);

        $this->saveQrImage($qrImage, $fullPath);

        $qr->path = $relativePath;
        $qr->save();

        if ($type === 'warehouse') {
            DB::table('warehouses')->where('id', $model->id)->update(['qr_code_id' => $qr->id]);
        } elseif ($type === 'table') {
            DB::table('tables')->where('id', $model->id)->update(['qr_code_id' => $qr->id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'QR Code successfully generated',
            'qr_url'  => !config('database.connections.saleprosaas_landlord') ? asset($qr->path) : asset('../../' . $qr->path),
            'url'     => $qr->url,
            'code'    => $qr->code
        ]);
    }

    /**
     * Isolated method to handle storage mechanism
     */
    protected function saveQrImage($content, $fullPath)
    {
        file_put_contents($fullPath, $content);
        // For S3 swap: Storage::disk('s3')->put($relativePath, $content);
    }

    /**
     * View QR code details
     */
    public function show($id)
    {
        $qr = QrCode::findOrFail($id);
        return response()->json([
            'success'   => true,
            'image_url' => asset($qr->path),
            'url'       => $qr->url,
            'code'      => $qr->code,
            'redirect'  => url('/q/' . $qr->code)
        ]);
    }

    /**
     * Download QR code
     */
    public function download($id)
    {
        $qr = QrCode::findOrFail($id);
        $filePath = public_path($qr->path);

        if (!file_exists($filePath)) {
            abort(404, 'QR Code image not found.');
        }

        return response()->download($filePath);
    }

    /**
     * Redirect route /q/{code}
     */
    public function redirect($code)
    {
        $qr = QrCode::where('code', $code)->where('is_active', true)->firstOrFail();
        return redirect()->away($qr->url);
    }

    public function saveSettings(Request $request)
    {
        $qr_catelog_setting = QrCatelogSetting::latest()->first();
        if (!$qr_catelog_setting) {
            $qr_catelog_setting = new QrCatelogSetting();
        }
        
        $qr_catelog_setting->show_stock_out_product = $request->show_stock_out_product;
        $qr_catelog_setting->save();

        return response()->json(['success' => true, 'message' => 'Settings updated successfully']);
    }
}
