<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\LibraryCard;
use App\Models\User;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LibraryCardController extends Controller
{
    public function index()
    {
        $cards = LibraryCard::with('user')->latest()->get();
        
        $stats = [
            'total' => LibraryCard::count(),
            'active' => LibraryCard::where('status', 'active')->where('validity_date', '>=', now())->count(),
            'expired' => LibraryCard::where('validity_date', '<', now())->count(),
            'blocked' => LibraryCard::whereIn('status', ['blocked', 'lost'])->count(),
        ];

        return view('staff.library-cards.index', compact('cards', 'stats'));
    }

    public function create()
    {
        $students = User::where('role', 'student')
            ->where('is_active', true)
            ->whereDoesntHave('libraryCard', function($query) {
                $query->where('status', 'active');
            })
            ->orderBy('name')
            ->get();

        return view('staff.library-cards.create', compact('students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'validity_date' => 'required|date|after:today',
            'notes' => 'nullable|string',
        ]);

        $user = User::findOrFail($validated['user_id']);

        // Check if user already has an active card
        $existingCard = LibraryCard::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($existingCard) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'User already has an active library card!');
        }

        // Generate card number
        $cardNumber = LibraryCard::generateCardNumber();

        // Generate QR code (using SVG format - no imagick required)
        $qrCode = QrCode::format('svg')
            ->size(200)
            ->generate($cardNumber);

        $card = LibraryCard::create([
            'user_id' => $user->id,
            'card_number' => $cardNumber,
            'issue_date' => now(),
            'validity_date' => $validated['validity_date'],
            'status' => 'active',
            'qr_code' => $qrCode,
            'issued_by' => auth()->user()->name,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Notify student
        \App\Helpers\NotificationHelper::createNotification(
            $user->id,
            'library_card_issued',
            'Library Card Issued',
            'Your library card ' . $cardNumber . ' has been issued. Valid until ' . $card->validity_date->format('M d, Y'),
            route('student.library-card.show')
        );

        return redirect()->route('staff.library-cards.show', $card)
            ->with('success', 'Library card issued successfully!');
    }

    public function show(LibraryCard $libraryCard)
    {
        $libraryCard->load('user');
        return view('staff.library-cards.show', compact('libraryCard'));
    }

    public function block(LibraryCard $libraryCard)
    {
        if ($libraryCard->isBlocked()) {
            return redirect()->back()
                ->with('error', 'Card is already blocked!');
        }

        $libraryCard->markAsBlocked('Blocked by staff');

        // Notify student
        \App\Helpers\NotificationHelper::createNotification(
            $libraryCard->user_id,
            'library_card_blocked',
            'Library Card Blocked',
            'Your library card ' . $libraryCard->card_number . ' has been blocked.',
            route('student.library-card.show')
        );

        return redirect()->back()
            ->with('success', 'Library card blocked successfully!');
    }

    public function unblock(LibraryCard $libraryCard)
    {
        if (!$libraryCard->isBlocked()) {
            return redirect()->back()
                ->with('error', 'Card is not blocked!');
        }

        $libraryCard->update(['status' => 'active']);

        // Notify student
        \App\Helpers\NotificationHelper::createNotification(
            $libraryCard->user_id,
            'library_card_unblocked',
            'Library Card Unblocked',
            'Your library card ' . $libraryCard->card_number . ' has been unblocked.',
            route('student.library-card.show')
        );

        return redirect()->back()
            ->with('success', 'Library card unblocked successfully!');
    }

    public function renew(Request $request, LibraryCard $libraryCard)
    {
        $validated = $request->validate([
            'validity_date' => 'required|date|after:today',
        ]);

        $libraryCard->renew($validated['validity_date']);

        // Notify student
        \App\Helpers\NotificationHelper::createNotification(
            $libraryCard->user_id,
            'library_card_renewed',
            'Library Card Renewed',
            'Your library card ' . $libraryCard->card_number . ' has been renewed until ' . $libraryCard->validity_date->format('M d, Y'),
            route('student.library-card.show')
        );

        return redirect()->back()
            ->with('success', 'Library card renewed successfully!');
    }

    public function print(LibraryCard $libraryCard)
    {
        $libraryCard->load('user');
        return view('staff.library-cards.print', compact('libraryCard'));
    }
}
