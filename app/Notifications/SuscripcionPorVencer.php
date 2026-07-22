<?php

namespace App\Notifications;

use App\Models\MembresiaCliente;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuscripcionPorVencer extends Notification
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
            ->subject('Tu suscripción está por vencer')
            ->greeting('Hola '.$notifiable->name)
            ->line("Tu suscripción «{$plan}» vencerá el {$fecha}.")
            ->line('Al vencer se cancelará y perderás el acceso y tus beneficios incluidos.')
            ->line('Acércate a recepción para renovarla y seguir entrenando.')
            ->salutation('AURUM Fitness Club');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'tipo' => 'suscripcion_por_vencer',
            'titulo' => 'Tu suscripción está por vencer',
            'cuerpo' => sprintf(
                'La suscripción «%s» vencerá el %s. Renuévala para no perder tus beneficios.',
                $this->suscripcion->membresia?->nombre ?? 'tu membresía',
                $this->suscripcion->fecha_fin->format('d/m/Y'),
            ),
            'membresia_cliente_id' => $this->suscripcion->id,
            'icono' => 'vencer',
        ];
    }
}
