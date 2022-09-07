<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@email.com',
            'password' => bcrypt('admin'),
            'role' => 'admin'
        ]);

        
        DB::table('users')->insert([
            'name' => 'cas',
            'email' => 'cas@email.com',
            'password' => bcrypt('cas'),
            'role' => 'cashier'
        ]);

        //reception cashier  opd1 opd2 opd3 opd4 Altrasound Laboratoriest Pharmacist
        DB::table('users')->insert([
            'name' => 'rec',
            'email' => 'rec@email.com',
            'password' => bcrypt('rec'),
            'role' => 'reception'
        ]);

        
        DB::table('users')->insert([
            'name' => 'opd1',
            'email' => 'opd1@email.com',
            'password' => bcrypt('opd1'),
            'role' => 'opd1'
        ]);

        
        DB::table('users')->insert([
            'name' => 'opd2',
            'email' => 'opd2@email.com',
            'password' => bcrypt('opd2'),
            'role' => 'opd2'
        ]);

        
        DB::table('users')->insert([
            'name' => 'opd3',
            'email' => 'opd3@email.com',
            'password' => bcrypt('opd3'),
            'role' => 'opd3'
        ]);

        
        DB::table('users')->insert([
            'name' => 'opd4',
            'email' => 'opd4@email.com',
            'password' => bcrypt('opd4'),
            'role' => 'opd4'
        ]);


        
        DB::table('users')->insert([
            'name' => 'alt',
            'email' => 'alt@email.com',
            'password' => bcrypt('alt'),
            'role' => 'Altrasound'
        ]);


        
        DB::table('users')->insert([
            'name' => 'lab',
            'email' => 'lab@email.com',
            'password' => bcrypt('lab'),
            'role' => 'Laboratoriest'
        ]);

        
        DB::table('users')->insert([
            'name' => 'pha',
            'email' => 'pha@email.com',
            'password' => bcrypt('pha'),
            'role' => 'Pharmacist'
        ]);

    }
}
