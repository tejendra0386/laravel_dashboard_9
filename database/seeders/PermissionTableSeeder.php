<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Permission::insert([
            [
                "column_name"=>"user",
                "name"=>"View User",
                "slug"=>Str::slug("View User"),
            ],
            [
                "column_name"=>"user",
                "name"=>"Create User",
                "slug"=>Str::slug("Create User"),
            ],
            [
                "column_name"=>"user",
                "name"=>"Edit User",
                "slug"=>Str::slug("Edit User"),
            ],
            [
                "column_name"=>"user",
                "name"=>"Remove User",
                "slug"=>Str::slug("Remove User"),
            ],
            [
                "column_name"=>"role",
                "name"=>"View Role",
                "slug"=>Str::slug("View Role"),
            ],
            [
                "column_name"=>"role",
                "name"=>"Create Role",
                "slug"=>Str::slug("Create Role"),
            ],
            [
                "column_name"=>"role",
                "name"=>"Edit Role",
                "slug"=>Str::slug("Edit Role"),
            ],
            [
                "column_name"=>"role",
                "name"=>"Remove Role",
                "slug"=>Str::slug("Remove Role"),
            ],
            [
                "column_name"=>"permission",
                "name"=>"View Permission",
                "slug"=>Str::slug("View Permission"),
            ],
            [
                "column_name"=>"permission",
                "name"=>"Create Permission",
                "slug"=>Str::slug("Create Permission"),
            ],
            [
                "column_name"=>"permission",
                "name"=>"Edit Permission",
                "slug"=>Str::slug("Edit Permission"),
            ],
            [
                "column_name"=>"permission",
                "name"=>"Remove Permission",
                "slug"=>Str::slug("Remove Permission"),
            ],
        ]);
    }
}
