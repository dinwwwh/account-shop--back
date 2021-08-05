<?php

namespace Database\Seeders;

use App\Models\Config;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Config::firstOrCreate(
            ['key' => 'recharge-phonecard.manual_telcos'],
            [
                'data' => [
                    [
                        'key' => 'VIETTEL',
                        'name' => 'viettel',
                        'faceValues' => [
                            [
                                'value' => 10000,
                                'tax' => 20,
                                'taxForInvalidFaceValue' => 100,
                            ],
                            [
                                'value' => 20000,
                                'tax' => 30,
                                'taxForInvalidFaceValue' => 90,
                            ],
                            [
                                'value' => 50000,
                                'tax' => 40,
                                'taxForInvalidFaceValue' => 80,
                            ],
                            [
                                'value' => 100000,
                                'tax' => 50,
                                'taxForInvalidFaceValue' => 70,
                            ]
                        ]
                    ]
                ],
                'rules_of_data' => [
                    'array',
                    '*.key' => ['required', 'string', 'distinct'],
                    '*.name' => ['required', 'string'],
                    '*.faceValues' => ['required', 'array'],
                    '*.faceValues.*.value' => ['required', 'integer', 'min:0'],
                    '*.faceValues.*.tax' => ['required', 'integer', 'min:0', 'max:100'],
                    '*.faceValues.*.taxForInvalidFaceValue' => ['required', 'integer', 'min:0', 'max:100'],
                ],
                'structure_description' => '[...{"key": "VIETTEL", "name": "viettel", "faceValues": [...{"value": 10000, "tax": 10, "taxForInvalidFaceValue": 20}]}]',
                'description' => 'Chứa thông tin loại thẻ, mệnh giá, thuế và phí phạt khi sai mệnh giá của cổng nạp thẻ thủ công.',
                'public' => true,
            ]
        );

        Config::firstOrCreate(
            ['key' => 'recharge-phonecard.port_manual_enable'],
            [
                'data' => true,
                'rules_of_data' => [
                    'boolean'
                ],
                'structure_description' => 'is_enable:boolean',
                'description' => 'Quyết định liệu có sử dụng cổng nạp thẻ thủ công không [yes/no].',
                'public' => true,
            ]
        );
    }
}
