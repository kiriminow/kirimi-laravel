<?php

namespace Kirimi\Laravel\Tests;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\Notification;
use Kirimi\KirimiClient;
use Kirimi\Laravel\Channels\KirimiChannel;
use Kirimi\Laravel\Facades\Kirimi;
use Kirimi\Laravel\KirimiServiceProvider;
use Kirimi\Laravel\Messages\KirimiMessage;
use Mockery;
use Orchestra\Testbench\TestCase;
use RuntimeException;

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
        $app['config']->set('kirimi.waba_id', 'test_waba');
        $app['config']->set('kirimi.base_url', 'https://api.kirimi.id');
        $app['config']->set('kirimi.timeout', 30);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Kirimi::clearResolvedInstances();

        parent::tearDown();
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

    public function test_facade_resolves_and_shares_the_singleton(): void
    {
        $this->assertInstanceOf(KirimiClient::class, Kirimi::getFacadeRoot());
        $this->assertSame($this->app->make(KirimiClient::class), Kirimi::getFacadeRoot());
        $this->assertSame($this->app->make('kirimi'), $this->app->make(KirimiClient::class));
    }

    public function test_config_is_loaded(): void
    {
        $this->assertSame('test_user', config('kirimi.user_code'));
        $this->assertSame('test_secret', config('kirimi.secret'));
        $this->assertSame('test_device', config('kirimi.device_id'));
        $this->assertSame('test_waba', config('kirimi.waba_id'));
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
        $channel = $this->app->make(ChannelManager::class)->channel('kirimi');

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

    public function test_kirimi_message_waba_fluent_chain(): void
    {
        $msg = KirimiMessage::create()
            ->to('628123456789')
            ->waba()
            ->wabaId('waba_001')
            ->template('order_update')
            ->variables(['A1', 'B2'])
            ->header(['type' => 'text', 'text' => 'Hi'])
            ->buttons([['type' => 'quick_reply']]);

        $this->assertSame('waba_001', $msg->wabaId);
        $this->assertSame('order_update', $msg->templateName);
        $this->assertSame(['A1', 'B2'], $msg->variables);
        $this->assertSame(['type' => 'text', 'text' => 'Hi'], $msg->header);
        $this->assertSame([['type' => 'quick_reply']], $msg->buttons);
        $this->assertTrue($msg->isWaba);
    }

    public function test_kirimi_message_broadcast_and_contact_fluent_chain(): void
    {
        $broadcast = KirimiMessage::create('Promo')->broadcast('promo-label', ['6281', '6282']);
        $this->assertTrue($broadcast->isBroadcast);
        $this->assertSame('promo-label', $broadcast->label);
        $this->assertSame(['6281', '6282'], $broadcast->numbers);

        $contact = KirimiMessage::create()->nama('Budi')->nomor('628123456789');
        $this->assertSame('Budi', $contact->nama);
        $this->assertSame('628123456789', $contact->nomor);
    }

    public function test_kirimi_message_setters_are_fluent(): void
    {
        $msg = new KirimiMessage();
        $numbers = ['6281'];
        $variables = ['A1'];

        $this->assertSame($msg, $msg->to('628111'));
        $this->assertSame($msg, $msg->device('d1'));
        $this->assertSame($msg, $msg->message('hi'));
        $this->assertSame($msg, $msg->mediaUrl('http://x.com/a.jpg'));
        $this->assertSame($msg, $msg->options(['fileName' => 'a.jpg']));
        $this->assertSame($msg, $msg->quick());
        $this->assertSame($msg, $msg->waba());
        $this->assertSame($msg, $msg->wabaId('w1'));
        $this->assertSame($msg, $msg->template('t1'));
        $this->assertSame($msg, $msg->variables($variables));
        $this->assertSame($msg, $msg->header(['type' => 'text']));
        $this->assertSame($msg, $msg->buttons([]));
        $this->assertSame($msg, $msg->label('l1'));
        $this->assertSame($msg, $msg->numbers($numbers));
        $this->assertSame($msg, $msg->nama('Budi'));
        $this->assertSame($msg, $msg->nomor('6281'));
        $this->assertSame($msg, $msg->broadcast('l2', $numbers));
    }

    // Facade delegation

    public function test_facade_delegates_new_waba_method(): void
    {
        $client = Mockery::mock(KirimiClient::class);
        $client->shouldReceive('sendWabaMessage')
            ->once()
            ->with('waba_001', '628123456789', 'order_update', ['variables' => ['A1']])
            ->andReturn(['message_id' => 'msg_1']);

        Kirimi::swap($client);

        $this->assertSame(
            ['message_id' => 'msg_1'],
            Kirimi::sendWabaMessage('waba_001', '628123456789', 'order_update', ['variables' => ['A1']]),
        );
    }

    public function test_facade_delegates_new_deposit_method(): void
    {
        $client = Mockery::mock(KirimiClient::class);
        $client->shouldReceive('createDeposit')->once()->with(50000)->andReturn(['ref' => 'dep_1']);

        Kirimi::swap($client);

        $this->assertSame(['ref' => 'dep_1'], Kirimi::createDeposit(50000));
    }

    // Notification channel routing

    public function test_channel_routes_default_message_to_send_message(): void
    {
        $client = Mockery::mock(KirimiClient::class);
        $client->shouldReceive('sendMessage')
            ->once()
            ->with('test_device', '628123456789', 'Hello', null, [])
            ->andReturn(['ok' => true]);

        $channel = new KirimiChannel($client, config('kirimi'));

        $result = $channel->send(new \stdClass(), $this->notificationReturning(
            KirimiMessage::create('Hello')->to('628123456789'),
        ));

        $this->assertSame(['ok' => true], $result);
    }

    public function test_channel_routes_fast_message_to_send_message_fast_with_receiver(): void
    {
        $client = Mockery::mock(KirimiClient::class);
        $client->shouldReceive('sendMessageFast')
            ->once()
            ->with('test_device', '628123456789', 'Quick hello', null, [])
            ->andReturn(['ok' => true]);

        $channel = new KirimiChannel($client, config('kirimi'));

        $result = $channel->send(new \stdClass(), $this->notificationReturning(
            KirimiMessage::create('Quick hello')->to('628123456789')->quick(),
        ));

        $this->assertSame(['ok' => true], $result);
    }

    public function test_channel_builds_canonical_waba_payload(): void
    {
        $client = Mockery::mock(KirimiClient::class);
        $client->shouldReceive('sendWabaMessage')
            ->once()
            ->with('waba_001', '628123456789', 'order_update', [
                'variables' => ['A1', 'B2'],
                'header'    => ['type' => 'text', 'text' => 'Hi'],
            ])
            ->andReturn(['accepted' => true]);

        $channel = new KirimiChannel($client, config('kirimi'));

        $message = KirimiMessage::create()
            ->to('628123456789')
            ->wabaId('waba_001')
            ->template('order_update')
            ->variables(['A1', 'B2'])
            ->header(['type' => 'text', 'text' => 'Hi']);

        $result = $channel->send(new \stdClass(), $this->notificationReturning($message));

        $this->assertSame(['accepted' => true], $result);
    }

    public function test_channel_falls_back_to_config_waba_id(): void
    {
        $client = Mockery::mock(KirimiClient::class);
        $client->shouldReceive('sendWabaMessage')
            ->once()
            ->with('test_waba', '628123456789', 'order_update', [])
            ->andReturn(['accepted' => true]);

        $channel = new KirimiChannel($client, config('kirimi'));

        $result = $channel->send(new \stdClass(), $this->notificationReturning(
            KirimiMessage::create()->to('628123456789')->template('order_update'),
        ));

        $this->assertSame(['accepted' => true], $result);
    }

    public function test_channel_routes_broadcast_with_array_numbers(): void
    {
        $client = Mockery::mock(KirimiClient::class);
        $client->shouldReceive('broadcastMessage')
            ->once()
            ->with('test_device', 'promo', ['6281', '6282'], 'Promo time', [])
            ->andReturn(['queued' => 2]);

        $channel = new KirimiChannel($client, config('kirimi'));

        $result = $channel->send(new \stdClass(), $this->notificationReturning(
            KirimiMessage::create('Promo time')->broadcast('promo', ['6281', '6282']),
        ));

        $this->assertSame(['queued' => 2], $result);
    }

    public function test_channel_routes_contact_to_save_contact(): void
    {
        $client = Mockery::mock(KirimiClient::class);
        $client->shouldReceive('saveContact')
            ->once()
            ->with('Budi', '628123456789', 'test_device')
            ->andReturn(['saved' => true]);

        $channel = new KirimiChannel($client, config('kirimi'));

        $result = $channel->send(new \stdClass(), $this->notificationReturning(
            KirimiMessage::create()->nama('Budi')->nomor('628123456789'),
        ));

        $this->assertSame(['saved' => true], $result);
    }

    public function test_channel_resolves_client_from_container(): void
    {
        $client = Mockery::mock(KirimiClient::class);
        $client->shouldReceive('sendMessage')
            ->once()
            ->with('test_device', '628123456789', 'Hello', null, [])
            ->andReturn(['ok' => true]);

        $this->app->instance(KirimiClient::class, $client);

        $channel = $this->app->make(ChannelManager::class)->channel('kirimi');

        $result = $channel->send(new \stdClass(), $this->notificationReturning(
            KirimiMessage::create('Hello')->to('628123456789'),
        ));

        $this->assertSame(['ok' => true], $result);
    }

    // Channel guards

    public function test_channel_throws_when_receiver_missing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('recipient');

        $channel = new KirimiChannel(Mockery::mock(KirimiClient::class), config('kirimi'));

        $channel->send(new \stdClass(), $this->notificationReturning(
            KirimiMessage::create('Hello'),
        ));
    }

    public function test_channel_throws_when_device_missing_for_plain_message(): void
    {
        config(['kirimi.device_id' => null]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('device_id');

        $channel = new KirimiChannel(Mockery::mock(KirimiClient::class), config('kirimi'));

        $channel->send(new \stdClass(), $this->notificationReturning(
            KirimiMessage::create('Hello')->to('628123456789'),
        ));
    }

    public function test_channel_throws_when_waba_id_missing(): void
    {
        config(['kirimi.waba_id' => null]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('waba_id');

        $channel = new KirimiChannel(Mockery::mock(KirimiClient::class), config('kirimi'));

        $channel->send(new \stdClass(), $this->notificationReturning(
            KirimiMessage::create()->to('628123456789')->waba()->template('order_update'),
        ));
    }

    public function test_channel_throws_when_waba_template_missing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('template_name');

        $channel = new KirimiChannel(Mockery::mock(KirimiClient::class), config('kirimi'));

        $channel->send(new \stdClass(), $this->notificationReturning(
            KirimiMessage::create()->to('628123456789')->waba()->wabaId('waba_001'),
        ));
    }

    public function test_channel_throws_when_broadcast_label_missing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('label');

        $channel = new KirimiChannel(Mockery::mock(KirimiClient::class), config('kirimi'));

        $channel->send(new \stdClass(), $this->notificationReturning(
            KirimiMessage::create('Promo')->numbers(['6281']),
        ));
    }

    public function test_channel_throws_when_contact_incomplete(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('nama');

        $channel = new KirimiChannel(Mockery::mock(KirimiClient::class), config('kirimi'));

        $channel->send(new \stdClass(), $this->notificationReturning(
            KirimiMessage::create()->nama('Budi'),
        ));
    }

    public function test_channel_throws_when_notification_missing_to_kirimi(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('toKirimi');

        $channel = new KirimiChannel(Mockery::mock(KirimiClient::class), config('kirimi'));

        $channel->send(new \stdClass(), new class extends Notification {});
    }

    private function notificationReturning(KirimiMessage $message): Notification
    {
        return new class($message) extends Notification {
            public function __construct(private KirimiMessage $message) {}

            public function toKirimi(mixed $notifiable): KirimiMessage
            {
                return $this->message;
            }
        };
    }
}
