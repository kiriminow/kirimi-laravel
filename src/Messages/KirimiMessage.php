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
    public bool $isBroadcast = false;
    public array $options = [];

    // WABA
    public ?string $wabaId = null;
    public ?string $templateName = null;
    public array $variables = [];
    public ?array $header = null;
    public ?array $buttons = null;

    // Broadcast
    public ?string $label = null;
    public array $numbers = [];

    // Contacts
    public ?string $nama = null;
    public ?string $nomor = null;

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

    public function to(string $receiver): static
    {
        $this->to = $receiver;

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

    public function options(array $options): static
    {
        $this->options = $options;

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

    public function wabaId(string $wabaId): static
    {
        $this->wabaId = $wabaId;
        $this->isWaba = true;

        return $this;
    }

    public function template(string $templateName): static
    {
        $this->templateName = $templateName;
        $this->isWaba = true;

        return $this;
    }

    public function variables(array $variables): static
    {
        $this->variables = $variables;

        return $this;
    }

    public function header(array $header): static
    {
        $this->header = $header;

        return $this;
    }

    public function buttons(array $buttons): static
    {
        $this->buttons = $buttons;

        return $this;
    }

    public function broadcast(string $label, array $numbers): static
    {
        $this->isBroadcast = true;
        $this->label = $label;
        $this->numbers = array_values($numbers);

        return $this;
    }

    public function label(string $label): static
    {
        $this->label = $label;
        $this->isBroadcast = true;

        return $this;
    }

    public function numbers(array $numbers): static
    {
        $this->numbers = array_values($numbers);
        $this->isBroadcast = true;

        return $this;
    }

    public function nama(string $nama): static
    {
        $this->nama = $nama;

        return $this;
    }

    public function nomor(string $nomor): static
    {
        $this->nomor = $nomor;

        return $this;
    }
}
