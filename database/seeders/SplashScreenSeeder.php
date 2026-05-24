<?php

namespace Database\Seeders;

use App\Domains\Core\SplashScreen\Models\SplashScreen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Domains\Core\Upload\Models\Uploads;
use Illuminate\Support\Facades\File;

class SplashScreenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('splash_screens')->delete();
        $now = now(); 
        $type = 'splash_image';
        $folder = 'splashScreen/splash_image';
        $splashScreens = [
            [
                'id'         => 1,
                'title_en'      => 'Connecting People',
                'title_ar'      => 'Connecting People',
                'description_en' => 'Wabell is a service application that connects people with people.',
                'description_ar' => 'Wabell is a service application that connects people with people.',
                'status'         => 'active',
                'position'         => 0,
                'created_at' => $now,
                'updated_at' => $now,
                'splash_image'      => 'default_images/splash_images/onboarding_one.png'
            ],
            [
                'id'         => 2,
                'title_en'      => 'Empowering Learners and Service Masters',
                'title_ar'      => 'Empowering Learners and Service Masters',
                'description_en' => 'Register as a Master, Wabell enables Learners to connect with you.',
                'description_ar' => 'Register as a Master, Wabell enables Learners to connect with you.',
                'status'         => 'active',
                'position'         => 1,
                'created_at' => $now,
                'updated_at' => $now,
                'splash_image'      => 'default_images/splash_images/onboarding_two.png'
            ],
            [
                'id'         => 3,
                'title_en'      => 'Welcome to Wabell',
                'title_ar'      => 'Welcome to Wabell',
                'description_en' => 'Enjoy Wabell and create your profile now.',
                'description_ar' => 'Enjoy Wabell and create your profile now.',
                'status'         => 'active',
                'position'      => 2,
                'created_at' => $now,
                'updated_at' => $now,
                'splash_image'      => 'default_images/splash_images/onboarding_three.png'
            ],
            [
                'id'         => 4,
                'title_en'      => 'Welcome to Wabell',
                'title_ar'      => 'Welcome to Wabell',
                'description_en' => 'Enjoy Wabell and create your profile now.',
                'description_ar' => 'Enjoy Wabell and create your profile now.',
                'status'         => 'active',
                'position'      => 3,
                'created_at' => $now,
                'updated_at' => $now,
                'splash_image'      => 'default_images/splash_images/onboarding_three.png'
            ],
            [
                'id'         => 5,
                'title_en'      => 'Welcome to Wabell',
                'title_ar'      => 'Welcome to Wabell',
                'description_en' => 'Enjoy Wabell and create your profile now.',
                'description_ar' => 'Enjoy Wabell and create your profile now.',
                'status'         => 'active',
                'position'      => 4,
                'created_at' => $now,
                'updated_at' => $now,
                'splash_image'      => 'default_images/splash_images/onboarding_three.png'
            ],
        ];
        foreach ($splashScreens as $data) {
            // Create the Article
            $splashScreen = SplashScreen::create([
                'title_en'        => $data['title_en'],
                'description_en'  => $data['description_en'],
                'title_ar'        => $data['title_ar'],
                'description_ar'  => $data['description_ar'],
                'status'       => $data['status'],
                'position'      => $data['position'],
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
 
            // Handle the image for this article
            $sourcePath = public_path($data['splash_image']);
            if (File::exists($sourcePath)) {
                $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
                $filename = basename($sourcePath);
                $finalPath = "$folder/$filename";
                $destinationPath = public_path('storage/'.$finalPath);
 
                File::ensureDirectoryExists(dirname($destinationPath));
 
                // Copy the file
                File::copy($sourcePath, $destinationPath);
 
                // Get file info
                $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
 
                // Create an entry in the uploads table using the morph relation
                $upload = new Uploads();
 
                $upload->file_path          = $finalPath;
                $upload->extension          = $extension;
                $upload->original_file_name = $filename;
                $upload->type               = $type;
                $upload->file_type          = 'image';
                $upload->orientation        = null;
               
                $splashScreen->uploads()->save($upload);
            } else {
                $this->command->warn("Image file not found: " . $sourcePath);
            }
        }
        // SplashScreen::insert($splashScreens);
    }
}
