<?php

namespace App\Models\Courses\Traits;

use App\Models\Auth\User;
use App\Models\Courses\CourseAccessLink;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Trait CourseAccessLinkMethod.
 */
trait CourseAccessLinkMethod
{
    public static function GenerateUUID()
    {
        return (string) Str::uuid();
    }

    public static function GetByUuid($uuid, $isActive = null)
    {
        return CourseAccessLink::where('uuid', $uuid)
            ->when($isActive != null, function ($query) use ($isActive) {
                $iIsActive = $isActive ? 1 : 0;
                Log::debug("isActive {$isActive} iIsActive {$iIsActive}");
                return $query->where('active', '=', $iIsActive);
            })
            ->first();
    }

    public static function GetInstructorForToken($uuid, $isActive)
    {
        $instructorId = 0;
        $link = self::GetByUuid($uuid, $isActive);
        if ($link != null)
            $instructorId = $link->instructor_id;

        $instructor = User::findOr($instructorId, function() {
            return null;
        });

        return $instructor;
    }
}