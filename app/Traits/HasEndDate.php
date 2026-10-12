<?php

namespace App\Traits;

trait HasEndDate
{
    /**
     * Détermine si la date de fin prévue est dépassée.
     *
     * Sert à n'autoriser la clôture et la suppression qu'après échéance.
     */
    public function hasEnded(): bool
    {
        return $this->end_date->lt(today());
    }
}
