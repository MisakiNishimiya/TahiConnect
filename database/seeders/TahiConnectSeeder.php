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
        // ── Platform Admins ──
        $admin1 = User::create([
            'name' => 'Maria Santos', 'first_name' => 'Maria', 'last_name' => 'Santos',
            'email' => 'admin@tahiconnect.com', 'password' => Hash::make('password'),
            'contact_number' => '+63 917 123 4567', 'role' => 'admin',
            'address' => '123 Rizal Ave, Makati City, Metro Manila',
        ]);

        // ── Shop Owners ──
        $ownerData = [
            ['Rosa', 'Alcantara', 'rosa@tahiconnect.com'],
            ['Jun', 'Mendoza', 'jun@tahiconnect.com'],
            ['Lito', 'Garcia', 'lito@tahiconnect.com'],
        ];
        $owners = [];
        foreach ($ownerData as $o) {
            $owners[] = User::create([
                'name' => "$o[0] $o[1]", 'first_name' => $o[0], 'last_name' => $o[1],
                'email' => $o[2], 'password' => Hash::make('password'),
                'contact_number' => '+63 918 ' . rand(100, 999) . ' ' . rand(1000, 9999),
                'role' => 'shop_owner',
            ]);
        }

        // ── Shops ──
        $shopData = [
            [
                'name' => "Aling Rosa's Tailoring", 'owner_id' => $owners[0]->id,
                'address' => '123 Bajada', 'barangay' => 'Bajada', 'city' => 'Davao City',
                'specialties' => ['Barong Tagalog', 'Filipiniana', 'Gowns'],
                'rating' => 4.8, 'total_reviews' => 124
            ],
            [
                'name' => "Tahi ni Jun", 'owner_id' => $owners[1]->id,
                'address' => '456 Matina', 'barangay' => 'Matina', 'city' => 'Davao City',
                'specialties' => ['Men\'s Suits', 'Corporate Uniforms'],
                'rating' => 4.5, 'total_reviews' => 89
            ],
            [
                'name' => "Davao Suits & Gowns", 'owner_id' => $owners[2]->id,
                'address' => '789 Toril', 'barangay' => 'Toril', 'city' => 'Davao City',
                'specialties' => ['Wedding Gowns', 'Tuxedos', 'Prom Dresses'],
                'rating' => 4.9, 'total_reviews' => 210
            ],
        ];
        $shops = [];
        foreach ($shopData as $s) {
            $shops[] = Shop::create([
                'owner_id' => $s['owner_id'],
                'name' => $s['name'],
                'slug' => Str::slug($s['name']),
                'address' => $s['address'],
                'barangay' => $s['barangay'],
                'city' => $s['city'],
                'specialties' => $s['specialties'],
                'rating' => $s['rating'],
                'total_reviews' => $s['total_reviews'],
                'is_verified' => true,
                'is_active' => true,
                'operating_hours' => [
                    'Monday' => '09:00-17:00', 'Tuesday' => '09:00-17:00',
                    'Wednesday' => '09:00-17:00', 'Thursday' => '09:00-17:00',
                    'Friday' => '09:00-17:00', 'Saturday' => '10:00-15:00', 'Sunday' => 'Closed'
                ],
            ]);
        }

        // Assign Shop Owners their shop_id
        foreach ($owners as $idx => $owner) {
            $owner->update(['shop_id' => $shops[$idx]->id]);
        }

        // ── Staff ──
        $staff = [];
        $staffData = [
            ['Elena', 'Reyes', 'elena@tahiconnect.com', 0],
            ['Carlos', 'Mendoza', 'carlos@tahiconnect.com', 0],
            ['Patricia', 'Garcia', 'patricia@tahiconnect.com', 1],
            ['Miguel', 'Torres', 'miguel@tahiconnect.com', 2],
        ];
        foreach ($staffData as $s) {
            $staff[] = User::create([
                'name' => "$s[0] $s[1]", 'first_name' => $s[0], 'last_name' => $s[1],
                'email' => $s[2], 'password' => Hash::make('password'),
                'contact_number' => '+63 919 ' . rand(100, 999) . ' ' . rand(1000, 9999),
                'role' => 'tailor_staff',
                'shop_id' => $shops[$s[3]]->id,
            ]);
        }

        // ── Customers ──
        $customers = [];
        $customerData = [
            ['Ana', 'Dela Cruz', 'ana@email.com'],
            ['Juan', 'Bautista', 'juan@email.com'],
            ['Sofia', 'Ramirez', 'sofia@email.com'],
            ['Marco', 'Villanueva', 'marco@email.com'],
            ['Isabella', 'Fernandez', 'isabella@email.com'],
            ['Gabriel', 'Aquino', 'gabriel@email.com'],
            ['Carmen', 'Lim', 'carmen@email.com'],
            ['Rafael', 'Navarro', 'rafael@email.com'],
            ['Lucia', 'Pangilinan', 'lucia@email.com'],
            ['Diego', 'Soriano', 'diego@email.com'],
        ];
        foreach ($customerData as $c) {
            $customers[] = User::create([
                'name' => "$c[0] $c[1]", 'first_name' => $c[0], 'last_name' => $c[1],
                'email' => $c[2], 'password' => Hash::make('password'),
                'contact_number' => '+63 923 ' . rand(100, 999) . ' ' . rand(1000, 9999),
                'role' => 'customer',
            ]);
        }

        // ── Garment Types ──
        $garmentData = [
            ['Barong Tagalog', 'Traditional Filipino formal wear made from lightweight fabric.', 3500],
            ['Filipiniana Dress', 'Elegant butterfly-sleeved terno dress for formal occasions.', 5000],
            ['Terno', 'Modern interpretation of the classic Filipino terno with butterfly sleeves.', 6500],
            ["Baro't Saya", 'Traditional Filipino blouse and skirt ensemble.', 4000],
            ["Men's Suit", 'Custom-tailored two-piece or three-piece suit.', 8500],
            ["Ladies' Blouse", 'Bespoke ladies blouse with custom fit.', 1800],
            ['Formal Gown', 'Floor-length formal gown for special occasions.', 12000],
            ['School Uniform', 'Standard school uniform with precise measurements.', 1200],
            ['Corporate Uniform', 'Professional corporate attire set.', 2500],
            ['Casual Wear', 'Custom casual clothing tailored to your style.', 1500],
        ];
        
        $allGarments = [];
        foreach ($shops as $shop) {
            // Give each shop a random subset of 6 garments
            $shopGarments = collect($garmentData)->random(6);
            foreach ($shopGarments as $g) {
                $allGarments[] = GarmentType::create([
                    'shop_id' => $shop->id,
                    'name' => $g[0], 'description' => $g[1], 'base_price' => $g[2],
                ]);
            }
        }

        // ── Fabrics ──
        $fabricData = [
            ['Jusi', 'Banana silk', 'Ivory', 450],
            ['Piña Cloth', 'Pineapple fiber', 'Natural White', 1200],
            ['Organza', 'Sheer woven', 'Champagne', 350],
            ['Silk', 'Natural silk', 'Pearl White', 800],
            ['Cotton Twill', 'Cotton weave', 'Navy Blue', 250],
            ['Linen', 'Natural linen', 'Beige', 380],
            ['Chiffon', 'Sheer chiffon', 'Blush Pink', 300],
            ['Satin', 'Smooth satin', 'Midnight Black', 420],
        ];
        foreach ($shops as $shop) {
            $shopFabrics = collect($fabricData)->random(5);
            foreach ($shopFabrics as $f) {
                Fabric::create([
                    'shop_id' => $shop->id,
                    'name' => $f[0], 'material' => $f[1], 'color' => $f[2], 'price_per_meter' => $f[3], 'in_stock' => true,
                ]);
            }
        }

        // ── Pre-made Products ──
        $productData = [
            ['Standard White Barong (RTW)', 'Classic semi-formal white barong, ready to wear.', 2500, ['XS', 'S', 'M', 'L', 'XL']],
            ['Pre-made Formal Slacks', 'Straight-cut dark slacks, perfect for corporate or formal use.', 1200, ['28', '30', '32', '34', '36']],
            ['Casual Linen Shirt', 'Relaxed-fit linen shirt ideal for tropical weather.', 950, ['S', 'M', 'L', 'XL']],
            ['Filipiniana Shawl / Alampay', 'Traditional shawl to complement casual or formal Filipiniana dresses.', 1800, ['Free Size']],
        ];

        $allProducts = [];
        foreach ($shops as $shop) {
            foreach ($productData as $p) {
                $allProducts[] = PreMadeProduct::create([
                    'shop_id' => $shop->id,
                    'name' => $p[0],
                    'description' => $p[1],
                    'price' => $p[2],
                    'available_sizes' => $p[3],
                    'is_active' => true,
                ]);
            }
        }

        // ── Measurements ──
        $measurementData = [
            [96.5, 81.3, 96.5, 44.5, 61.0, 78.7, 38.1, 170.2],
            [101.6, 86.4, 101.6, 46.0, 63.5, 81.3, 40.6, 175.3],
            [86.4, 68.6, 91.4, 38.1, 55.9, 76.2, 33.0, 162.6],
            [106.7, 91.4, 104.1, 48.3, 66.0, 83.8, 41.9, 180.3],
        ];
        $statuses = ['validated', 'pending', 'validated', 'rejected'];
        for ($i = 0; $i < 8; $i++) {
            Measurement::create([
                'user_id' => $customers[$i]->id,
                'chest' => $measurementData[$i % 4][0], 'waist' => $measurementData[$i % 4][1],
                'hip' => $measurementData[$i % 4][2], 'shoulder' => $measurementData[$i % 4][3],
                'sleeve_length' => $measurementData[$i % 4][4], 'inseam' => $measurementData[$i % 4][5],
                'neck' => $measurementData[$i % 4][6], 'height' => $measurementData[$i % 4][7],
                'validation_status' => $statuses[$i % 4],
            ]);
        }

        // ── Orders ──
        $allStatuses = ['pending','measurements_verified','in_production','fitting_scheduled','final_adjustment','ready_for_pickup','completed','released'];
        $preMadeStatuses = ['pending', 'in_production', 'ready_for_pickup', 'completed', 'released'];
        $orders = [];
        for ($i = 0; $i < 20; $i++) {
            $customer = $customers[$i % count($customers)];
            $shop = $shops[$i % count($shops)];
            $shopStaff = User::where('shop_id', $shop->id)->where('role', 'tailor_staff')->get();
            $staffMember = $shopStaff->count() > 0 ? $shopStaff->random() : null;
            
            $isPreMade = ($i % 4 === 0);
            $qty = rand(1, 3);
            
            if ($isPreMade) {
                // Find products for this shop
                $shopProducts = PreMadeProduct::where('shop_id', $shop->id)->get();
                $product = $shopProducts->count() > 0 ? $shopProducts->random() : $allProducts[0];
                $status = $preMadeStatuses[$i % count($preMadeStatuses)];
                $size = collect($product->available_sizes)->random();
                $amount = $product->price * $qty;

                $orders[] = Order::create([
                    'shop_id' => $shop->id,
                    'user_id' => $customer->id,
                    'staff_id' => $staffMember?->id,
                    'tracking_number' => 'TC-2025-' . str_pad($i + 1001, 4, '0', STR_PAD_LEFT),
                    'order_type' => 'pre_made',
                    'pre_made_product_id' => $product->id,
                    'product_size' => $size,
                    'garment_type_id' => null,
                    'fabric_preference' => null,
                    'quantity' => $qty,
                    'status' => $status,
                    'estimated_completion' => Carbon::now()->addDays(rand(2, 7)),
                    'total_amount' => $amount,
                ]);
            } else {
                $shopGarments = GarmentType::where('shop_id', $shop->id)->get();
                $garment = $shopGarments->count() > 0 ? $shopGarments->random() : $allGarments[0];
                $status = $allStatuses[$i % count($allStatuses)];
                $amount = $garment->base_price * $qty;

                $orders[] = Order::create([
                    'shop_id' => $shop->id,
                    'user_id' => $customer->id,
                    'staff_id' => $staffMember?->id,
                    'tracking_number' => 'TC-2025-' . str_pad($i + 1001, 4, '0', STR_PAD_LEFT),
                    'order_type' => 'custom',
                    'garment_type_id' => $garment->id,
                    'fabric_preference' => 'Silk',
                    'quantity' => $qty,
                    'status' => $status,
                    'estimated_completion' => Carbon::now()->addDays(rand(7, 45)),
                    'total_amount' => $amount,
                ]);
            }
        }

        // ── Order Status History ──
        foreach ($orders as $order) {
            if ($order->order_type === 'pre_made') {
                $statusIdx = array_search($order->status, $preMadeStatuses);
                for ($s = 0; $s <= $statusIdx; $s++) {
                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status' => $preMadeStatuses[$s],
                        'changed_by' => $order->staff_id,
                        'created_at' => Carbon::now()->subDays(($statusIdx - $s) * 1)->addHours(rand(1, 4)),
                    ]);
                }
            } else {
                $statusIdx = array_search($order->status, $allStatuses);
                for ($s = 0; $s <= $statusIdx; $s++) {
                    OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status' => $allStatuses[$s],
                        'changed_by' => $order->staff_id,
                        'created_at' => Carbon::now()->subDays(($statusIdx - $s) * 3)->addHours(rand(8, 17)),
                    ]);
                }
            }
        }

        // ── Appointments ──
        $appointmentTypes = ['initial_measurement', 'fabric_selection', 'baste_fitting', 'final_pickup'];
        for ($i = 0; $i < 15; $i++) {
            $isPast = $i < 5;
            $shop = $shops[$i % count($shops)];
            $shopStaff = User::where('shop_id', $shop->id)->where('role', 'tailor_staff')->get();
            $staffMember = $shopStaff->count() > 0 ? $shopStaff->random() : null;
            
            Appointment::create([
                'shop_id' => $shop->id,
                'user_id' => $customers[$i % count($customers)]->id,
                'staff_id' => $staffMember?->id,
                'date' => $isPast ? Carbon::now()->subDays(rand(1, 30)) : Carbon::now()->addDays(rand(1, 21)),
                'time' => sprintf('%02d:00:00', rand(9, 16)),
                'type' => $appointmentTypes[$i % 4],
                'status' => $isPast ? ($i % 2 === 0 ? 'completed' : 'cancelled') : ($i % 2 === 0 ? 'pending' : 'confirmed'),
            ]);
        }

        // ── Payments ──
        foreach ($orders as $i => $order) {
            $isPaid = in_array($order->status, ['completed', 'released', 'ready_for_pickup']);
            Payment::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'amount' => $isPaid ? $order->total_amount : $order->total_amount * 0.5,
                'payment_method' => ['cash', 'gcash', 'bank_transfer', 'card'][$i % 4],
                'status' => $isPaid ? 'paid' : 'pending',
                'payment_date' => $isPaid ? Carbon::now()->subDays(rand(1, 20)) : null,
                'reference_number' => $isPaid ? 'REF-' . strtoupper(substr(md5($i), 0, 8)) : null,
            ]);
        }

        // ── Notifications ──
        for ($i = 0; $i < 20; $i++) {
            CustomNotification::create([
                'user_id' => $customers[$i % count($customers)]->id,
                'type' => 'system', 'title' => 'System Update', 'message' => 'Welcome to TahiConnect.',
                'is_read' => $i > 10,
                'created_at' => Carbon::now()->subHours(rand(1, 200)),
            ]);
        }

        // ── Available Time Slots ──
        foreach ($shops as $shop) {
            for ($day = 0; $day < 7; $day++) {
                $date = Carbon::now()->addDays($day);
                if ($date->isWeekend()) continue;
                $slots = [['09:00','10:00'],['10:00','11:00'],['11:00','12:00']];
                foreach ($slots as $slot) {
                    AvailableTimeSlot::create([
                        'shop_id' => $shop->id,
                        'date' => $date->toDateString(),
                        'start_time' => $slot[0], 'end_time' => $slot[1],
                        'is_available' => true,
                        'max_bookings' => 3,
                        'current_bookings' => 0,
                    ]);
                }
            }
        }

        // ── Shop Reviews ──
        foreach ($shops as $shop) {
            for ($i = 0; $i < 5; $i++) {
                ShopReview::create([
                    'shop_id' => $shop->id,
                    'user_id' => $customers[rand(0, 9)]->id,
                    'rating' => rand(4, 5),
                    'comment' => 'Great service and quality!',
                ]);
            }
        }
    }
}
