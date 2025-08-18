<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OsImage;

class OsImagesSeeder extends Seeder
{
    public function run(): void
    {
        $osimages = [
            ['id' => '7e2f195a-4fdc-48df-8877-4681473d6cc0', 'android' => 'Android 10.0', 'version' => '10.0.0', 'skdVersion' => '29', 'arch' => 'x86'],
            ['id' => '85efd3fd-c5f2-402b-a013-3cb32871731b', 'android' => 'Android 11.0', 'version' => '11.0.0', 'skdVersion' => '30', 'arch' => 'x86_64'],
            ['id' => 'def4b92c-3a4a-43ea-8f22-1823ba474752', 'android' => 'Android 12.0', 'version' => '12.0.0', 'skdVersion' => '31', 'arch' => 'x86_64'],
            ['id' => '26958fcc-ba2b-44d3-9798-84a6105f56de', 'android' => 'Android 12.1', 'version' => '12.1.0', 'skdVersion' => '32', 'arch' => 'x86_64'],
            ['id' => '5f94a2c5-29cf-4deb-9ba0-42cd7a4d41b7', 'android' => 'Android 13.0', 'version' => '13.0.0', 'skdVersion' => '33', 'arch' => 'x86_64'],
        ];

        foreach ($osimages as $row) {
            OsImage::updateOrCreate(['id' => $row['id']], $row);
        }
    }
}


