import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;
window.createIcons = createIcons;
window.lucideIcons = icons;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});
