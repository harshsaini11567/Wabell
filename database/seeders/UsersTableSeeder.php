<?php

namespace Database\Seeders;

use App\Domains\Core\User\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name'        => 'Super Admin',
                'email'          => 'superadmin@gmail.com',
                'phone'          => '1523647890',
                'password'       => bcrypt('Superadmin@1234'),
                'remember_token' => null,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'language'          => 'ar',
            ]
        ];
        foreach($users as $key=>$user){
            $createdUser =  User::create($user);
        }
    }
}
