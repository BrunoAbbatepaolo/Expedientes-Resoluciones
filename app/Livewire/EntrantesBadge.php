<?php

namespace App\Livewire;

use Livewire\Component;

class EntrantesBadge extends Component
{
    public function render()
    {
        return view('livewire.entrantes-badge', [
            'count' => auth()->user()?->pasesPendientesCount() ?? 0,
        ]);
    }
}
