<?php

namespace Kirimi\Laravel\Console;

use Illuminate\Console\Command;
use Kirimi\KirimiClient;
use Kirimi\KirimiException;

class SendCommand extends Command
{
    protected $signature = 'kirimi:send
        {phone : Recipient phone number (with country code, e.g. 628123456789)}
        {message : Message content}
        {--device= : Device ID (overrides config default)}
        {--media= : Optional media URL}
        {--fast : Use fast send endpoint (no typing effect)}';

    protected $description = 'Send a WhatsApp message via Kirimi';

    public function handle(KirimiClient $client): int
    {
        $phone = $this->argument('phone');
        $message = $this->argument('message');
        $deviceId = $this->option('device') ?: config('kirimi.device_id');
        $mediaUrl = $this->option('media') ?: null;
        $fast = $this->option('fast');

        if (!$deviceId) {
            $this->error('Device ID is required. Use --device=<id> or set KIRIMI_DEVICE_ID in your .env file.');

            return self::FAILURE;
        }

        try {
            if ($fast) {
                $result = $client->sendMessageFast($deviceId, $phone, $message, $mediaUrl);
            } else {
                $result = $client->sendMessage($deviceId, $phone, $message, $mediaUrl);
            }

            $this->info('Message sent successfully!');

            if (!empty($result)) {
                $this->table(['Key', 'Value'], collect($result)->map(fn ($v, $k) => [$k, is_array($v) ? json_encode($v) : $v])->values()->toArray());
            }

            return self::SUCCESS;
        } catch (KirimiException $e) {
            $this->error('Failed to send message: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
