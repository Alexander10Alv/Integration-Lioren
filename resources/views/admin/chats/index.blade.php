<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Solicitudes y Chats') }}
        </h2>
    </x-slot>

    <style>
        .chat-card { background: white; border-radius: 1rem; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s; cursor: pointer; border-left: 4px solid #FFC107; }
        .chat-card:hover { box-shadow: 0 8px 16px rgba(0,0,0,0.15); transform: translateX(4px); }
        .chat-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .badge-activo { background: #d1fae5; color: #065f46; }
        .badge-cerrado { background: #fee2e2; color: #991b1b; }
        .cliente-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #FFC107 0%, #FFB300 100%); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #1a1a1a; }
    </style>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div>
                    <h1 style="font-size: 2rem; font-weight: 800; color: #111827; margin-bottom: 0.5rem;">
                        Solicitudes de Clientes
                    </h1>
                    <p style="color: #6b7280;">Gestiona las consultas y solicitudes de planes</p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 2rem; font-weight: 800; color: #FFC107;">{{ $chats->count() }}</div>
                    <div style="font-size: 0.875rem; color: #6b7280;">Chats activos</div>
                </div>
            </div>

            @if($chats->isEmpty())
                <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 1rem;">
                    <i class="fas fa-inbox" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #374151; margin-bottom: 0.5rem;">
                        No hay solicitudes pendientes
                    </h3>
                    <p style="color: #6b7280;">
                        Las nuevas consultas de clientes aparecerán aquí
                    </p>
                </div>
            @else
                @foreach($chats as $chat)
                    <div class="chat-card" onclick="window.location.href='{{ route('chats.show', $chat) }}'">
                        <div style="display: flex; gap: 1rem;">
                            <div class="cliente-avatar">
                                {{ strtoupper(substr($chat->cliente->name, 0, 1)) }}
                            </div>
                            
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                                    <div>
                                        <h3 style="font-size: 1.125rem; font-weight: 700; color: #111827; margin-bottom: 0.25rem;">
                                            {{ $chat->cliente->name }}
                                        </h3>
                                        <div style="font-size: 0.875rem; color: #6b7280;">
                                            {{ $chat->cliente->email }}
                                        </div>
                                    </div>
                                    <span class="chat-badge badge-{{ $chat->estado }}">
                                        {{ $chat->estado === 'activo' ? 'Activo' : 'Cerrado' }}
                                    </span>
                                </div>

                                <div style="padding: 0.75rem; background: #f9fafb; border-radius: 0.5rem; margin-bottom: 0.75rem;">
                                    <div style="font-size: 0.875rem; font-weight: 600; color: #111827; margin-bottom: 0.25rem;">
                                        <i class="fas fa-clipboard-list"></i> {{ $chat->contexto }}
                                    </div>
                                </div>

                                @if($chat->ultimoMensaje)
                                    <div style="padding: 0.75rem; background: #fffbeb; border-radius: 0.5rem; border-left: 4px solid #FFC107;">
                                        <div style="font-size: 0.75rem; font-weight: 600; color: #92400e; margin-bottom: 0.25rem;">
                                            Último mensaje de {{ $chat->ultimoMensaje->user->name }}
                                        </div>
                                        <div style="color: #78350f;">
                                            {{ Str::limit($chat->ultimoMensaje->mensaje, 100) }}
                                        </div>
                                        <div style="font-size: 0.75rem; color: #a16207; margin-top: 0.25rem;">
                                            {{ $chat->ultimoMensaje->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                @endif

                                <div style="display: flex; align-items: center; gap: 1.5rem; margin-top: 0.75rem; font-size: 0.875rem; color: #6b7280;">
                                    <span><i class="fas fa-calendar"></i> {{ $chat->created_at->format('d/m/Y H:i') }}</span>
                                    <span><i class="fas fa-comments"></i> {{ $chat->mensaje_count }}/22 mensajes</span>
                                    @if($chat->mensajesNoLeidos()->where('user_id', '!=', auth()->id())->count() > 0)
                                        <span style="padding: 0.25rem 0.75rem; background: #ef4444; color: white; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;">
                                            {{ $chat->mensajesNoLeidos()->where('user_id', '!=', auth()->id())->count() }} sin leer
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</x-app-layout>
