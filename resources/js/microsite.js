import { registerMicrositeCart } from "./microsite/cart";

function bootstrap() {
    if (!window.Alpine) {
        return;
    }

    registerMicrositeCart(window.Alpine);
}

if (window.Alpine) {
    bootstrap();
}

document.addEventListener("livewire:init", () => {
    bootstrap();
});
