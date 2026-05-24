<?php

namespace Database\Seeders;

use App\Domains\Core\Subscription\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Domains\Core\Upload\Models\Uploads;
use Illuminate\Support\Facades\File;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('plans')->delete();
        $now = now();
        $type = 'plan_image';
        $varifiedType = 'verified_icon';
        $folder = 'plan/plan-images';
        $plans = [
            [
                'name_en' => 'Basic Plan',
                'name_ar' => 'الخطة الأساسية',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'features_en' =>'Feature A, Feature B, Feature C',
                'features_ar' =>'Feature A, Feature B, Feature C',
                'plan_image'    => 'default_images/plan_images/basic.png',
                'verified_icon' => 'default_images/plan_images/basic.png',
               'plan_slug' => config('constant.plan_name.basic'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name_en' => 'Gold Plan',
                'name_ar' => 'الخطة الذهبية',
                'monthly_price' => 80,
                'yearly_price' => 500,
                'features_en' =>'Feature A, Feature B, Feature C',
                'features_ar' =>'Feature A, Feature B, Feature C',
                'plan_image'      => 'default_images/plan_images/subscriber.png',
                'verified_icon'      => 'default_images/plan_images/subscriber.png',
                'plan_slug' => config('constant.plan_name.gold'),
                'ios_product_id' => "com.gold.monthly",
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name_en' => 'Premium Plan',
                'name_ar' => 'الخطة المميزة',
                'monthly_price' => 100,
                'yearly_price' => 1200,
                'features_en' => 'Feature A, Feature B, Feature C, Feature D',
                'features_ar' => 'Feature A, Feature B, Feature C, Feature D',
                'plan_image' => 'default_images/plan_images/premium.png',
                'verified_icon' => 'default_images/plan_images/premium.png',
                'plan_slug' => config('constant.plan_name.premium'),
                'ios_product_id' => "com.premium.monthly",
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
          foreach ($plans as $data) {
            // Create the Article
            $plan = Plan::create([
                'name_en'        => $data['name_en'],
                'name_ar'        => $data['name_ar'],
                'monthly_price'  => $data['monthly_price'],
                'yearly_price'   => $data['yearly_price'],
                'features_en'  => $data['features_en'],
                'features_ar'  => $data['features_ar'],
                'plan_slug'   => $data['plan_slug'],
                'ios_product_id' => $data['ios_product_id'],
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
 
            // Handle the image for this article
            $sourcePath = public_path($data['plan_image']);
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
               
                $plan->uploads()->save($upload);
            } else {
                $this->command->warn("Image file not found: " . $sourcePath);
            }

            $sourceVerifiedPath = public_path($data['verified_icon']);
            if (File::exists($sourceVerifiedPath)) {
                $extension = pathinfo($sourceVerifiedPath, PATHINFO_EXTENSION);
                $filename = basename($sourceVerifiedPath);
                $finalPath = "$folder/$filename";
                $destinationPath = public_path('storage/'.$finalPath);
 
                File::ensureDirectoryExists(dirname($destinationPath));
 
                // Copy the file
                File::copy($sourceVerifiedPath, $destinationPath);
 
                // Get file info
                $extension = pathinfo($sourceVerifiedPath, PATHINFO_EXTENSION);
 
                // Create an entry in the uploads table using the morph relation
                $upload = new Uploads();
 
                $upload->file_path          = $finalPath;
                $upload->extension          = $extension;
                $upload->original_file_name = $filename;
                $upload->type               = $varifiedType;
                $upload->file_type          = 'image';
                $upload->orientation        = null;
               
                $plan->uploads()->save($upload);
            } else {
                $this->command->warn("Image file not found: " . $sourceVerifiedPath);
            }
        }
    }
}