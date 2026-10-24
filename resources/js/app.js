import Alpine from 'alpinejs';
import htmx from 'htmx.org';

// htmx doesn't swap error responses by default. Swapping 422s lets a form
// come back from the server with its validation errors in place.
htmx.config.responseHandling.unshift({ code: '422', swap: true });

window.Alpine = Alpine;
Alpine.start();
