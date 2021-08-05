<?php

namespace Database\Factories;

use App\Models\RechargePhonecard;
use App\Models\User;
use Arr;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class RechargePhonecardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RechargePhonecard::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $telcos = config('recharge-phonecard.manual_telcos', []);
        $randomTelco = Arr::random($telcos);
        $telco = $randomTelco['key'];
        $faceValue = Arr::random(array_map(fn ($fv) => $fv['value'], $randomTelco['faceValues']));
        $status = Arr::random(config('recharge-phonecard.statuses'));
        $port = Arr::random(config('recharge-phonecard.ports'));

        $approverId = null;
        if ($status === config('recharge-phonecard.statuses.approving')) {
            $approverId = User::inRandomOrder()->first()->getKey();
        }

        return [
            'telco' => $telco,
            'face_value' => $faceValue,
            'status' => $status,
            'port' => $port,
            'serial' => Str::random(),
            'code' => Str::random(),
            'approver_id' => $approverId,
        ];
    }
}
