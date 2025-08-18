<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HardwareProfile;

class HardwareProfilesSeeder extends Seeder
{
    public function run(): void
    {
        $hwProfiles = [
            ['id' => '55fddf0b-0e56-4742-8791-106ad93c01ff', 'title' => 'Google Nexus 4', 'dimension' => '768 x 1280 dpi 320'],
            ['id' => '9fc4abf6-4a22-443d-b045-d3ef2bd96974', 'title' => 'Google Nexus 5', 'dimension' => '1080 x 1920 dpi 480'],
            ['id' => 'bf1a9765-c743-4cdc-95ec-c40a74493055', 'title' => 'Google Nexus 5X', 'dimension' => '1080 x 1920 dpi 420'],
            ['id' => 'a59951f2-ed13-40f9-80b9-3ddceb3c89f5', 'title' => 'Google Nexus 6', 'dimension' => '1440 x 2560 dpi 560'],
            ['id' => '180ddae8-436c-4ae3-a274-91144b7e4bb4', 'title' => 'Google Nexus 6P', 'dimension' => '1440 x 2560 dpi 560'],
            ['id' => '107d757e-463a-4a18-8667-b8dec6e4c87e', 'title' => 'Google Pixel', 'dimension' => '1080 x 1920 dpi 420'],
            ['id' => 'e6a305b5-ca40-4587-9aa8-623eb535b2f2', 'title' => 'Google Pixel 2', 'dimension' => '1080 x 1920 dpi 420'],
            ['id' => 'dfbdd1bc-cce2-4f27-b8be-535b93ff9ee7', 'title' => 'Google Pixel 2 XL', 'dimension' => '1440 x 2880 dpi 560'],
            ['id' => '143eb44a-1d3a-4f27-bcac-3c40124e2836', 'title' => 'Google Pixel 3', 'dimension' => '1080 x 2160 dpi 440'],
            ['id' => 'e5008049-8394-40fc-b7f8-87fa9f1c305f', 'title' => 'Google Pixel 3 XL', 'dimension' => '1440 x 2960 dpi 560'],
            ['id' => '95016679-8f8d-4890-b026-e4ad889aadf1', 'title' => 'Google Pixel 3a', 'dimension' => '1080 x 2220 dpi 420'],
            ['id' => '5a557f9d-982c-464d-87cf-cb30f89967b9', 'title' => 'Google Pixel 5', 'dimension' => '1080 x 2340 dpi 432'],
            ['id' => 'febef235-f39a-4a9d-a1fc-126639a2bf85', 'title' => 'Google Pixel 5a', 'dimension' => '1080 x 2400 dpi 413'],
            ['id' => 'bf540d7c-b8fa-4801-a91d-e6ca350ec24d', 'title' => 'Google Pixel 6', 'dimension' => '1080 x 2400 dpi 411'],
            ['id' => 'd9c8ba0e-5e19-46a5-826f-1c49839cb7ed', 'title' => 'Google Pixel 6 Pro', 'dimension' => '1440 x 3120 dpi 512'],
            ['id' => 'bd72d553-edc0-40fd-b652-02e9f718d764', 'title' => 'Google Pixel 6a', 'dimension' => '1080 x 2400 dpi 429'],
            ['id' => '8dc3a3f2-3175-4c04-8ebf-206e01a43fbb', 'title' => 'Google Pixel 7', 'dimension' => '1080 x 2400 dpi 416'],
            ['id' => 'beeeadb9-82cb-401a-8378-e320aca9f23b', 'title' => 'Google Pixel 8', 'dimension' => '1080 x 2400 dpi 428'],
            ['id' => '9e2a7753-4c6a-494b-b3fe-a27691081914', 'title' => 'Google Pixel 8 Pro', 'dimension' => '1344 x 2992 dpi 489'],
            ['id' => 'bd402826-4ee6-4598-94df-da4f89021042', 'title' => 'Google Pixel XL', 'dimension' => '1440 x 2560 dpi 560'],
            ['id' => '2f71872c-8bbc-4260-a284-7c6f50ede169', 'title' => 'HTC One', 'dimension' => '1080 x 1920 dpi 480'],
            ['id' => '47f68b9a-08cb-4b55-b0a7-a6664d118829', 'title' => 'Motorola Moto X', 'dimension' => '720 x 1280 dpi 320'],
            ['id' => 'a2631466-c3e1-4fea-b625-80ee31f384ee', 'title' => 'Samsung A10', 'dimension' => '720 x 1520 dpi 260'],
            ['id' => 'b5fb8027-05a1-4ade-937b-38b04696104e', 'title' => 'Samsung A50', 'dimension' => '1080 x 2340 dpi 400'],
            ['id' => '164f43bf-b2f7-493f-9287-699c8d1d0779', 'title' => 'Samsung Galaxy A14', 'dimension' => '1080 x 2408 dpi 400'],
            ['id' => 'de20111c-332a-4cb4-8088-d4a7f8f961ec', 'title' => 'Samsung Galaxy Note 2', 'dimension' => '720 x 1280 dpi 320'],
            ['id' => 'c1fb5618-68de-47c6-9659-e28a30de156c', 'title' => 'Samsung Galaxy Note 3', 'dimension' => '1080 x 1920 dpi 480'],
            ['id' => 'c6c6a0f3-0b13-4411-b30b-4827fb699500', 'title' => 'Samsung Galaxy S10', 'dimension' => '1440 x 3040 dpi 560'],
            ['id' => 'e625349a-cf77-4190-91a3-67afeb6f39e8', 'title' => 'Samsung Galaxy S23', 'dimension' => '1080 x 2340 dpi 425'],
            ['id' => '1df05208-b43d-4ac4-a0af-1b60d7efd763', 'title' => 'Samsung Galaxy S3', 'dimension' => '720 x 1280 dpi 320'],
            ['id' => '15edb3ca-3bf4-4778-a85c-c04a7d005715', 'title' => 'Samsung Galaxy S4', 'dimension' => '1080 x 1920 dpi 480'],
            ['id' => '4a7cf261-fa75-4a18-a564-718ebefba390', 'title' => 'Samsung Galaxy S5', 'dimension' => '1080 x 1920 dpi 480'],
            ['id' => '6185bb7c-1f96-40bb-9f6c-798bd7547d36', 'title' => 'Samsung Galaxy S6', 'dimension' => '1440 x 2560 dpi 640'],
            ['id' => 'e7a4ecd9-6044-41c7-ace3-ccee5402b590', 'title' => 'Samsung Galaxy S7', 'dimension' => '1440 x 2560 dpi 560'],
            ['id' => 'f34de94f-1e85-4d61-9b0d-c47968bc156c', 'title' => 'Samsung Galaxy S8', 'dimension' => '1440 x 2960 dpi 480'],
            ['id' => 'e20da1a3-313c-434a-9d43-7268b12fee08', 'title' => 'Samsung Galaxy S9', 'dimension' => '1440 x 2960 dpi 560'],
            ['id' => 'd8b10016-c02a-41f9-8a91-ce9b44197d21', 'title' => 'Xiaomi Redmi Note 7', 'dimension' => '1080 x 2340 dpi 420'],
            ['id' => '63d83ff8-551f-4a7a-b592-8973895d8292', 'title' => 'Xiaomi Redmi Note 9', 'dimension' => '1080 x 2340 dpi 395'],
        ];

        foreach ($hwProfiles as $profile) {
            HardwareProfile::updateOrCreate(['id' => $profile['id']], $profile);
        }
    }
}


