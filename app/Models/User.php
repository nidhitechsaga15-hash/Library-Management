<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'father_name',
        'mother_name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'student_id',
        'course',
        'branch',
        'batch',
        'semester',
        'year',
        'staff_id',
        'department',
        'staff_role',
        'date_of_birth',
        'is_active',
        'membership_id',
        'member_type',
        'membership_status',
        'membership_expiry_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'is_active' => 'boolean',
            'membership_expiry_date' => 'date',
        ];
    }

    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }

    public function fines()
    {
        return $this->hasMany(Fine::class);
    }

    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class)->orderBy('created_at', 'desc');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }

    public function libraryCard()
    {
        return $this->hasOne(\App\Models\LibraryCard::class)->latest();
    }

    public function reservations()
    {
        return $this->hasMany(\App\Models\BookReservation::class);
    }

    public function hasValidLibraryCard()
    {
        return $this->libraryCard && $this->libraryCard->isValid();
    }

    public function getActiveBorrowsCount()
    {
        return $this->borrows()->where('status', 'borrowed')->count();
    }

    public function canBorrowMoreBooks($maxBooks = null)
    {
        if ($maxBooks === null) {
            // Get max books from member type settings
            $maxBooks = \App\Models\MemberTypeSetting::getMaxBooks($this->member_type ?? 'student');
        }
        return $this->getActiveBorrowsCount() < $maxBooks;
    }

    /**
     * Get max books allowed for this user based on member type
     */
    public function getMaxBooksAllowed()
    {
        return \App\Models\MemberTypeSetting::getMaxBooks($this->member_type ?? 'student');
    }

    /**
     * Get issue duration for this user based on member type
     */
    public function getIssueDurationDays()
    {
        return \App\Models\MemberTypeSetting::getIssueDuration($this->member_type ?? 'student');
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isStaff()
    {
        return $this->role === 'staff';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    public function bookConditions()
    {
        return $this->hasMany(BookCondition::class, 'reported_by');
    }

    /**
     * Generate unique membership ID
     */
    public static function generateMembershipId($role = 'student')
    {
        $prefix = strtoupper(substr($role, 0, 1)); // S, F, or T
        $year = date('Y');
        $lastMember = self::where('membership_id', 'like', "{$prefix}{$year}%")
            ->orderBy('membership_id', 'desc')
            ->first();
        
        if ($lastMember && $lastMember->membership_id) {
            $lastNumber = (int) substr($lastMember->membership_id, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
