<?php

namespace App\Models\Courses\Traits;

use App\Models\Auth\User;
use App\Models\Courses\SeatBlock;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Trait SeatBlockMethod for use with SeatBlock class.
 */
trait SeatBlockMethod
{
    public function GetSeatCountForUser(User $user)
    {
        $totalSeats = SeatBlock::where('instructor_id', $user->id)
            ->where(function($query) {
                $query->where('expires_at', '<', Carbon::now()->toDateTimeString())
                    ->orWhere('expires_at', 'is', null);
            })
            ->sum('seat_count');

        return $totalSeats;
    }
}