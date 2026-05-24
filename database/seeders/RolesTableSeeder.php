<?php

namespace Database\Seeders;

use App\Domains\Core\Role\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            [
                'id'         => 1,
                'name_en'      => 'Super Admin',
                'name_ar'      => 'المشرف الفائق',
                'role_type'         => 'super_admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id'         => 2,
                'name_en'      => 'Master',
                'name_ar'      => 'يتقن',
                'role_type'         => 'app',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id'         => 3,
                'name_en'      => 'Learner',
                'name_ar'      => 'المتعلم',
                'role_type'         => 'app',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        ];
         foreach($roles as $key=>$role){
            $createdRole =  Role::create($role);
        }
    }
}
