<?php

namespace App\Application\Providers;

use App\Domain\Endereco\Models\Endereco;
use App\Domain\Endereco\Models\EnderecoObserver;
use App\Domain\Usuario\Models\User;
use App\Domain\Usuario\Models\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObserver::class);
        Endereco::observe(EnderecoObserver::class);
    }
}
