<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /** @var list<array<string, mixed>> */
    private array $products = [
        ['code' => 'ELKT-001', 'name' => 'Laptop Dell Latitude 5540', 'category' => 'Elektronik', 'stock' => 10, 'condition' => 'good', 'location' => 'Gedung A, Lantai 2'],
        ['code' => 'ELKT-002', 'name' => 'Proyektor Epson EB-X51', 'category' => 'Elektronik', 'stock' => 4, 'condition' => 'good', 'location' => 'Gedung C, Lantai 2'],
        ['code' => 'ELKT-003', 'name' => 'Monitor LG 24" Full HD', 'category' => 'Elektronik', 'stock' => 15, 'condition' => 'good', 'location' => 'Gedung A, Lantai 3'],
        ['code' => 'FURN-001', 'name' => 'Meja Kerja Staff 120x60 cm', 'category' => 'Furnitur', 'stock' => 20, 'condition' => 'good', 'location' => 'Gudang, Lantai 1'],
        ['code' => 'FURN-002', 'name' => 'Kursi Ergonomis Highback', 'category' => 'Furnitur', 'stock' => 25, 'condition' => 'good', 'location' => 'Gudang, Lantai 2'],
        ['code' => 'ALTU-001', 'name' => 'Spidol Board Marker Pilot (set 4 warna)', 'category' => 'Alat Tulis', 'stock' => 20, 'condition' => 'good', 'location' => 'Gedung A, Lantai 1'],
        ['code' => 'ALTU-002', 'name' => 'Kalkulator Casio FX-991EX', 'category' => 'Alat Tulis', 'stock' => 10, 'condition' => 'good', 'location' => 'Gedung B, Lantai 1'],
        ['code' => 'JRNG-001', 'name' => 'Switch TP-Link 24-Port Gigabit', 'category' => 'Peralatan Jaringan', 'stock' => 6, 'condition' => 'good', 'location' => 'Server Room, Lantai 1'],
        ['code' => 'JRNG-002', 'name' => 'Access Point Ubiquiti UAP-AC-Lite', 'category' => 'Peralatan Jaringan', 'stock' => 10, 'condition' => 'lightly_damaged', 'location' => 'Server Room, Lantai 1'],
        ['code' => 'KEND-001', 'name' => 'Toyota Avanza 2022 (B 1234 XYZ)', 'category' => 'Kendaraan', 'stock' => 1, 'condition' => 'good', 'location' => 'Parkir Basement'],
    ];

    public function run(): void
    {
        $categories = Category::pluck('id', 'name');

        foreach ($this->products as $item) {
            Product::create([
                'code' => $item['code'],
                'name' => $item['name'],
                'category_id' => $categories[$item['category']] ?? null,
                'stock' => $item['stock'],
                'stock_available' => $item['stock'],
                'condition' => $item['condition'],
                'location' => $item['location'],
                'image' => null,
            ]);
        }
    }
}
