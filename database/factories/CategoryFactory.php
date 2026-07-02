<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    private static array $categories = [
        'Elektronik' => 'Perangkat elektronik seperti laptop, printer, dan monitor',
        'Furnitur' => 'Perabotan kantor seperti meja, kursi, dan lemari',
        'Alat Tulis' => 'Perlengkapan tulis menulis dan administrasi',
        'Peralatan Jaringan' => 'Switch, router, kabel, dan aksesori jaringan',
        'Kendaraan' => 'Kendaraan operasional kantor',
    ];

    private static int $index = 0;

    public function definition(): array
    {
        $keys = array_keys(self::$categories);
        $name = $keys[self::$index % count($keys)];
        $description = self::$categories[$name];
        self::$index++;

        return [
            'name' => $name,
            'description' => $description,
        ];
    }
}
