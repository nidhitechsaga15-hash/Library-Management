<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LibraryCard;
use Carbon\Carbon;
use App\Helpers\NotificationHelper;

class CheckLibraryCardExpiration extends Command
{
    protected $signature = 'library-cards:check-expiration';
    protected $description = 'Check and notify users about expiring library cards';

    public function handle()
    {
        // Check cards expiring in 30 days
        $expiringSoon = LibraryCard::where('status', 'active')
            ->where('validity_date', '>=', now())
            ->where('validity_date', '<=', now()->addDays(30))
            ->whereDoesntHave('user.notifications', function($query) {
                $query->where('type', 'library_card_expiring')
                    ->where('created_at', '>=', now()->subDay());
            })
            ->get();

        foreach ($expiringSoon as $card) {
            NotificationHelper::createNotification(
                $card->user_id,
                'library_card_expiring',
                'Library Card Expiring Soon',
                'Your library card ' . $card->card_number . ' will expire on ' . $card->validity_date->format('M d, Y') . '. Please renew it soon.',
                route('student.library-card.show')
            );
        }

        // Check expired cards
        $expired = LibraryCard::where('status', 'active')
            ->where('validity_date', '<', now())
            ->get();

        foreach ($expired as $card) {
            // Update status to expired
            $card->update(['status' => 'expired']);

            NotificationHelper::createNotification(
                $card->user_id,
                'library_card_expired',
                'Library Card Expired',
                'Your library card ' . $card->card_number . ' has expired on ' . $card->validity_date->format('M d, Y') . '. Please contact library staff to renew.',
                route('student.library-card.show')
            );
        }

        $this->info('Checked ' . ($expiringSoon->count() + $expired->count()) . ' library cards.');
        return 0;
    }
}
