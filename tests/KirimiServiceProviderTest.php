<?php

namespace Kirimi\Laravel\Tests;

use Kirimi\KirimiClient;
use Kirimi\Laravel\Channels\KirimiChannel;
use Kirimi\Laravel\Facades\Kirimi;
use Kirimi\Laravel\KirimiServiceProvider;
use Kirimi\Laravel\Messages\KirimiMessage;
use Orchestra\Testbench\TestCase;

class KirimiServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [KirimiServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Kirimi' => Kirimi::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('kirimi.user_code', 'test_user');
        $app['config']->set('kirimi.secret', 'test_secret');
        $app['config']->set('kirimi.device_id', 'test_device');
        $app['config']->set('kirimi.base_url', 'https://api.kirimi.id');
        $app['config']->set('kirimi.timeout', 30);
    }

    public function test_service_provider_registers_client(): void
    {
        $client = $this->app->make(KirimiClient::class);

        $this->assertInstanceOf(KirimiClient::class, $client);
    }

    public function test_service_provider_registers_kirimi_alias(): void
    {
        $client = $this->app->make('kirimi');

        $this->assertInstanceOf(KirimiClient::class, $client);
    }

    public function test_singleton_returns_same_instance(): void
    {
        $a = $this->app->make(KirimiClient::class);
        $b = $this->app->make(KirimiClient::class);

        $this->assertSame($a, $b);
    }

    public function test_facade_resolves(): void
    {
        $this->assertInstanceOf(KirimiClient::class, Kirimi::getFacadeRoot());
    }

    public function test_config_is_loaded(): void
    {
        $this->assertSame('test_user', config('kirimi.user_code'));
        $this->assertSame('test_secret', config('kirimi.secret'));
        $this->assertSame('test_device', config('kirimi.device_id'));
        $this->assertSame('https://api.kirimi.id', config('kirimi.base_url'));
        $this->assertSame(30, config('kirimi.timeout'));
    }

    public function test_config_is_publishable(): void
    {
        $this->artisan('vendor:publish', ['--tag' => 'kirimi-config', '--force' => true])
            ->assertExitCode(0);
    }

    public function test_kirimi_channel_is_registered(): void
    {
        $channel = $this->app->make(\Illuminate\Notifications\ChannelManager::class)->channel('kirimi');

        $this->assertInstanceOf(KirimiChannel::class, $channel);
    }

    // KirimiMessage fluent builder tests

    public function test_kirimi_message_create(): void
    {
        $msg = KirimiMessage::create('Hello');

        $this->assertSame('Hello', $msg->message);
        $this->assertFalse($msg->isFast);
        $this->assertFalse($msg->isWaba);
    }

    public function test_kirimi_message_fluent_chain(): void
    {
        $msg = KirimiMessage::create()
            ->to('628123456789')
            ->device('dev001')
            ->message('Test message')
            ->mediaUrl('https://example.com/img.jpg')
            ->quick();

        $this->assertSame('628123456789', $msg->to);
        $this->assertSame('dev001', $msg->deviceId);
        $this->assertSame('Test message', $msg->message);
        $this->assertSame('https://example.com/img.jpg', $msg->mediaUrl);
        $this->assertTrue($msg->isFast);
        $this->assertFalse($msg->isWaba);
    }

    public function test_kirimi_message_waba_flag(): void
    {
        $msg = KirimiMessage::create('Waba message')->waba();

        $this->assertTrue($msg->isWaba);
        $this->assertFalse($msg->isFast);
    }

    public function test_kirimi_message_returns_static_for_chaining(): void
    {
        $msg = new KirimiMessage();

        $this->assertSame($msg, $msg->to('628111'));
        $this->assertSame($msg, $msg->device('d1'));
        $this->assertSame($msg, $msg->message('hi'));
        $this->assertSame($msg, $msg->mediaUrl('http://x.com/a.jpg'));
        $this->assertSame($msg, $msg->quick());
        $this->assertSame($msg, $msg->waba());
    }
}
