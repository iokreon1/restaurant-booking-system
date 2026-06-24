<?php

namespace App\Notifications\Messages;

class KirimiMessage
{
    public string $content = '';

    public ?string $mediaUrl = null;

    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function mediaUrl(string $url): self
    {
        $this->mediaUrl = $url;

        return $this;
    }
}
