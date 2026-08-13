// 1. Bootstrap runs first — sets up window.Axios, window.Echo (Pusher)
import './bootstrap';

// 2. Alpine starts after Echo is ready
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
