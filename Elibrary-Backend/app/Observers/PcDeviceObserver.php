<?php

namespace App\Observers;

use App\Models\PcDevices;
use App\Events\PcStatusUpdated;
use Log;
class PcDeviceObserver
{
    /**
     * Handle the PcDevices "created" event.
     */
    public function created(PcDevices $pcDevices): void
    {
        //
    }

    /**
     * Handle the PcDevices "updated" event.
     */
    public function updated(PcDevices $pcDevices): void
    {
        //
        if ($pcDevices->wasChanged('status')) {
            Log::info('Broadcasting device update', ['id' => $pcDevices->id, 'status' => $pcDevices->status]);
            event(new PcStatusUpdated($pcDevices->id, $pcDevices->status, $pcDevices->device_name));
        } else if ($pcDevices->wasChanged("status") && $session->status === 'pre-occupied') {
            Log::info('Broadcasting device update', ['id' => $pcDevices->id, 'pre-occupied' => $pcDevices->status]);
            event(new PcStatusUpdated($pcDevices->id, $pcDevices->status, $pcDevices->device_name));
        }
    }

    /**
     * Handle the PcDevices "deleted" event.
     */
    public function deleted(PcDevices $pcDevices): void
    {
        //
    }

    /**
     * Handle the PcDevices "restored" event.
     */
    public function restored(PcDevices $pcDevices): void
    {
        //
    }

    /**
     * Handle the PcDevices "force deleted" event.
     */
    public function forceDeleted(PcDevices $pcDevices): void
    {
        //
    }
}
