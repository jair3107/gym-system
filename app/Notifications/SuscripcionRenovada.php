<?php

namespace App\Notifications;

use App\Models\MembresiaCliente;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuscripcionRenovada extends Notification
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
            ->subject('Tu suscripción se renovó')
            ->greeting('Hola '.$notifiable->name)
            ->line("Tu suscripción «{$plan}» se renovó correctamente.")
            ->line("Se realizó un cargo de \${$precio}.")
            ->line("Tu nueva vigencia es hasta el {$fecha}. ¡Gracias por seguir con nosotros!")
            ->salutation('AURUM Fitness Club');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'tipo' => 'suscripcion_renovada',
            'titulo' => 'Tu suscripción se renovó',
            'cuerpo' => sprintf(
                'La suscripción «%s» se renovó. Vigencia hasta el %s.',
                $this->suscripcion->membresia?->nombre ?? 'tu membresía',
                $this->suscripcion->fecha_fin->format('d/m/Y'),
            ),
            'membresia_cliente_id' => $this->suscripcion->id,
            'icono' => 'ok',
        ];
    }
}
