<?php

namespace Kirimi\Laravel\Channels;

use Illuminate\Notifications\Notification;
use Kirimi\KirimiClient;
use Kirimi\Laravel\Messages\KirimiMessage;
use RuntimeException;

class KirimiChannel
{
    public function __construct(
        protected KirimiClient $client,
        protected array $config,
    ) {}

    public function send(mixed $notifiable, Notification $notification): mixed
    {
        if (!method_exists($notification, 'toKirimi')) {
            throw new RuntimeException('Notification must define toKirimi() method');
        }

        $message = $notification->toKirimi($notifiable);

        if (!$message instanceof KirimiMessage) {
            throw new RuntimeException('toKirimi() must return KirimiMessage instance');
        }

        if (empty($message->to)) {
            throw new RuntimeException('Kirimi message requires a recipient phone number (call ->to(...))');
        }

        $deviceId = $message->deviceId ?? $this->config['device_id'] ?? null;

        if (!$deviceId) {
            throw new RuntimeException('Kirimi device_id required (set via ->device(...) on message or KIRIMI_DEVICE_ID in config)');
        }

        if ($message->isWaba) {
            return $this->client->sendWabaMessage($deviceId, $message->to, $message->message ?? '');
        }

        if ($message->isFast) {
            return $this->client->sendMessageFast($deviceId, $message->to, $message->message ?? '', $message->mediaUrl);
        }

        return $this->client->sendMessage($deviceId, $message->to, $message->message ?? '', $message->mediaUrl);
    }
}
