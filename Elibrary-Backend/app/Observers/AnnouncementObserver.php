<?php

namespace App\Observers;

use App\Models\Announcement;
use App\Events\NewAnnouncement;
use Log;
class AnnouncementObserver
{

    public function created(Announcement $announcement): void
    {
        Log::info('Broadcasting new announcement', [
            'id' => $announcement->id,
            'message' => $announcement->announcement,
        ]);
        event(new NewAnnouncement($announcement->id, $announcement->announcement));
    }

    public function updated(Announcement $announcement): void
    {
        //
    }

    public function deleted(Announcement $announcement): void
    {
        //
    }

    public function restored(Announcement $announcement): void
    {
        //
    }

    public function forceDeleted(Announcement $announcement): void
    {
        //
    }
}
