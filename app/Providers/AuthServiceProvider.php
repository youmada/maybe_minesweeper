<?php

namespace App\Providers;

use App\Domain\Room\RoomAggregate;
use App\Policies\RoomStatePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // ここでポリシーをマッピングする
        RoomAggregate::class => RoomStatePolicy::class,
    ];

    public function register(): void {}

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
