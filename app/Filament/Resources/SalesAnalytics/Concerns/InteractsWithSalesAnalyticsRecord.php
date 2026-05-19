<?php

namespace App\Filament\Resources\SalesAnalytics\Concerns;

use App\Filament\Resources\SalesAnalytics\SalesAnalyticsResource;
use App\Models\TicketOrder;
use App\Support\EventSalesInsights;

trait InteractsWithSalesAnalyticsRecord
{
    public ?TicketOrder $record = null;

    protected ?EventSalesInsights $cachedSalesInsights = null;

    protected function getSalesAnalyticsRecord(): ?TicketOrder
    {
        if (($this->record ?? null) instanceof TicketOrder) {
            return $this->resolveAggregateRecord($this->record);
        }

        try {
            $owner = $this->getOwner();

            if ($owner && method_exists($owner, 'getRecord')) {
                $record = $owner->getRecord();

                if ($record instanceof TicketOrder) {
                    return $this->resolveAggregateRecord($record);
                }
            }

            if ($owner && ($owner->record ?? null) instanceof TicketOrder) {
                return $this->resolveAggregateRecord($owner->record);
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    protected function getSalesInsights(): ?EventSalesInsights
    {
        if ($this->cachedSalesInsights) {
            return $this->cachedSalesInsights;
        }

        $record = $this->getSalesAnalyticsRecord();

        if (! $record) {
            return null;
        }

        return $this->cachedSalesInsights = new EventSalesInsights($record);
    }

    protected function resolveAggregateRecord(TicketOrder $record): TicketOrder
    {
        if ($this->isAggregateRecord($record)) {
            return $record;
        }

        $eventId = $record->event_id;

        if (! $eventId) {
            return $record;
        }

        $aggregateRecord = SalesAnalyticsResource::getEloquentQuery()
            ->where('event_id', $eventId)
            ->first();

        return $aggregateRecord instanceof TicketOrder ? $aggregateRecord : $record;
    }

    protected function isAggregateRecord(TicketOrder $record): bool
    {
        $attributes = $record->getAttributes();

        return array_key_exists('orders_count', $attributes)
            && array_key_exists('tickets_sold', $attributes)
            && array_key_exists('revenue_total', $attributes);
    }
}
