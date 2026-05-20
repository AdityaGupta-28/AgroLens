<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WaterShortageAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $regionName,
        public float $groundwaterLevel
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Water Shortage Alert — '.$this->regionName)
            ->line("Groundwater level has dropped to {$this->groundwaterLevel}m in {$this->regionName}.")
            ->line('SMS integration placeholder — configure AGROLENS_SMS_DRIVER in production.')
            ->action('View Dashboard', url('/dashboard'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'water_shortage',
            'region' => $this->regionName,
            'groundwater_level' => $this->groundwaterLevel,
            'message' => "Low groundwater alert for {$this->regionName}",
        ];
    }
}
