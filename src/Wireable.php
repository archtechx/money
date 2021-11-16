<?php

declare(strict_types=1);

// Temporary until Livewire gets the new system for Wireable properties

namespace Livewire {
    interface Wireable
    {
        public function toLivewire();
        public static function fromLivewire($value);
    }
}
