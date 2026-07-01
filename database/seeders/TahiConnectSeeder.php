<?php

namespace Database\Seeders;

use App\Models\User;
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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TahiConnectSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admins ──
        $admin1 = User::create([
            'name' => 'Maria Santos', 'first_name' => 'Maria', 'last_name' => 'Santos',
            'email' => 'admin@tahiconnect.com', 'password' => Hash::make('password'),
            'contact_number' => '+63 917 123 4567', 'role' => 'admin',
            'address' => '123 Rizal Ave, Makati City, Metro Manila',
        ]);
        $admin2 = User::create([
            'name' => 'Roberto Cruz', 'first_name' => 'Roberto', 'last_name' => 'Cruz',
            'email' => 'admin2@tahiconnect.com', 'password' => Hash::make('password'),
            'contact_number' => '+63 918 234 5678', 'role' => 'admin',
            'address' => '456 Ayala Blvd, Quezon City, Metro Manila',
        ]);

        // ── Staff ──
        $staff = [];
        $staffData = [
            ['Elena', 'Reyes', 'elena@tahiconnect.com', '+63 919 345 6789'],
            ['Carlos', 'Mendoza', 'carlos@tahiconnect.com', '+63 920 456 7890'],
            ['Patricia', 'Garcia', 'patricia@tahiconnect.com', '+63 921 567 8901'],
            ['Miguel', 'Torres', 'miguel@tahiconnect.com', '+63 922 678 9012'],
        ];
        foreach ($staffData as $s) {
            $staff[] = User::create([
                'name' => "$s[0] $s[1]", 'first_name' => $s[0], 'last_name' => $s[1],
                'email' => $s[2], 'password' => Hash::make('password'),
                'contact_number' => $s[3], 'role' => 'tailor_staff',
                'address' => 'TahiConnect Workshop, BGC, Taguig City',
            ]);
        }

        // ── Customers ──
        $customers = [];
        $customerData = [
            ['Ana', 'Dela Cruz', 'ana@email.com', '+63 923 111 2233'],
            ['Juan', 'Bautista', 'juan@email.com', '+63 924 222 3344'],
            ['Sofia', 'Ramirez', 'sofia@email.com', '+63 925 333 4455'],
            ['Marco', 'Villanueva', 'marco@email.com', '+63 926 444 5566'],
            ['Isabella', 'Fernandez', 'isabella@email.com', '+63 927 555 6677'],
            ['Gabriel', 'Aquino', 'gabriel@email.com', '+63 928 666 7788'],
            ['Carmen', 'Lim', 'carmen@email.com', '+63 929 777 8899'],
            ['Rafael', 'Navarro', 'rafael@email.com', '+63 930 888 9900'],
            ['Lucia', 'Pangilinan', 'lucia@email.com', '+63 931 999 0011'],
            ['Diego', 'Soriano', 'diego@email.com', '+63 932 000 1122'],
        ];
        foreach ($customerData as $c) {
            $customers[] = User::create([
                'name' => "$c[0] $c[1]", 'first_name' => $c[0], 'last_name' => $c[1],
                'email' => $c[2], 'password' => Hash::make('password'),
                'contact_number' => $c[3], 'role' => 'customer',
                'address' => 'Metro Manila, Philippines',
            ]);
        }

        // ── Garment Types ──
        $garments = [];
        $garmentData = [
            ['Barong Tagalog', 'Traditional Filipino formal wear made from lightweight fabric with intricate embroidery.', 3500],
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
        foreach ($garmentData as $g) {
            $garments[] = GarmentType::create([
                'name' => $g[0], 'description' => $g[1], 'base_price' => $g[2],
            ]);
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
            ['Polyester Blend', 'Poly-cotton', 'Charcoal Gray', 180],
            ['Ramie', 'Natural ramie', 'Off-White', 550],
        ];
        foreach ($fabricData as $f) {
            Fabric::create([
                'name' => $f[0], 'material' => $f[1], 'color' => $f[2], 'price_per_meter' => $f[3], 'in_stock' => true,
            ]);
        }

        // ── Measurements ──
        $measurementData = [
            [96.5, 81.3, 96.5, 44.5, 61.0, 78.7, 38.1, 170.2],
            [101.6, 86.4, 101.6, 46.0, 63.5, 81.3, 40.6, 175.3],
            [86.4, 68.6, 91.4, 38.1, 55.9, 76.2, 33.0, 162.6],
            [106.7, 91.4, 104.1, 48.3, 66.0, 83.8, 41.9, 180.3],
            [83.8, 66.0, 88.9, 37.0, 54.6, 73.7, 32.0, 160.0],
            [99.1, 83.8, 99.1, 45.7, 62.2, 80.0, 39.4, 172.7],
            [81.3, 63.5, 86.4, 35.6, 53.3, 71.1, 31.0, 157.5],
            [104.1, 88.9, 102.9, 47.6, 64.8, 82.6, 41.0, 177.8],
        ];
        $statuses = ['validated', 'validated', 'pending', 'validated', 'validated', 'pending', 'validated', 'rejected'];
        for ($i = 0; $i < 8; $i++) {
            Measurement::create([
                'user_id' => $customers[$i]->id,
                'chest' => $measurementData[$i][0], 'waist' => $measurementData[$i][1],
                'hip' => $measurementData[$i][2], 'shoulder' => $measurementData[$i][3],
                'sleeve_length' => $measurementData[$i][4], 'inseam' => $measurementData[$i][5],
                'neck' => $measurementData[$i][6], 'height' => $measurementData[$i][7],
                'validation_status' => $statuses[$i],
            ]);
        }

        // ── Orders ──
        $allStatuses = ['pending','measurements_verified','in_production','fitting_scheduled','final_adjustment','ready_for_pickup','completed','released'];
        $fabricNames = ['Jusi','Piña Cloth','Silk','Organza','Cotton Twill','Linen','Chiffon','Satin'];
        $orders = [];
        for ($i = 0; $i < 20; $i++) {
            $customer = $customers[$i % count($customers)];
            $garment = $garments[$i % count($garments)];
            $staffMember = $staff[$i % count($staff)];
            $status = $allStatuses[$i % count($allStatuses)];
            $qty = rand(1, 3);
            $amount = $garment->base_price * $qty;

            $orders[] = Order::create([
                'user_id' => $customer->id,
                'staff_id' => $staffMember->id,
                'tracking_number' => 'TC-2025-' . str_pad($i + 1001, 4, '0', STR_PAD_LEFT),
                'garment_type_id' => $garment->id,
                'fabric_preference' => $fabricNames[$i % count($fabricNames)],
                'quantity' => $qty,
                'special_instructions' => $i % 3 === 0 ? 'Please use traditional embroidery pattern' : ($i % 3 === 1 ? 'Slim fit preferred' : null),
                'status' => $status,
                'estimated_completion' => Carbon::now()->addDays(rand(7, 45)),
                'total_amount' => $amount,
            ]);
        }

        // ── Order Status History ──
        foreach ($orders as $order) {
            $statusIdx = array_search($order->status, $allStatuses);
            for ($s = 0; $s <= $statusIdx; $s++) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $allStatuses[$s],
                    'changed_by' => $staff[0]->id,
                    'notes' => match($allStatuses[$s]) {
                        'pending' => 'Order received and queued for processing',
                        'measurements_verified' => 'Customer measurements verified and approved',
                        'in_production' => 'Garment is now in production',
                        'fitting_scheduled' => 'Fitting appointment has been scheduled',
                        'final_adjustment' => 'Making final adjustments after fitting',
                        'ready_for_pickup' => 'Order is ready for customer pickup',
                        'completed' => 'Order completed and picked up',
                        'released' => 'Order released to customer',
                        default => null,
                    },
                    'created_at' => Carbon::now()->subDays(($statusIdx - $s) * 3)->addHours(rand(8, 17)),
                ]);
            }
        }

        // ── Appointments ──
        $appointmentTypes = ['fitting', 'consultation', 'pickup'];
        $appointmentStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        for ($i = 0; $i < 15; $i++) {
            $isPast = $i < 5;
            Appointment::create([
                'user_id' => $customers[$i % count($customers)]->id,
                'staff_id' => $staff[$i % count($staff)]->id,
                'date' => $isPast ? Carbon::now()->subDays(rand(1, 30)) : Carbon::now()->addDays(rand(1, 21)),
                'time' => sprintf('%02d:00:00', rand(9, 16)),
                'type' => $appointmentTypes[$i % 3],
                'status' => $isPast ? ($i % 2 === 0 ? 'completed' : 'cancelled') : ($i % 2 === 0 ? 'pending' : 'confirmed'),
                'notes' => $i % 2 === 0 ? 'Please prepare measurement tools' : null,
            ]);
        }

        // ── Payments ──
        for ($i = 0; $i < 15; $i++) {
            $order = $orders[$i];
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
        $notifData = [
            ['order', 'Order Confirmed', 'Your order TC-2025-1001 for Barong Tagalog has been confirmed.'],
            ['appointment', 'Appointment Reminder', 'You have a fitting appointment tomorrow at 10:00 AM.'],
            ['payment', 'Payment Received', 'We received your payment of ₱3,500.00 for order TC-2025-1003.'],
            ['system', 'Welcome to TahiConnect', 'Thank you for joining TahiConnect! Start by adding your measurements.'],
            ['order', 'Order In Production', 'Your Filipiniana Dress is now being crafted by our expert tailors.'],
            ['appointment', 'Appointment Confirmed', 'Your consultation on July 15 at 2:00 PM has been confirmed.'],
            ['payment', 'Payment Reminder', 'You have an outstanding balance of ₱4,250.00 for order TC-2025-1005.'],
            ['order', 'Ready for Pickup', 'Your Men\'s Suit is ready! Visit our shop to pick it up.'],
            ['system', 'New Feature: Virtual Try-On', 'Try our new AI-powered virtual try-on feature to preview your garments!'],
            ['order', 'Fitting Scheduled', 'Your fitting for the Formal Gown has been scheduled for July 20.'],
            ['appointment', 'Appointment Cancelled', 'Your pickup appointment on July 10 has been cancelled.'],
            ['payment', 'Refund Processed', 'A refund of ₱1,200.00 has been processed to your account.'],
            ['order', 'Measurements Verified', 'Your measurements have been verified for order TC-2025-1008.'],
            ['system', 'Profile Update', 'Please update your contact number in your profile settings.'],
            ['order', 'Final Adjustment', 'Final adjustments are being made to your Terno.'],
            ['appointment', 'New Time Slot', 'New appointment slots are available for next week.'],
            ['payment', 'Payment Successful', 'Your GCash payment of ₱5,000.00 was successful.'],
            ['order', 'Order Completed', 'Your Baro\'t Saya order has been completed. Thank you!'],
            ['system', 'Maintenance Notice', 'System maintenance scheduled for July 25, 2:00-4:00 AM.'],
            ['order', 'Design Approved', 'The design for your Corporate Uniform has been approved.'],
        ];
        foreach ($notifData as $idx => $n) {
            CustomNotification::create([
                'user_id' => $customers[$idx % count($customers)]->id,
                'type' => $n[0], 'title' => $n[1], 'message' => $n[2],
                'is_read' => $idx > 10,
                'created_at' => Carbon::now()->subHours(rand(1, 200)),
            ]);
        }

        // ── Available Time Slots ──
        for ($day = 0; $day < 14; $day++) {
            $date = Carbon::now()->addDays($day);
            if ($date->isWeekend()) continue;
            $slots = [['09:00','10:00'],['10:00','11:00'],['11:00','12:00'],['13:00','14:00'],['14:00','15:00'],['15:00','16:00']];
            foreach ($slots as $slot) {
                AvailableTimeSlot::create([
                    'date' => $date->toDateString(),
                    'start_time' => $slot[0], 'end_time' => $slot[1],
                    'is_available' => rand(0, 4) > 0,
                    'max_bookings' => 3,
                    'current_bookings' => rand(0, 2),
                ]);
            }
        }

        // ── Activity Logs ──
        $activities = [
            ['Order Placed', 'Placed a new order for Barong Tagalog'],
            ['Measurement Updated', 'Updated body measurements'],
            ['Appointment Booked', 'Booked a fitting appointment for July 18'],
            ['Payment Made', 'Made a payment of ₱3,500.00 via GCash'],
            ['Order Placed', 'Placed a new order for Filipiniana Dress'],
            ['Profile Updated', 'Updated contact number'],
            ['Virtual Try-On', 'Generated a virtual try-on preview'],
            ['Appointment Cancelled', 'Cancelled consultation appointment'],
            ['Payment Made', 'Made a cash payment of ₱5,000.00'],
            ['Order Placed', 'Placed a new order for Men\'s Suit'],
            ['Measurement Updated', 'Added new measurements for formal gown'],
            ['Appointment Booked', 'Booked a pickup appointment'],
            ['Design Uploaded', 'Uploaded design reference for Terno'],
            ['Order Placed', 'Placed a new order for School Uniform'],
            ['Payment Made', 'Made a bank transfer of ₱8,500.00'],
        ];
        foreach ($activities as $idx => $a) {
            ActivityLog::create([
                'user_id' => $customers[$idx % count($customers)]->id,
                'action' => $a[0], 'description' => $a[1],
                'created_at' => Carbon::now()->subHours(rand(1, 150)),
            ]);
        }
    }
}
