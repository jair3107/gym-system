<?php

namespace App\Notifications;

use App\Models\MembresiaCliente;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuscripcionPorRenovar extends Notification
{
    use Queueable;

    public function __construct(public MembresiaCliente $suscripcion)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $plan = $this->suscripcion->membresia?->nombre ?? 'tu membresía';
        $fecha = $this->suscripcion->fecha_fin->format('d/m/Y');
        $precio = number_format((float) ($this->suscripcion->membresia?->precio ?? 0), 2);

        return (new MailMessage)
            ->subject('Tu suscripción se renovará pronto')
            ->greeting('Hola '.$notifiable->name)
            ->line("Tu suscripción «{$plan}» se renovará automáticamente el {$fecha}.")
            ->line("Se realizará un cargo de \${$precio}.")
            ->line('Si no deseas renovar, avísanos en recepción antes de esa fecha.')
            ->salutation('AURUM Fitness Club');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'tipo' => 'suscripcion_por_renovar',
            'titulo' => 'Tu suscripción se renovará pronto',
            'cuerpo' => sprintf(
                'La suscripción «%s» se renovará automáticamente el %s.',
                $this->suscripcion->membresia?->nombre ?? 'tu membresía',
                $this->suscripcion->fecha_fin->format('d/m/Y'),
            ),
            'membresia_cliente_id' => $this->suscripcion->id,
            'icono' => 'renovar',
        ];
    }
}
