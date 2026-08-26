<?php

namespace Modules\DeliveryManagement\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\DeliveryManagement\Models\DeliveryNotification;
use Modules\DeliveryManagement\Models\DeliveryMan;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\CacheForget;

class DeliveryNotificationController extends Controller
{
    use \App\Traits\CacheForget;

    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if ($role->hasPermissionTo('delivery-notifications-index')) {
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if (empty($all_permission))
                $all_permission[] = 'dummy text';

            $lims_notification_list = DeliveryNotification::with('deliveryMan')->get();
            $lims_delivery_man_list = DeliveryMan::where('is_active', true)->get();

            return view('backend.delivery_management.delivery_notification.index', compact('lims_notification_list', 'lims_delivery_man_list', 'all_permission'));
        } else {
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'delivery_man_id' => 'required|exists:delivery_men,id',
            'type' => 'required|max:255',
            'title' => 'required|max:255',
            'body' => 'required',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        try {
            DB::beginTransaction();
            DeliveryNotification::create($data);
            DB::commit();
            $this->cacheForget('delivery_notification_list');

            return redirect('delivery-notifications')->with('message', __('db.Notification created successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Notification creation failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Notification creation failed: ' . $e->getMessage());
        }
    }

    public function unreadCount()
    {
        $count = DeliveryNotification::where('is_read', false)->count();
        return response()->json(['count' => $count]);
    }

    public function markAsRead($id)
    {
        $lims_notification_data = DeliveryNotification::findOrFail($id);
        $lims_notification_data->is_read = true;
        $lims_notification_data->read_at = now();
        $lims_notification_data->save();

        return redirect()->back()->with('message', __('db.Notification marked as read'));
    }

    public function markAllAsRead()
    {
        DeliveryNotification::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect()->back()->with('message', __('db.All notifications marked as read'));
    }

    public function templates()
    {
        $templates = DeliverySetting::where('type', 'notification_template')->get();
        return view('backend.delivery_management.delivery_notification.templates', compact('templates'));
    }

    public function updateTemplates(Request $request)
    {
        $data = $request->all();

        try {
            DB::beginTransaction();
            foreach ($data as $key => $value) {
                if ($key != '_token') {
                    DeliverySetting::updateOrCreate(
                        ['key' => $key, 'type' => 'notification_template'],
                        ['value' => $value]
                    );
                }
            }
            DB::commit();
            $this->cacheForget('delivery_settings');

            return redirect('delivery-notifications/templates')->with('message', __('db.Templates updated successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Template update failed: ' . $e->getMessage());
            return redirect()->back()->with('not_permitted', 'Template update failed: ' . $e->getMessage());
        }
    }
}
