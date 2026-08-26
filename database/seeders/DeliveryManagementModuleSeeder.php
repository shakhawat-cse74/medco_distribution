<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DeliveryManagementModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('Seeding Delivery Management module data...');

        // Generate random data for deterministic test results
        $faker = \Faker\Factory::create();

        // First, ensure we have a warehouse to assign deliveries
        $warehouse = DB::table('warehouses')->first() ?: $this->createTestWarehouse();

        // Seed Delivery Men
        $deliveryMen = [];
        foreach (range(1, 5) as $i) {
            $email = 'delivery' . ($i + 1) . '@example.com';
            $phone = '1234567890' . $i;

            // Check if user already exists
            $existingUser = DB::table('users')->where('email', $email)->first();
            if ($existingUser) {
                $userId = $existingUser->id;
            } else {
                $userId = DB::table('users')->insertGetId([
                    'name' => 'Delivery Man ' . ($i + 1),
                    'email' => $email,
                    'password' => Hash::make('password123'),
                    'phone' => $phone,
                    'role_id' => 3,
                    'is_active' => 1,
                    'is_deleted' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Check if delivery man already exists
            $existingDM = DB::table('delivery_men')->where('email', $email)->first();
            if ($existingDM) {
                $deliveryMen[] = $existingDM->id;
                continue;
            }

            $deliveryMen[] = DB::table('delivery_men')->insertGetId([
                'delivery_man_id' => 'DLV-' . strtoupper(substr(md5($faker->name), 0, 8)),
                'name' => 'Delivery Man ' . ($i + 1),
                'email' => $email,
                'phone_number' => $phone,
                'password' => Hash::make('password123'),
                'address' => $faker->address,
                'city' => $faker->city,
                'country' => 'Country ' . $i,
                'nid_number' => 'NID' . str_pad($i, 9, '0', STR_PAD_LEFT),
                'license_number' => 'LIC' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'vehicle_type' => ['motorcycle', 'car', 'van', 'bicycle'][($i - 1) % 4],
                'vehicle_number' => 'VH' . strtoupper(substr(md5('vehicle' . $i), 0, 8)),
                'image' => 'https://picsum.photos/seed/delivery{$i}/200/200.jpg',
                'user_id' => $userId,
                'warehouse_id' => $warehouse->id,
                'note' => $faker->optional()->text(100),
                'is_active' => 1,
                'last_login_at' => $faker->optional()->dateTimeBetween('-30 days'),
                'fcm_token' => $faker->optional()->slug(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create delivery man vehicle for each delivery man
            $this->createDeliveryManVehicle($deliveryMen[$i - 1], $faker);

            // Create commission records
            $this->createCommission($deliveryMen[$i - 1], $faker);
        }

        // Seed Customers
        $customers = [];
        foreach (range(1, 10) as $i) {
            $customers[] = DB::table('customers')->insertGetId([
                'customer_group_id' => 1,
                'name' => 'Customer ' . $i,
                'email' => 'customer' . $i . '@example.com',
                'phone_number' => '987654321' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'address' => $faker->address,
                'city' => $faker->city,
                'country' => 'Country ' . ($i % 5 + 1),
                'state' => $faker->state,
                'postal_code' => str_pad($i * 10000, 5, '0', STR_PAD_LEFT),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Seed Field Orders
        $fieldOrders = [];
        $products = DB::table('products')
            ->inRandomOrder()
            ->limit(5)
            ->get();

        foreach (range(1, 15) as $i) {
            $refNo = 'FORD-' . date('Ymd') . '-' . str_pad($i, 6, '0', STR_PAD_LEFT);

            // Check if field order already exists
            $existingFO = DB::table('field_orders')->where('reference_no', $refNo)->first();
            if ($existingFO) {
                $fieldOrders[] = $existingFO->id;
                continue;
            }

            $subTotal = $faker->randomFloat(2, 100, 5000);
            $discountAmount = $faker->randomElement([0, 5, 10]);
            $taxAmount = $faker->randomElement([0, 8, 15]);
            $shippingCost = $faker->randomElement([0, 10, 20]);
            $grandTotal = $subTotal + $taxAmount + $shippingCost - $discountAmount;
            $paidAmount = $faker->randomFloat(2, 0, $grandTotal);
            $dueAmount = $grandTotal - $paidAmount;
            $status = $i % 4 === 0 ? 'completed' : ($i % 3 === 0 ? 'assigned' : 'pending');

            $fieldOrderId = DB::table('field_orders')->insertGetId([
                'reference_no' => $refNo,
                'customer_id' => $customers[($i - 1) % count($customers)],
                'delivery_man_id' => $deliveryMen[array_rand($deliveryMen)],
                'warehouse_id' => $warehouse->id,
                'sale_id' => 1,
                'status' => $status,
                'order_type' => 'field',
                'sub_total' => $subTotal,
                'discount_amount' => $discountAmount,
                'discount_type' => 'flat',
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'special_instructions' => $faker->optional()->text(100),
                'delivery_address' => $faker->address,
                'delivery_city' => $faker->city,
                'delivery_country' => 'Country ' . ($i % 5 + 1),
                'invoice_no' => 'INV-' . date('Ymd') . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $fieldOrders[] = $fieldOrderId;

            // Add products to field order
            $this->addProductsToFieldOrder($fieldOrderId, $faker, $i % 3 + 1);

            // Create field payment if order is paid
            if ($i % 4 === 0) {
                $this->createFieldPayment($fieldOrderId, $faker, $warehouse);
            }

            // Assign delivery if status is assigned
            if ($i % 3 === 0) {
                $this->assignDelivery($fieldOrderId, $faker);
            }
        }

        // Seed Deliveries (DeliveryManDelivery)
        $deliveries = [];
        $fieldOrderIds = array_filter($fieldOrders);

        foreach (range(1, 12) as $i) {
            if (empty($fieldOrderIds)) {
                continue;
            }

            $fieldOrderId = $fieldOrderIds[array_rand($fieldOrderIds)];
            $fieldOrder = DB::table('field_orders')->find($fieldOrderId);

            if (!$fieldOrder) {
                continue;
            }

            $refNo = 'DELV-' . date('Ymd') . '-' . str_pad($i, 6, '0', STR_PAD_LEFT);

            // Check if delivery already exists
            $existingDelivery = DB::table('delivery_man_deliveries')->where('reference_no', $refNo)->first();
            if ($existingDelivery) {
                $deliveries[] = $existingDelivery->id;
                continue;
            }

            $deliveryId = DB::table('delivery_man_deliveries')->insertGetId([
                'reference_no' => $refNo,
                'field_order_id' => $fieldOrderId,
                'delivery_man_id' => $fieldOrder->delivery_man_id,
                'customer_id' => $fieldOrder->customer_id,
                'address' => $fieldOrder->delivery_address,
                'city' => $fieldOrder->delivery_city,
                'country' => $fieldOrder->delivery_country,
                'status' => ['pending', 'assigned', 'started', 'completed', 'due'][array_rand(range(0, 4))],
                'assigned_by' => $deliveryMen[array_rand($deliveryMen)],
                'assigned_at' => now(),
                'started_at' => $faker->boolean(70) ? now() : null,
                'completed_at' => $faker->boolean(40) ? now() : null,
                'note' => $faker->optional()->text(100),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $deliveries[] = $deliveryId;

            // Create delivery proof if status is completed
            if ($faker->boolean(30)) {
                $this->createDeliveryProof($deliveryId, $faker);
            }
        }

        // Seed Cash Deposits
        foreach (range(1, 8) as $i) {
            DB::table('cash_deposits')->insert([
                'delivery_man_id' => $deliveryMen[array_rand($deliveryMen)],
                'amount' => $faker->randomFloat(2, 100, 2000),
                'deposit_method' => ['cash', 'bank', 'card'][array_rand(range(0, 2))],
                'bank_name' => $faker->optional()->company,
                'account_number' => $faker->optional()->bothify('AC-##########'),
                'reference_no' => 'CASH-' . date('Ymd') . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'status' => ['pending', 'verified', 'rejected'][array_rand(range(0, 2))],
                'note' => $faker->optional()->text(50),
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Seed Field Returns
        foreach (range(1, 5) as $i) {
            if (empty($fieldOrderIds)) {
                continue;
            }

            $fieldOrderId = $fieldOrderIds[array_rand($fieldOrderIds)];
            $fieldOrder = DB::table('field_orders')->find($fieldOrderId);

            if (!$fieldOrder) {
                continue;
            }

            $refNo = 'RET-' . date('Ymd') . '-' . str_pad($i, 6, '0', STR_PAD_LEFT);

            // Check if return already exists
            $existingReturn = DB::table('field_returns')->where('reference_no', $refNo)->first();
            if ($existingReturn) {
                $returnId = $existingReturn->id;
            } else {
                $returnId = DB::table('field_returns')->insertGetId([
                    'field_order_id' => $fieldOrderId,
                    'delivery_man_id' => $fieldOrder->delivery_man_id,
                    'customer_id' => $fieldOrder->customer_id,
                    'reference_no' => $refNo,
                    'reason' => ['defective', 'wrong_item', 'damaged', 'expired'][array_rand(range(0, 3))],
                    'status' => 'pending',
                    'refund_amount' => $faker->randomFloat(2, 50, 500),
                    'note' => $faker->optional()->text(100),
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Add return products
            $this->addProductsToFieldReturn($returnId, $faker, $i % 2 + 1);
        }

        // Seed Customer Visits
        foreach (range(1, 10) as $i) {
            DB::table('customer_visits')->insert([
                'delivery_man_id' => $deliveryMen[array_rand($deliveryMen)],
                'customer_id' => $customers[array_rand($customers)],
                'check_in_at' => $faker->dateTimeBetween('-30 days'),
                'check_out_at' => $faker->optional()->dateTimeBetween('+30 days', '+90 days'),
                'check_in_latitude' => $faker->optional()->latitude,
                'check_in_longitude' => $faker->optional()->longitude,
                'check_out_latitude' => $faker->optional()->latitude,
                'check_out_longitude' => $faker->optional()->longitude,
                'note' => $faker->optional()->text(200),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Delivery Management module data seeded successfully!');
    }

    private function createTestWarehouse()
    {
        return DB::table('warehouses')->insertGetId([
            'name' => 'Main Warehouse',
            'phone' => '1234567890',
            'email' => 'warehouse@example.com',
            'address' => '123 Main St',
            'city' => 'City',
            'country' => 'Country',
            'note' => 'Test warehouse for delivery module',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createDeliveryManVehicle($deliveryManId, $faker)
    {
        DB::table('delivery_man_vehicles')->insert([
            'delivery_man_id' => $deliveryManId,
            'vehicle_type' => ['motorcycle', 'car', 'van'][array_rand(range(0, 2))],
            'vehicle_number' => 'VH' . strtoupper(substr(md5('vehicle' . $faker->randomDigit), 0, 12)),
            'brand' => $faker->optional()->word,
            'model' => $faker->optional()->word,
            'color' => $faker->optional()->safeColorName,
            'registration_number' => $faker->optional()->bothify('??-####-??'),
            'license_number' => $faker->optional()->bothify('LIC-####'),
            'registration_expiry' => $faker->optional()->dateTimeBetween('+1 year', '+3 years'),
            'insurance_expiry' => $faker->optional()->dateTimeBetween('+1 year', '+3 years'),
            'image' => $faker->optional()->imageUrl(400, 300, 'vehicle'),
            'note' => $faker->optional()->sentence,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createCommission($deliveryManId, $faker)
    {
        DB::table('delivery_man_commissions')->insert([
            'delivery_man_id' => $deliveryManId,
            'commission_type' => 'percentage',
            'commission_rate' => $faker->randomFloat(2, 5, 20),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function addProductsToFieldOrder($fieldOrderId, $faker, $productCount)
    {
        $products = DB::table('products')->inRandomOrder()->limit(5)->get();

        foreach (range(1, $productCount) as $i) {
            $product = $products[$i % count($products)];
            $qty = rand(1, 10);
            $unitPrice = $product->price;
            $discountAmount = $faker->randomElement([0, 5, 10]);
            $taxAmount = $faker->randomElement([0, 8, 15]);
            $subTotal = ($qty * $unitPrice) - $discountAmount;

            DB::table('field_order_products')->insert([
                'field_order_id' => $fieldOrderId,
                'product_id' => $product->id,
                'product_variant_id' => null,
                'product_batch_id' => null,
                'code' => $product->code ?? null,
                'name' => $product->name,
                'unit' => $product->unit ?? 'pcs',
                'qty' => $qty,
                'sale_unit_quantity' => $qty,
                'unit_price' => $unitPrice,
                'sub_total' => $subTotal,
                'discount_amount' => $discountAmount,
                'discount_type' => 'flat',
                'tax_amount' => $taxAmount,
                'note' => $faker->optional()->text(50),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function createFieldPayment($fieldOrderId, $faker, $warehouse)
    {
        $fieldOrder = DB::table('field_orders')->find($fieldOrderId);

        $paymentMethod = ['cash', 'card', 'bank', 'cheque', 'gift_card'][array_rand(range(0, 4))];
        $paymentData = [
            'field_order_id' => $fieldOrderId,
            'payment_method' => $paymentMethod,
            'amount' => $faker->randomFloat(2, 10, 2000),
            'reference_no' => 'PAY-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 6, '0', STR_PAD_LEFT),
            'cheque_no' => $faker->optional()->bothify('??-#####'),
            'bank_name' => $faker->optional()->company,
            'note' => $faker->optional()->text(100),
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Add card-specific fields
        if ($paymentMethod === 'card') {
            $paymentData['card_type'] = $faker->optional()->creditCardType;
            $paymentData['card_last_four'] = $faker->optional()->creditCardNumber;
            $paymentData['approval_code'] = $faker->optional()->bothify('AUTH-####');
        }

        // Add cheque-specific fields
        if ($paymentMethod === 'cheque') {
            $paymentData['cheque_date'] = $faker->optional()->dateTimeBetween('-30 days', '+30 days');
        }

        // Add gift card field
        if ($paymentMethod === 'gift_card') {
            $giftCard = DB::table('gift_cards')->inRandomOrder()->first();
            if ($giftCard) {
                $paymentData['gift_card_id'] = $giftCard->id;
            }
        }

        DB::table('field_payments')->insert($paymentData);
    }

    private function assignDelivery($fieldOrderId, $faker)
    {
        $deliveryManId = DB::table('delivery_men')->inRandomOrder()->first()->id;

        DB::table('delivery_man_deliveries')->insert([
            'reference_no' => 'DELV-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 6, '0', STR_PAD_LEFT),
            'field_order_id' => $fieldOrderId,
            'delivery_man_id' => $deliveryManId,
            'customer_id' => DB::table('field_orders')->find($fieldOrderId)->customer_id,
            'address' => DB::table('field_orders')->find($fieldOrderId)->delivery_address,
            'city' => DB::table('field_orders')->find($fieldOrderId)->delivery_city,
            'country' => DB::table('field_orders')->find($fieldOrderId)->delivery_country,
            'status' => 'assigned',
            'priority' => ['normal', 'high', 'urgent'][array_rand(range(0, 2))],
            'assigned_by' => 1, // Admin user
            'assigned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createDeliveryProof($deliveryId, $faker)
    {
        DB::table('delivery_proofs')->insert([
            'delivery_id' => $deliveryId,
            'proof_type' => 'photo',
            'file_path' => $faker->imageUrl(640, 480, 'delivery', true),
            'signature_data' => $faker->imageUrl(200, 100, 'signature', true),
            'otp_code' => str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT),
            'is_verified' => $faker->boolean(80),
            'verified_at' => $faker->optional()->dateTimeBetween('-30 days'),
            'note' => $faker->optional()->text(200),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function addProductsToFieldReturn($returnId, $faker, $productCount)
    {
        $products = DB::table('products')->inRandomOrder()->limit(5)->get();

        foreach (range(1, $productCount) as $i) {
            $product = $products[$i % count($products)];
            $qty = rand(1, 5);
            $unitPrice = $product->price;
            $subTotal = $qty * $unitPrice;

            DB::table('field_return_products')->insert([
                'field_return_id' => $returnId,
                'product_id' => $product->id,
                'product_variant_id' => null,
                'product_batch_id' => null,
                'code' => $product->code ?? null,
                'name' => $product->name,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'sub_total' => $subTotal,
                'note' => $faker->optional()->text(50),
                'photo' => $faker->optional()->imageUrl(400, 300, 'return'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}