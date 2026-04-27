import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import '../../vendor/masmerise/livewire-toaster/resources/js/index.js';

const Basque = {
    weekdays: {
        shorthand: ["Ig", "Al", "Ar", "Az", "Og", "Or", "Lr"],
        longhand: ["Igandea", "Astelehena", "Asteartea", "Asteazkena", "Osteguna", "Ostirala", "Larunbata"]
    },
    months: {
        shorthand: ["Urt", "Ots", "Mar", "Api", "Mai", "Eka", "Uzt", "Abu", "Ira", "Urr", "Aza", "Abe"],
        longhand: ["Urtarrila", "Otsaila", "Martxoa", "Apirila", "Maiatza", "Ekaina", "Uztaila", "Abuztua", "Iraila", "Urria", "Azaroa", "Abendua"]
    },
    firstDayOfWeek: 1,
    ordinal: () => ".",
    time_24hr: true
};

function initSushinelliDatePickers() {
    const inputs = document.querySelectorAll('[data-sush-date]');
    inputs.forEach((input) => {
        // Si ya está inicializado, no hacer nada (para evitar doble instancia)
        if (input._flatpickr) return;

        flatpickr(input, {
            locale: Basque,
            inline: true,
            dateFormat: 'Y-m-d',
            minDate: "today",
            defaultDate: input.value || new Date(),
            disableMobile: true,
            onChange: (_selectedDates, _dateStr) => {
                 input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    });
}

// Inicializar en carga inicial y navegación de Livewire
document.addEventListener('livewire:navigated', () => {
    initSushinelliDatePickers();
});

// Observer para casos extremos donde Livewire re-inyecta el HTML sin disparar navigated
const observer = new MutationObserver(() => initSushinelliDatePickers());
observer.observe(document.documentElement, { subtree: true, childList: true });
