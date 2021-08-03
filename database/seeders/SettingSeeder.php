<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::firstOrCreate(
            ['key' => 'recharge_phonecard_manual_telcos'],
            [
                'data' => [
                    'VIETTEL' => [
                        10000 => 20,
                        20000 => 30,
                        50000 => 40,
                    ]
                ],
                'rules_of_data' => [
                    'rootRules' => ['array', 'keys:string,min:1,max:50'],
                    '*' => ['array', 'keys:integer,min:1'],
                    '*.*' => ['integer', 'min:0', 'max:100'],
                ],
                'structure_description' => '{"telco": {"face_value": "percent_discount", ...}, ...}',
                'description' => 'Chứa thông tin loại thẻ, mệnh giá và chiết khấu của cổng nạp tiền thẻ thủ công.',
                'public' => true,
            ]
        );
    }
}
