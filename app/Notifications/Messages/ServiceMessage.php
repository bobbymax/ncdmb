<?php

namespace App\Notifications\Messages;

interface ServiceMessage
{
    public function title(): string;   // subject/title
    public function summary(): string; // short paragraph
    public function toArray(): array;  // payload for DB + Blade
}
