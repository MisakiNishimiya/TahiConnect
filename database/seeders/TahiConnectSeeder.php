<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Shop;
use App\Models\ShopReview;
use App\Models\Measurement;
use App\Models\Appointment;
use App\Models\GarmentType;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\CustomNotification;
use App\Models\VirtualTryon;
use App\Models\AvailableTimeSlot;
use App\Models\Fabric;
use App\Models\ActivityLog;
use App\Models\PreMadeProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TahiConnectSeeder extends Seeder
{
    public function run(): void
    {
        // ── Single Shop Instance ──────────────────────────────────────────────
        // In the single-shop system there is exactly one shop record.
        // All operational data belongs to this shop.
        $shop = Shop::firstOrCreate(
            ['slug' => 'tahiconnect-tailoring'],
            [
                'name'            => config('app.name', 'TahiConnect') . ' Tailoring',
                'description'     => 'A professional tailoring business providing quality custom garments for every occasion.',
                'address'         => '123 Main Street',
                'barangay'        => 'Bajada',
                'city'            => 'Davao City',
                'province'        => 'Davao del Sur',
                'contact_number'  => '+63 917 123 4567',
                'email'           => 'shop@example.com',
                'specialties'     => ['Barong Tagalog', 'Filipiniana', 'Gowns', "Men's Suits"],
                'is_verified'     => true,
                'is_active'       => true,
                'operating_hours' => [
                    'Monday'    => '09:00-17:00',
                    'Tuesday'   => '09:00-17:00',
                    'Wednesday' => '09:00-17:00',
                    'Thursday'  => '09:00-17:00',
                    'Friday'    => '09:00-17:00',
                    'Saturday'  => '10:00-15:00',
                    'Sunday'    => 'Closed',
                ],
            ]
        );

        // ── Super Admin ───────────────────────────────────────────────────────────
        // Super Admin = TahiConnect platform owner (not the tailoring business owner)
        $admin = User::firstOrCreate(
            ['email' => 'superadmin@tahiconnect.com'],
            [
                'name'           => 'TahiConnect Admin',
                'first_name'     => 'TahiConnect',
                'last_name'      => 'Admin',
                'password'       => Hash::make('password'),
                'contact_number' => '+63 917 000 0001',
                'role'           => 'super_admin',
            ]
        );

        // ── Shop Owner ────────────────────────────────────────────────────────
        $owner = User::firstOrCreate(
            ['email' => 'owner@shop.com'],
            [
                'name'           => 'Rosa Alcantara',
                'first_name'     => 'Rosa',
                'last_name'      => 'Alcantara',
                'password'       => Hash::make('password'),
                'contact_number' => '+63 918 111 2222',
                'role'           => 'shop_owner',
                'shop_id'        => $shop->id,
            ]
        );
        // Link owner to shop
        $shop->update(['owner_id' => $owner->id]);

        // ── Tailor Staff ──────────────────────────────────────────────────────
        $staffData = [
            ['Elena',   'Reyes',   'elena@shop.com'],
            ['Carlos',  'Mendoza', 'carlos@shop.com'],
            ['Patricia','Garcia',  'patricia@shop.com'],
        ];
        $staff = [];
        foreach ($staffData as $s) {
            $staff[] = User::firstOrCreate(
                ['email' => $s[2]],
                [
                    'name'           => "$s[0] $s[1]",
                    'first_name'     => $s[0],
                    'last_name'      => $s[1],
                    'password'       => Hash::make('password'),
                    'contact_number' => '+63 919 ' . rand(100, 999) . ' ' . rand(1000, 9999),
                    'role'           => 'tailor_staff',
                    'shop_id'        => $shop->id,
                ]
            );
        }

        // ── Customers ─────────────────────────────────────────────────────────
        $customerData = [
            ['Ana',      'Dela Cruz',   'ana@email.com'],
            ['Juan',     'Bautista',    'juan@email.com'],
            ['Sofia',    'Ramirez',     'sofia@email.com'],
            ['Marco',    'Villanueva',  'marco@email.com'],
            ['Isabella', 'Fernandez',   'isabella@email.com'],
            ['Gabriel',  'Aquino',      'gabriel@email.com'],
            ['Carmen',   'Lim',         'carmen@email.com'],
            ['Rafael',   'Navarro',     'rafael@email.com'],
            ['Lucia',    'Pangilinan',  'lucia@email.com'],
            ['Diego',    'Soriano',     'diego@email.com'],
        ];
        $customers = [];
        foreach ($customerData as $c) {
            $customers[] = User::firstOrCreate(
                ['email' => $c[2]],
                [
                    'name'           => "$c[0] $c[1]",
                    'first_name'     => $c[0],
                    'last_name'      => $c[1],
                    'password'       => Hash::make('password'),
                    'contact_number' => '+63 923 ' . rand(100, 999) . ' ' . rand(1000, 9999),
                    'role'           => 'customer',
                ]
            );
        }

        // ── Garment Types ─────────────────────────────────────────────────────
        $garmentData = [
            ['Barong Tagalog',       'Traditional Filipino formal wear made from lightweight fabric.',  3500],
            ['Filipiniana Dress',    'Elegant butterfly-sleeved terno dress for formal occasions.',     5000],
            ['Terno',                'Modern interpretation of the classic Filipino terno.',            6500],
            ["Baro't Saya",          'Traditional Filipino blouse and skirt ensemble.',                 4000],
            ["Men's Suit",           'Custom-tailored two-piece or three-piece suit.',                  8500],
            ["Ladies' Blouse",       'Bespoke ladies blouse with custom fit.',                          1800],
            ['Formal Gown',          'Floor-length formal gown for special occasions.',                12000],
            ['School Uniform',       'Standard school uniform with precise measurements.',              1200],
            ['Corporate Uniform',    'Professional corporate attire set.',                              2500],
            ['Casual Wear',          'Custom casual clothing tailored to your style.',                  1500],
        ];
        $garments = [];
        foreach ($garmentData as $g) {
            $garments[] = GarmentType::firstOrCreate(
                ['shop_id' => $shop->id, 'name' => $g[0]],
                ['description' => $g[1], 'base_price' => $g[2]]
            );
        }

        // ── Fabrics ───────────────────────────────────────────────────────────
        $fabricData = [
            ['Jusi',        'Banana silk',   'Ivory',         450,  true],
            ['Piña Cloth',  'Pineapple fiber','Natural White', 1200, true],
            ['Organza',     'Sheer woven',   'Champagne',      350,  true],
            ['Silk',        'Natural silk',  'Pearl White',    800,  true],
            ['Cotton Twill','Cotton weave',  'Navy Blue',      250,  true],
            ['Linen',       'Natural linen', 'Beige',          380,  true],
            ['Chiffon',     'Sheer chiffon', 'Blush Pink',     300,  true],
            ['Satin',       'Smooth satin',  'Midnight Black', 420,  false],
        ];
        $fabrics = [];
        foreach ($fabricData as $f) {
            $fabrics[] = Fabric::firstOrCreate(
                ['shop_id' => $shop->id, 'name' => $f[0]],
                ['material' => $f[1], 'color' => $f[2], 'price_per_meter' => $f[3], 'in_stock' => $f[4]]
            );
        }

        // ── Pre-made Products ─────────────────────────────────────────────────
        $productData = [
            ['Standard White Barong (RTW)',   'Classic semi-formal white barong, ready to wear.',        2500, ['XS','S','M','L','XL']],
            ['Pre-made Formal Slacks',         'Straight-cut dark slacks for corporate or formal use.',   1200, ['28','30','32','34','36']],
            ['Casual Linen Shirt',             'Relaxed-fit linen shirt ideal for tropical weather.',      950, ['S','M','L','XL']],
            ['Filipiniana Shawl / Alampay',    'Traditional shawl to complement Filipiniana dresses.',    1800, ['Free Size']],
        ];
        $products = [];
        foreach ($productData as $p) {
            $products[] = PreMadeProduct::firstOrCreate(
                ['shop_id' => $shop->id, 'name' => $p[0]],
                ['description' => $p[1], 'price' => $p[2], 'available_sizes' => $p[3], 'is_active' => true]
            );
        }

        // ── Measurements ─────────────────────────────────────────────────────
        $measurementData = [
            [96.5, 81.3, 96.5, 44.5, 61.0, 78.7, 38.1, 170.2],
            [101.6,86.4,101.6, 46.0, 63.5, 81.3, 40.6, 175.3],
            [86.4, 68.6, 91.4, 38.1, 55.9, 76.2, 33.0, 162.6],
            [106.7,91.4,104.1, 48.3, 66.0, 83.8, 41.9, 180.3],
        ];
        $validationStatuses = ['validated', 'pending', 'validated', 'rejected'];
        for ($i = 0; $i < min(8, count($customers)); $i++) {
            $m = $measurementData[$i % 4];
            Measurement::firstOrCreate(
                ['user_id' => $customers[$i]->id],
                [
                    'chest'             => $m[0], 'waist'         => $m[1],
                    'hip'               => $m[2], 'shoulder'      => $m[3],
                    'sleeve_length'     => $m[4], 'inseam'        => $m[5],
                    'neck'              => $m[6], 'height'        => $m[7],
                    'validation_status' => $validationStatuses[$i % 4],
                ]
            );
        }

        // ── Orders ────────────────────────────────────────────────────────────
        $allStatuses     = ['pending','measurements_verified','in_production','fitting_scheduled','final_adjustment','ready_for_pickup','completed','released'];
        $preMadeStatuses = ['pending','in_production','ready_for_pickup','completed','released'];

        $orders = [];
        for ($i = 0; $i < 20; $i++) {
            $customer    = $customers[$i % count($customers)];
            $staffMember = $staff[$i % count($staff)];
            $isPreMade   = ($i % 4 === 0);
            $qty         = rand(1, 3);

            // Skip if tracking number already exists
            $trackingNumber = 'TC-2025-' . str_pad($i + 1001, 4, '0', STR_PAD_LEFT);
            if (Order::where('tracking_number', $trackingNumber)->exists()) {
                continue;
            }

            if ($isPreMade) {
                $product = $products[$i % count($products)];
                $status  = $preMadeStatuses[$i % count($preMadeStatuses)];
                $size    = collect($product->available_sizes)->first();
                $orders[] = Order::create([
                    'shop_id'            => $shop->id,
                    'user_id'            => $customer->id,
                    'staff_id'           => $staffMember->id,
                    'tracking_number'    => $trackingNumber,
                    'order_type'         => 'pre_made',
                    'pre_made_product_id'=> $product->id,
                    'product_size'       => $size,
                    'garment_type_id'    => null,
                    'fabric_preference'  => null,
                    'quantity'           => $qty,
                    'status'             => $status,
                    'estimated_completion' => Carbon::now()->addDays(rand(2, 7)),
                    'total_amount'       => $product->price * $qty,
                ]);
            } else {
                $garment = $garments[$i % count($garments)];
                $status  = $allStatuses[$i % count($allStatuses)];
                $orders[] = Order::create([
                    'shop_id'            => $shop->id,
                    'user_id'            => $customer->id,
                    'staff_id'           => $staffMember->id,
                    'tracking_number'    => $trackingNumber,
                    'order_type'         => 'custom',
                    'garment_type_id'    => $garment->id,
                    'fabric_preference'  => 'Silk',
                    'quantity'           => $qty,
                    'status'             => $status,
                    'estimated_completion' => Carbon::now()->addDays(rand(7, 45)),
                    'total_amount'       => $garment->base_price * $qty,
                ]);
            }
        }

        // ── Order Status History ──────────────────────────────────────────────
        foreach ($orders as $order) {
            $flow       = $order->order_type === 'pre_made' ? $preMadeStatuses : $allStatuses;
            $statusIdx  = array_search($order->status, $flow);
            if ($statusIdx === false) continue;
            for ($s = 0; $s <= $statusIdx; $s++) {
                OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'status'     => $flow[$s],
                    'changed_by' => $order->staff_id,
                    'created_at' => Carbon::now()->subDays(($statusIdx - $s) * 2)->addHours(rand(1, 6)),
                ]);
            }
        }

        // ── Appointments ─────────────────────────────────────────────────────
        $appointmentTypes = ['initial_measurement', 'fabric_selection', 'baste_fitting', 'final_pickup'];
        for ($i = 0; $i < 15; $i++) {
            $isPast      = $i < 5;
            $staffMember = $staff[$i % count($staff)];
            Appointment::create([
                'shop_id'  => $shop->id,
                'user_id'  => $customers[$i % count($customers)]->id,
                'staff_id' => $staffMember->id,
                'date'     => $isPast
                    ? Carbon::now()->subDays(rand(1, 30))
                    : Carbon::now()->addDays(rand(1, 21)),
                'time'     => sprintf('%02d:00:00', rand(9, 16)),
                'type'     => $appointmentTypes[$i % 4],
                'status'   => $isPast
                    ? ($i % 2 === 0 ? 'completed' : 'cancelled')
                    : ($i % 2 === 0 ? 'pending'   : 'confirmed'),
            ]);
        }

        // ── Payments ─────────────────────────────────────────────────────────
        $paymentMethods = ['cash', 'gcash', 'bank_transfer', 'card'];
        foreach ($orders as $i => $order) {
            $isPaid = in_array($order->status, ['completed', 'released', 'ready_for_pickup']);
            Payment::create([
                'order_id'         => $order->id,
                'user_id'          => $order->user_id,
                'amount'           => $isPaid ? $order->total_amount : $order->total_amount * 0.5,
                'payment_method'   => $paymentMethods[$i % 4],
                'status'           => $isPaid ? 'paid' : 'pending',
                'payment_date'     => $isPaid ? Carbon::now()->subDays(rand(1, 20)) : null,
                'reference_number' => $isPaid ? 'REF-' . strtoupper(substr(md5($i), 0, 8)) : null,
            ]);
        }

        // ── Notifications ─────────────────────────────────────────────────────
        $notifMessages = [
            'Your order has been received and is now pending.',
            'Measurements have been verified. Production starting soon.',
            'Your garment is now in production.',
            'Your fitting session has been scheduled.',
            'Your order is ready for pickup!',
            'Thank you for your order. Please leave a review.',
        ];
        $notifTypes = ['order', 'order', 'order', 'appointment', 'order', 'system'];
        for ($i = 0; $i < 20; $i++) {
            CustomNotification::create([
                'user_id'    => $customers[$i % count($customers)]->id,
                'type'       => $notifTypes[$i % count($notifTypes)],
                'title'      => 'Order Update',
                'message'    => $notifMessages[$i % count($notifMessages)],
                'is_read'    => $i > 10,
                'created_at' => Carbon::now()->subHours(rand(1, 200)),
            ]);
        }

        // ── Available Time Slots ──────────────────────────────────────────────
        for ($day = 0; $day < 14; $day++) {
            $date = Carbon::now()->addDays($day);
            if ($date->isWeekend()) continue;
            $slots = [
                ['09:00', '10:00'],
                ['10:00', '11:00'],
                ['11:00', '12:00'],
                ['13:00', '14:00'],
                ['14:00', '15:00'],
                ['15:00', '16:00'],
            ];
            foreach ($slots as $slot) {
                AvailableTimeSlot::firstOrCreate(
                    ['shop_id' => $shop->id, 'date' => $date->toDateString(), 'start_time' => $slot[0]],
                    [
                        'end_time'         => $slot[1],
                        'is_available'     => true,
                        'max_bookings'     => 3,
                        'current_bookings' => 0,
                    ]
                );
            }
        }

        // ── Shop Reviews ─────────────────────────────────────────────────────
        $reviewComments = [
            'Excellent craftsmanship and attention to detail!',
            'Very professional service, garment fits perfectly.',
            'Highly recommend. Quick turnaround time.',
            'Beautiful work on my Filipiniana dress.',
            'Staff were very accommodating and helpful.',
        ];
        foreach ($orders as $i => $order) {
            if (!in_array($order->status, ['completed', 'released'])) continue;
            ShopReview::firstOrCreate(
                ['shop_id' => $shop->id, 'user_id' => $order->user_id, 'order_id' => $order->id],
                ['rating' => rand(4, 5), 'comment' => $reviewComments[$i % count($reviewComments)]]
            );
        }

        $this->command->info('✓ Single-shop seed complete.');
        $this->command->info("  Shop: {$shop->name} (ID: {$shop->id})");
        $this->command->info('  Super Admin: superadmin@tahiconnect.com / password');
        $this->command->info('  Shop Owner:  owner@shop.com / password');
        $this->command->info('  Staff:       elena@shop.com / password');
        $this->command->info('  Customer:    ana@email.com / password');
    }
}
