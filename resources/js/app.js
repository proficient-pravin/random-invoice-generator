import './bootstrap';
import 'flowbite';
import Alpine from 'alpinejs'; 

// Initialize Alpine
window.Alpine = Alpine;
import persist from '@alpinejs/persist'
 
Alpine.plugin(persist)
 

Alpine.start();
// console.log('After Alpine.start:', Alpine.magic); // Check if $persist is available here
