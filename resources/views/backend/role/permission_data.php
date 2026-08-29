<?php

return [
    "sidebar_permissions" =>  [
        "sidebar_whatsapp" => "whatsapp",
    ],
    "manufacturing_module" => [
        "production-view" => "Production List",
        "production-add" => "Add Production",
        "production-edit" => "Edit Production",
        "production-delete" => "Delete Production",
        "recipe-view" => "Recipe List",
        "recipe-add" => "Add Recipe",
        "recipe-edit" => "Edit Recipe",
        "recipe-delete" => "Delete Recipe",
    ],
    "project_module" => [
        "project_project_list" => "Project List",
        "project_project_add" => "Project Add",
        "project_project_show" => "Project Show",
        "project_project_edit" => "Project Edit",
        "project_project_delete" => "Project Delete",
        "project_task_list" => "Task List",
        "project_task_add" => "Task Add",
        "project_task_show" => "Task Show",
        "project_task_edit" => "Task Edit",
        "project_task_delete" => "Task Delete",
        "project_category_list" => "Project Category List",
        "project_category_add" => "Project Category Add",
        "project_category_edit" => "Project Category Edit",
        "project_category_delete" => "Project Category Delete",
    ],
    "repair_module" => [
        "repair-dashboard" => "Repair Dashboard",
        "repair-service-index" => "service_jobs_list",
        "repair-service-view" => "view_service_jobs",
        "repair-service-add" => "add_service_job",
        "repair-service-edit" => "edit_service_job",
        "repair-service-delete" => "delete_service_job",
        "repair-parts-view" => "View Parts and Billing",
        "repair-parts-add" => "Add Part",
        "repair-parts-edit" => "Edit Parts Qty bi Price",
        "repair-parts-delete" => "Remove Parts",
        "repair-charges-edit" => "Edit Service Charges",
        "repair-payment-add" => "Collect Payment",
        "repair-payment-delete" => "Delete Payment",
        "repair-device-type" => "Device Type",
    ],


    /*
    |--------------------------------------------------------------------------
    | Delivery Management Module
    |--------------------------------------------------------------------------
    |
    | Permissions related to delivery operations, delivery men,
    | field orders, payments, routes, vehicles, deliveries,
    | commissions, deposits, returns, visits, schedules,
    | notifications, settings, and reports.
    |
    */
    "delivery_management_module" => [

    /*
    |--------------------------------------------------------------------------
    | Delivery Man Management
    |--------------------------------------------------------------------------
    |
    | Create, view, update, delete and manage delivery men.
    |
    */
        "delivery-men-index"  => "Delivery Men List",
        "delivery-men-add"    => "Add Delivery Man",
        "delivery-men-view"   => "View Delivery Man",
        "delivery-men-edit"   => "Edit Delivery Man",
        "delivery-men-delete" => "Delete Delivery Man",

    /*
    |--------------------------------------------------------------------------
    | Delivery Man Assignments
    |--------------------------------------------------------------------------
    |
    | Manage assignments of delivery men to their respective tasks/orders.
    |
    */
        "delivery-man-assignments-index" => "Delivery Man Assignments",
        "delivery-man-assignments-add"   => "Assign Delivery Man",
        "delivery-man-assignments-edit"  => "Edit Assignment",
        "delivery-man-assignments-delete" => "Delete Assignment",

    /*
    |--------------------------------------------------------------------------
    | Routes and Vehicles
    |--------------------------------------------------------------------------
    |
    | Manage delivery routes and vehicles used by delivery men.
    |
    */
        "delivery-man-routes-index"   => "Delivery Routes",
        "delivery-man-routes-add"     => "Add Route",
        "delivery-man-routes-edit"    => "Edit Route",
        "delivery-man-routes-delete"  => "Delete Route",
        "delivery-man-vehicles-index" => "Vehicle Management",
        "delivery-man-vehicles-add"   => "Add Vehicle",
        "delivery-man-vehicles-edit"  => "Edit Vehicle",
        "delivery-man-vehicles-delete" => "Delete Vehicle",

    /*
    |--------------------------------------------------------------------------
    | Field Order Management
    |--------------------------------------------------------------------------
    |
    | Manage field orders from creation through cancellation.
    |
    */
        "field-orders-index"  => "Field Orders",
        "field-orders-add"    => "Create Field Order",
        "field-orders-view"   => "View Field Order",
        "field-orders-edit"   => "Edit Field Order",
        "field-orders-delete" => "Cancel Field Order",

    /*
    |--------------------------------------------------------------------------
    | Field Payment Management
    |--------------------------------------------------------------------------
    |
    | Manage payments collected against field orders.
    |
    */
        "field-payments-index"  => "Field Payments",
        "field-payments-add"    => "Collect Payment",
        "field-payments-view"   => "View Payment",
        "field-payments-edit"   => "Edit Payment",
        "field-payments-delete" => "Delete Payment",

    /*
    |--------------------------------------------------------------------------
    | Delivery Management
    |--------------------------------------------------------------------------
    |
    | Manage delivery assignments and update delivery status/details.
    |
    */
        "delivery-man-delivery-index"  => "Delivery Management",
        "delivery-man-delivery-assign" => "Assign Deliveries",
        "delivery-man-delivery-update" => "Update Delivery",

    /*
    |--------------------------------------------------------------------------
    | Delivery Proofs
    |--------------------------------------------------------------------------
    |
    | Manage proof of delivery captured during the delivery process.
    |
    */
        "delivery-proofs-index" => "Delivery Proofs",
        "delivery-proofs-add"   => "Capture Proof",
        "delivery-proofs-edit"  => "Edit Proof",
        "delivery-proofs-delete" => "Delete Proof",

    /*
    |--------------------------------------------------------------------------
    | Commission Management
    |--------------------------------------------------------------------------
    |
    | View and calculate commissions for delivery men.
    |
    */
        "delivery-man-commissions-index" => "Commission Management",
        "delivery-man-commissions-add"   => "Calculate Commission",
        "delivery-man-commissions-edit"  => "Edit Commission",
        "delivery-man-commissions-delete" => "Delete Commission",

    /*
    |--------------------------------------------------------------------------
    | Cash Deposit Management
    |--------------------------------------------------------------------------
    |
    | Manage cash deposits submitted by delivery men.
    |
    */
        "cash-deposits-index" => "Cash Deposits",
        "cash-deposits-add"   => "Record Deposit",
        "cash-deposits-edit"  => "Edit Deposit",
        "cash-deposits-delete" => "Delete Deposit",

    /*
    |--------------------------------------------------------------------------
    | Field Return Management
    |--------------------------------------------------------------------------
    |
    | Manage product/order returns initiated from the field.
    |
    */
        "field-returns-index" => "Field Returns",
        "field-returns-add"   => "Initiate Return",
        "field-returns-edit"  => "Edit Return",
        "field-returns-delete" => "Delete Return",

    /*
    |--------------------------------------------------------------------------
    | Customer Visit Management
    |--------------------------------------------------------------------------
    |
    | Manage customer visits and customer check-in activities.
    |
    */
        "customer-visits-index" => "Customer Visits",
        "customer-visits-add"   => "Check Customer In",
        "customer-visits-edit"  => "Edit Visit",
        "customer-visits-delete" => "Delete Visit",

    /*
    |--------------------------------------------------------------------------
    | Delivery Schedules
    |--------------------------------------------------------------------------
    |
    | Manage delivery schedules and planned delivery activities.
    |
    */
        "delivery-man-schedules-index" => "Delivery Schedules",
        "delivery-man-schedules-add"   => "Add Schedule",
        "delivery-man-schedules-edit"  => "Edit Schedule",
        "delivery-man-schedules-delete" => "Delete Schedule",

    /*
    |--------------------------------------------------------------------------
    | Delivery Settings
    |--------------------------------------------------------------------------
    |
    | Manage configuration/settings related to the delivery module.
    |
    */
        "delivery-settings-index"  => "Delivery Settings",
        "delivery-settings-update" => "Update Delivery Settings",

    /*
    |--------------------------------------------------------------------------
    | Delivery Notifications
    |--------------------------------------------------------------------------
    |
    | Manage delivery-related notifications.
    |
    */
        "delivery-notifications-index" => "Delivery Notifications",
        "delivery-notifications-edit"  => "Edit Notification",
        "delivery-notifications-delete" => "Delete Notification",

    /*
    |--------------------------------------------------------------------------
    | Delivery Reports
    |--------------------------------------------------------------------------
    |
    | Access delivery-related reports and analytics.
    |
    */
        "delivery-reports-index" => "Delivery Dashboard",

    /*
    |--------------------------------------------------------------------------
    | Warehouse Products
    |--------------------------------------------------------------------------
    |
    | Manage product stock and pricing across warehouses.
    |
    */
        "warehouse-products-index"  => "Warehouse Products",
        "warehouse-products-add"    => "Add Warehouse Product",
        "warehouse-products-edit"   => "Edit Warehouse Product",
        "warehouse-products-delete" => "Delete Warehouse Product",

    /*
    |--------------------------------------------------------------------------
    | Warehouse
    |--------------------------------------------------------------------------
    |
    | Manage warehouses master data.
    |
    */
        "warehouse-index"  => "Warehouse List",
        "warehouse-add"    => "Add Warehouse",
        "warehouse-edit"   => "Edit Warehouse",
        "warehouse-delete" => "Delete Warehouse",

    /*
    |--------------------------------------------------------------------------
    | Delivery Man Routes
    |--------------------------------------------------------------------------
    |
    | Manage delivery routes assigned to delivery men.
    |
    */
        "delivery-man-routes-index"  => "Delivery Routes",
        "delivery-man-routes-add"    => "Add Route",
        "delivery-man-routes-edit"   => "Edit Route",
        "delivery-man-routes-delete" => "Delete Route",
    ],
];
