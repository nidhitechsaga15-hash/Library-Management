<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LibraryCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LibraryCardController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $card = $user->libraryCard;

        return view('student.library-card.show', compact('card'));
    }

    public function request()
    {
        $user = Auth::user();

        // Check if user already has an active card
        $existingCard = LibraryCard::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($existingCard) {
            return redirect()->route('student.library-card.show')
                ->with('error', 'You already have an active library card!');
        }

        // Check if there's a pending request (blocked or lost card)
        $pendingCard = LibraryCard::where('user_id', $user->id)
            ->whereIn('status', ['blocked', 'lost'])
            ->first();

        if ($pendingCard) {
            return redirect()->route('student.library-card.show')
                ->with('error', 'You have a blocked/lost card. Please contact library staff.');
        }

        // Notify admins and staff about card request
        \App\Helpers\NotificationHelper::notifyAdminsAndStaff(
            'library_card_request',
            'New Library Card Request',
            $user->name . ' (' . ($user->student_id ?? $user->email) . ') has requested a new library card.',
            route('admin.library-cards.index')
        );

        return redirect()->route('student.library-card.show')
            ->with('success', 'Library card request submitted! Staff will review and issue your card.');
    }

    public function reportLost()
    {
        $user = Auth::user();
        $card = $user->libraryCard;

        if (!$card) {
            return redirect()->route('student.library-card.show')
                ->with('error', 'You do not have a library card!');
        }

        if ($card->status === 'lost') {
            return redirect()->route('student.library-card.show')
                ->with('error', 'Card is already marked as lost!');
        }

        $card->markAsLost();

        // Notify admins and staff
        \App\Helpers\NotificationHelper::notifyAdminsAndStaff(
            'library_card_lost',
            'Library Card Reported Lost',
            $user->name . ' has reported their library card ' . $card->card_number . ' as lost.',
            route('admin.library-cards.index')
        );

        return redirect()->route('student.library-card.show')
            ->with('success', 'Lost card reported! Please contact library staff for a replacement.');
    }

    public function print()
    {
        $user = Auth::user();
        $card = $user->libraryCard;

        if (!$card) {
            return redirect()->route('student.library-card.show')
                ->with('error', 'You do not have a library card!');
        }

        return view('student.library-card.print', compact('card'));
    }
}
