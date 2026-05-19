<?php

namespace App\Filament\Resources\SessionCustomers\Concerns;

use App\Models\Customer;

trait InteractsWithSessionCustomerRecord
{
    public ?Customer $record = null;

    protected function getSessionCustomerRecord(): ?Customer
    {
        if (($this->record ?? null) instanceof Customer) {
            return $this->record;
        }

        try {
            $owner = $this->getOwner();

            if ($owner && method_exists($owner, 'getRecord')) {
                $record = $owner->getRecord();

                if ($record instanceof Customer) {
                    return $record;
                }
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }
}
