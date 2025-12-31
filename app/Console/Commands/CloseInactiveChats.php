<?php

namespace App\Console\Commands;

use App\Models\Chat;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CloseInactiveChats extends Command
{
    protected $signature = 'chats:close-inactive';
    protected $description = 'Cierra chats inactivos por más de 6 días';

    public function handle()
    {
        $this->info('Buscando chats inactivos...');

        // Cerrar chats inactivos por más de 6 días
        $inactiveDays = 6;
        $inactiveDate = Carbon::now()->subDays($inactiveDays);

        $chatsInactivos = Chat::where('estado', 'activo')
            ->where(function($query) use ($inactiveDate) {
                $query->where('ultimo_mensaje_at', '<', $inactiveDate)
                      ->orWhere(function($q) use ($inactiveDate) {
                          $q->whereNull('ultimo_mensaje_at')
                            ->where('created_at', '<', $inactiveDate);
                      });
            })
            ->get();

        $count = 0;
        foreach ($chatsInactivos as $chat) {
            $chat->update([
                'estado' => 'cerrado',
                'cerrado_at' => now(),
                'cerrado_por' => 'sistema',
            ]);
            $count++;
        }

        $this->info("Se cerraron {$count} chats por inactividad.");

        // Cerrar chats que alcanzaron el límite de mensajes
        $chatsLimite = Chat::where('estado', 'activo')
            ->where('mensaje_count', '>=', 22)
            ->get();

        $countLimite = 0;
        foreach ($chatsLimite as $chat) {
            $chat->update([
                'estado' => 'cerrado',
                'cerrado_at' => now(),
                'cerrado_por' => 'sistema',
            ]);
            $countLimite++;
        }

        $this->info("Se cerraron {$countLimite} chats por límite de mensajes.");
        $this->info('Proceso completado.');

        return 0;
    }
}
