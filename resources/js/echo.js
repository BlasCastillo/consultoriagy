import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// El host de WebSockets se toma del mismo host desde el que se abrio la
// pagina. Asi funciona igual por 127.0.0.1, por LAN (172.16.0.108) o por
// el hotspot Wi-Fi de Windows (192.168.137.1), sin editar .env ni recompilar.
// Para forzar otro host, definir window.SGCJ_REVERB_HOST antes de cargar app.js.
const reverbHost = window.SGCJ_REVERB_HOST || window.location.hostname;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: reverbHost,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});