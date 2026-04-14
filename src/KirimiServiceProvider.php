<?php

namespace Kirimi\Laravel;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Kirimi\KirimiClient;
use Kirimi\Laravel\Channels\KirimiChannel;
use Kirimi\Laravel\Console\SendCommand;

class KirimiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/kirimi.php', 'kirimi');

        $this->app->singleton(KirimiClient::class, function ($app) {
            $config = $app['config']['kirimi'];

            return new KirimiClient(
                $config['user_code'] ?? '',
                $config['secret'] ?? '',
                $config['base_url'] ?? 'https://api.kirimi.id',
            );
        });

        $this->app->alias(KirimiClient::class, 'kirimi');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/kirimi.php' => config_path('kirimi.php'),
            ], 'kirimi-config');

            $this->commands([
                SendCommand::class,
            ]);
        }

        $this->app->make(ChannelManager::class)->extend('kirimi', function ($app) {
            return new KirimiChannel(
                $app->make(KirimiClient::class),
                $app['config']['kirimi'],
            );
        });
    }
}
