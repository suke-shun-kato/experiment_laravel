<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // UserFactory を使ってデータを作成。create()の引数の値を上書きする。
        User::factory()->create([
            'name' => 'Kato Shunsuke2',
            'email' => 'suke.shun.kato2@gmail.com'
        ]);
        User::factory()->create([
            'name' => 'Kato Shunsuke',
            'email' => 'suke.shun.kato@gmail.com'
        ]);
        // UserFactory を使って3レコードデータを作成する
        User::factory()->count(3)->create();
        // E-mail認証がまだのレコードデータを2レコード作成する
        User::factory()->count(2)->unverified()->create();
    }
}
