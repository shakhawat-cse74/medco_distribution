<?php

namespace App\Services;

use App\Models\InvoiceSchema;
use App\Models\InvoiceSetting;

class InvoiceService
{
    public function generateInvoiceName(string $default)
    {
        $invoice_settings = InvoiceSetting::active_setting();
        $invoice_schema = InvoiceSchema::latest()->first();
        $show_active_status =  json_decode($invoice_settings->show_column);
        $prefix = $invoice_settings->prefix ?? $default;
        if (isset($show_active_status) && $show_active_status->active_generat_settings == 1) {
            if ($invoice_settings->numbering_type == "sequential") {
                if ($invoice_schema == null) {
                    InvoiceSchema::query()->create(['last_invoice_number' => $invoice_settings->start_number]);
                    return $prefix . '-' . $invoice_settings->start_number;
                } else {
                    $invoice_schema->update(['last_invoice_number' => $invoice_schema->last_invoice_number + 1]);
                    return $prefix . '-' . $invoice_schema->last_invoice_number;
                }
            } elseif ($invoice_settings->numbering_type == "random") {
                return $prefix . '-' . rand($invoice_settings->start_number, str_repeat('9', (int)$invoice_settings->number_of_digit));
            } else {
                return  $prefix . date("Ymd") . '-' . date("his");
            }
        } else {
            return $prefix . date("Ymd") . '-' . date("his");
        }
    }
}
