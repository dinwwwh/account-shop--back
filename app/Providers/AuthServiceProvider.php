<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        'App\Models\Game' => 'App\Policies\GamePolicy',
        'App\Models\GameInfo' => 'App\Policies\GameInfoPolicy',
        'App\Models\AccountType' => 'App\Policies\AccountTypePolicy',
        'App\Models\AccountInfo' => 'App\Policies\AccountInfoPolicy',
        'App\Models\AccountAction' => 'App\Policies\AccountActionPolicy',
        'App\Models\Account' => 'App\Policies\AccountPolicy',
        'App\Models\AccountFee' => 'App\Policies\AccountFeePolicy',
        'App\Models\DiscountCode' => 'App\Policies\DiscountCodePolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            $url = config('app.front_end_url') . '/verify-email?'
                . http_build_query([
                    'url' => $url
                ]);

            return (new MailMessage)
                ->subject('Xác thực email')
                ->line('Vui lòng nhất vào nút phía dưới để xác thực email của bạn.')
                ->action('Xác Thực Email', $url)
                ->line('Nếu bạn không đăng ký tài khoản vui lòng bỏ qua email này.');
        });
    }
}
