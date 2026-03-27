<?php

namespace App\Models\Concerns;

trait ExposesPrimaryKeyAsId
{
    public function getIdAttribute(): ?int
    {
        $key = $this->getAttributeFromArray($this->getKeyName());

        return $key !== null ? (int) $key : null;
    }
}
