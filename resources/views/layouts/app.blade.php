<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SGCJ') }}</title>

    <!-- Fonts -->
    <link rel="icon" href="{{ asset('img/logo-gobernacion.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/figtree.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/all.min.css') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Imagen de Fondo */
        .bg-login-image {
            background: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.5));
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body class="font-sans antialiased text-slate-800">
    <div class="min-h-screen bg-slate-50 bg-login-image">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow border-b border-slate-200">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
    @stack('scripts')
    <script src="{{ asset('js/global-validation.js') }}"></script>
    <!-- ==========================================
         WIDGET CHATBOT IA (GEMINI NATIVO SGCJ)
    =========================================== -->
    <div id="ai-chatbot-container" class="fixed bottom-6 right-6 z-50 font-sans">
        <!-- Ventana del Chat (Oculta por defecto) -->
        <div id="ai-chatbot-window" class="hidden flex-col bg-white w-80 sm:w-96 rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transition-all duration-300 transform origin-bottom-right mb-4">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-4 flex justify-between items-center text-white shadow-md">
                <div class="flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <span class="font-semibold tracking-wide">Asistente SGCJ</span>
                </div>
                <button id="ai-close-btn" class="hover:bg-white/20 p-1 rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <!-- Mensajes -->
            <div id="ai-messages-container" class="p-4 h-80 overflow-y-auto bg-slate-50 flex flex-col gap-3 text-sm">
                <div class="bg-white border border-slate-200 text-slate-700 p-3 rounded-xl rounded-tl-none self-start max-w-[85%] shadow-sm">
                    Hola, soy el asistente inteligente del SGCJ. ¿En qué puedo ayudarte hoy?
                </div>
            </div>

            <!-- Input Box -->
            <div class="p-3 bg-white border-t border-slate-200 flex gap-2 items-center">
                <input type="text" id="ai-chat-input" class="flex-1 border border-slate-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all" placeholder="Consulta un trámite o gaceta...">
                <button id="ai-send-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-2 w-10 h-10 flex items-center justify-center transition-colors shadow-md">
                    <svg class="w-5 h-5 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path></svg>
                </button>
            </div>
        </div>

        <!-- Botón Flotante (FAB) -->
        <button id="ai-fab-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-4 shadow-[0_8px_30px_rgb(0,0,0,0.12)] transition-transform hover:scale-110 flex items-center justify-center ml-auto">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
        </button>
    </div>

    <!-- Lógica JS del Widget -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const fabBtn = document.getElementById('ai-fab-btn');
        const closeBtn = document.getElementById('ai-close-btn');
        const chatWindow = document.getElementById('ai-chatbot-window');
        const sendBtn = document.getElementById('ai-send-btn');
        const chatInput = document.getElementById('ai-chat-input');
        const msgContainer = document.getElementById('ai-messages-container');

        const toggleChat = () => {
            chatWindow.classList.toggle('hidden');
            chatWindow.classList.toggle('flex');
            if (!chatWindow.classList.contains('hidden')) chatInput.focus();
        };

        fabBtn.addEventListener('click', toggleChat);
        closeBtn.addEventListener('click', toggleChat);

        const appendMessage = (text, isUser = false) => {
            const div = document.createElement('div');
            div.className = isUser 
                ? 'bg-indigo-600 text-white p-3 rounded-xl rounded-tr-none self-end max-w-[85%] shadow-sm' 
                : 'bg-white border border-slate-200 text-slate-700 p-3 rounded-xl rounded-tl-none self-start max-w-[85%] shadow-sm whitespace-pre-wrap leading-relaxed';
            div.innerHTML = isUser ? text : text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
            msgContainer.appendChild(div);
            msgContainer.scrollTop = msgContainer.scrollHeight;
        };

        const sendMessage = async () => {
            const message = chatInput.value.trim();
            if (!message) return;

            appendMessage(message, true);
            chatInput.value = '';
            chatInput.disabled = true;

            const loadingId = 'loading-' + Date.now();
            const loadingDiv = document.createElement('div');
            loadingDiv.id = loadingId;
            loadingDiv.className = 'bg-white border border-slate-200 text-slate-500 p-3 rounded-xl rounded-tl-none self-start shadow-sm text-xs flex gap-2 items-center';
            loadingDiv.innerHTML = '<span class="animate-pulse">Consultando el archivo digital...</span>';
            msgContainer.appendChild(loadingDiv);
            msgContainer.scrollTop = msgContainer.scrollHeight;

            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch('/chatbot/ask', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: message })
                });

                const data = await response.json();
                document.getElementById(loadingId).remove();
                
                if (response.ok) {
                    appendMessage(data.reply);
                } else {
                    appendMessage('Error: ' + (data.reply || 'No pude procesar tu solicitud.'));
                }
            } catch (error) {
                document.getElementById(loadingId).remove();
                appendMessage('Error de red o el servidor SGCJ no responde.');
            } finally {
                chatInput.disabled = false;
                chatInput.focus();
            }
        };

        sendBtn.addEventListener('click', sendMessage);
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });
    });
    </script>
</body>

</html>