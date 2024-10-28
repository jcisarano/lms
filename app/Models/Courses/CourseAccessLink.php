<?php

namespace App\Models\Courses;

use Illuminate\Database\Eloquent\Model;
use App\Models\Courses\Traits\CourseAccessLinkMethod;

/**
 * Class CourseAccessLink.
 */
class CourseAccessLink extends Model
{
    use CourseAccessLinkMethod;
}
