<?php

namespace App\Interfaces;

interface NotificationTemplateServiceInterface
{
    public function getChannelsForRecipientType(string $type): array;
    public function getPriorityForRecipientType(string $type): string;
    public function getQueueForRecipientType(string $type): string;
}
