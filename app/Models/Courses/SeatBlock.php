<?php

namespace App\Models\Courses;

use Illuminate\Database\Eloquent\Model;
use App\Models\Courses\Traits\SeatBlockMethod;

/**
 * Class SeatBlock used to manage instructors available paid seats for courses.
 */
class SeatBlock extends Model
{
    use SeatBlockMethod;
}
