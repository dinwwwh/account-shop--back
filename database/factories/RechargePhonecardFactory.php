<?php

namespace Database\Factories;

use App\Models\RechargePhonecard;
use App\Models\Setting;
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
        $telcos = Setting::getValidatedOrFail('recharge_phonecard_manual_telcos');
        $telco = Arr::random(array_keys($telcos));
        $faceValue = Arr::random(array_keys($telcos[$telco]));
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
