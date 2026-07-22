<?php

namespace App\Notifications;

use App\Models\MembresiaCliente;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuscripcionFinalizada extends Notification
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

        return (new MailMessage)
            ->subject('Tu suscripción finalizó')
            ->greeting('Hola '.$notifiable->name)
            ->line("Tu suscripción «{$plan}» finalizó el {$fecha} y fue cancelada.")
            ->line('Tu cuenta quedó como inactiva. Puedes reactivarla contratando de nuevo en recepción.')
            ->line('¡Te esperamos de vuelta!')
            ->salutation('AURUM Fitness Club');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'tipo' => 'suscripcion_finalizada',
            'titulo' => 'Tu suscripción finalizó',
            'cuerpo' => sprintf(
                'La suscripción «%s» finalizó el %s y fue cancelada.',
                $this->suscripcion->membresia?->nombre ?? 'tu membresía',
                $this->suscripcion->fecha_fin->format('d/m/Y'),
            ),
            'membresia_cliente_id' => $this->suscripcion->id,
            'icono' => 'fin',
        ];
    }
}
