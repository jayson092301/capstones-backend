<?php

namespace App\Observers;

use App\Models\PcDevices;
use App\Events\DesktopPcStatusUpdated;
use Log;

class DesktopLockAppObserver
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
        if ($pcDevices->wasChanged('status') && $pcDevices->status === 'vacant') {
            Log::info('Broadcasting device update', ['id' => $pcDevices->id, 'status' => $pcDevices->status]);
            event(new DesktopPcStatusUpdated(
                $pcDevices->id, 
                $pcDevices->status, 
                $pcDevices->device_name, 
                $pcDevices->mac_address,
            ));
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
