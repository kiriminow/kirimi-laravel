<?php

namespace Kirimi\Laravel\Messages;

class KirimiMessage
{
    public ?string $to = null;
    public ?string $deviceId = null;
    public ?string $message = null;
    public ?string $mediaUrl = null;
    public bool $isFast = false;
    public bool $isWaba = false;

    public function __construct(?string $message = null)
    {
        if ($message !== null) {
            $this->message = $message;
        }
    }

    public static function create(?string $message = null): static
    {
        return new static($message);
    }

    public function to(string $phone): static
    {
        $this->to = $phone;

        return $this;
    }

    public function device(string $deviceId): static
    {
        $this->deviceId = $deviceId;

        return $this;
    }

    public function message(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function mediaUrl(string $url): static
    {
        $this->mediaUrl = $url;

        return $this;
    }

    public function quick(): static
    {
        $this->isFast = true;

        return $this;
    }

    public function waba(): static
    {
        $this->isWaba = true;

        return $this;
    }
}
