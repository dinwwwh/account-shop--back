<?php

namespace App\Console\Commands;

use App\Models\RechargePhonecard;
use Http;
use Illuminate\Console\Command;

class UpdateStatusCardsThesieure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thesieure:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status cards in app from thesieure';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $rechargePhonecards = RechargePhonecard::where('port', config('recharge-phonecard.ports.thesieure'))
            ->where('status', config('recharge-phonecard.statuses.approving'))
            ->get();

        foreach ($rechargePhonecards as $rechargePhonecard) {
            $this->updateStatusCard($rechargePhonecard);
        }

        $this->info('Updated status of ' . $rechargePhonecards->count() . ' phonecards to thesieure.');
    }

    public function updateStatusCard(RechargePhonecard $rechargePhonecard)
    {
        $sign = md5(
            config('recharge-phonecard.tsr_parent_key')
                . $rechargePhonecard->code
                . $rechargePhonecard->serial
        );

        $res = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post(
            'http://thesieure.com/chargingws/v2',
            [
                'telco' => $rechargePhonecard->telco,
                'code' => $rechargePhonecard->code,
                'serial' => $rechargePhonecard->serial,
                'amount' => $rechargePhonecard->face_value,
                'request_id' => $rechargePhonecard->getKey(),
                'parent_id' => config('recharge-phonecard.tsr_parent_id'),
                'sign' => $sign,
                'command' => 'check',
            ]
        )->json();

        switch ($res['status']) {
            case 1:
                $rechargePhonecard->update([
                    'status' => config('recharge-phonecard.statuses.success'),
                    'real_face_value' => $res['value'],
                    'received_value' => $res['amount'],
                    'data' => $res,
                ]);
                break;
            case 2:
                $rechargePhonecard->update([
                    'status' => config('recharge-phonecard.statuses.invalid_face_value'),
                    'real_face_value' => $res['value'],
                    'received_value' => $res['amount'],
                    'data' => $res,
                ]);
                break;
            case 99:
                $rechargePhonecard->update([
                    'status' => config('recharge-phonecard.statuses.approving'),
                    'data' => $res,
                ]);
                break;
            default:
                $rechargePhonecard->update([
                    'status' => config('recharge-phonecard.statuses.error'),
                    'real_face_value' => 0,
                    'received_value' => 0,
                    'data' => $res,
                ]);
                break;
        }
    }
}
