import "./bootstrap";
import "../../vendor/masmerise/livewire-toaster/resources/js";

import {
    Livewire,
    Alpine,
} from "../../vendor/livewire/livewire/dist/livewire.esm";

import anchor from "@alpinejs/anchor";
import collapse from "@alpinejs/collapse";

Alpine.plugin(anchor);
Alpine.plugin(collapse);

Livewire.start();
