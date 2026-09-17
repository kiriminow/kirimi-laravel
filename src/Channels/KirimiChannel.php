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

        $deviceId = $message->deviceId ?? $this->config['device_id'] ?? null;

        if ($message->nama !== null || $message->nomor !== null) {
            return $this->sendContact($message, $deviceId);
        }

        if ($message->isBroadcast || $message->numbers !== []) {
            return $this->sendBroadcast($message, $deviceId);
        }

        if (empty($message->to)) {
            throw new RuntimeException('Kirimi message requires a recipient phone number (call ->to(...))');
        }

        if ($message->isWaba) {
            return $this->sendWaba($message, $message->to);
        }

        if (!$deviceId) {
            throw new RuntimeException('Kirimi device_id required (set via ->device(...) on message or KIRIMI_DEVICE_ID in config)');
        }

        if ($message->isFast) {
            return $this->client->sendMessageFast(
                $deviceId,
                $message->to,
                $message->message ?? '',
                $message->mediaUrl,
                $message->options,
            );
        }

        return $this->client->sendMessage(
            $deviceId,
            $message->to,
            $message->message ?? '',
            $message->mediaUrl,
            $message->options,
        );
    }

    protected function sendWaba(KirimiMessage $message, string $receiver): array
    {
        $wabaId = $message->wabaId ?? $this->config['waba_id'] ?? null;

        if (!$wabaId) {
            throw new RuntimeException('Kirimi waba_id required for WABA messages (call ->wabaId(...) on message or set KIRIMI_WABA_ID in config)');
        }

        if (empty($message->templateName)) {
            throw new RuntimeException('Kirimi template_name required for WABA messages (call ->template(...) on message)');
        }

        $options = array_filter([
            'variables' => $message->variables !== [] ? $message->variables : null,
            'header'    => $message->header,
            'buttons'   => $message->buttons,
        ], static fn ($value) => $value !== null);

        return $this->client->sendWabaMessage($wabaId, $receiver, $message->templateName, $options);
    }

    protected function sendBroadcast(KirimiMessage $message, ?string $deviceId): array
    {
        if (!$deviceId) {
            throw new RuntimeException('Kirimi device_id required for broadcasts (set via ->device(...) on message or KIRIMI_DEVICE_ID in config)');
        }

        if (empty($message->label)) {
            throw new RuntimeException('Kirimi broadcast label required (call ->label(...) or ->broadcast($label, $numbers) on message)');
        }

        if ($message->numbers === []) {
            throw new RuntimeException('Kirimi broadcast numbers required (call ->numbers([...]) or ->broadcast($label, $numbers) on message)');
        }

        if (empty($message->message)) {
            throw new RuntimeException('Kirimi broadcast requires a message body (call ->message(...))');
        }

        $options = $message->mediaUrl !== null
            ? array_merge(['media_url' => $message->mediaUrl], $message->options)
            : $message->options;

        return $this->client->broadcastMessage(
            $deviceId,
            $message->label,
            $message->numbers,
            $message->message,
            $options,
        );
    }

    protected function sendContact(KirimiMessage $message, ?string $deviceId): array
    {
        if (empty($message->nama) || empty($message->nomor)) {
            throw new RuntimeException('Kirimi contact requires both ->nama(...) and ->nomor(...)');
        }

        return $this->client->saveContact($message->nama, $message->nomor, $deviceId);
    }
}
