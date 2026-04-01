<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('users')->delete();

        \DB::table('users')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                // 'mobile' => '1234567890',
                'password' => Hash::make('admin'),
                'is_admin' => true,
                'email_verified_at' => '2024-05-14 13:49:45',
                'remember_token' => NULL,
                'created_at' => '2024-05-14 13:49:45',
                'updated_at' => '2024-05-14 15:13:33',
            ),
        ));


    }
}
