<?php

namespace Modules\DeliveryManagement\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsDeliveryActivity
{
    protected function logActivity(string $action, ?string $referenceNo = null, ?string $description = null): void
    {
        ActivityLog::create([
            'date' => now()->toDateString(),
            'user_id' => Auth::id(),
            'action' => $action,
            'reference_no' => $referenceNo,
            'item_description' => $description,
        ]);
    }
}
