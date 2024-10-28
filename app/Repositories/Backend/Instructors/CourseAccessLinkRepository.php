<?php
namespace App\Repositories\Backend\Instructors;

use App\Exceptions\DatabaseException;
use \Illuminate\Database\QueryException;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Courses\CourseAccessLink;

use App\Models\Auth\User;

/**
 * Class CourseAccessLinkRepository
 */
class CourseAccessLinkRepository extends BaseRepository
{
    /**
        Create a course access link and attach it to the given user.
      */
    public function createLink(User $instructor) 
    {
        $link = new CourseAccessLink;
        $link->instructor_id = $instructor->id;
        $link->uuid = CourseAccessLink::GenerateUUID();
        $link->save();

        return true;
    }

    public function deleteLink(User $instructor, $linkId) 
    {
        $link = CourseAccessLink::find($linkId);
        if ($link->instructor_id == $instructor->id || $user->isAdmin())
        {
            $link->delete();
            return true;
        }

         return false;
    }

    /**
        Set link active or inactive. When inactive, the link cannot be used
        to access a course, but previously approved access for individual
        students will not be affected.
      */
    public function setLinkStatus(User $instructor, $linkId, $isActive) 
    {
        $link = CourseAccessLink::find($linkId);
        if ($link->instructor_id == $instructor->id || $user->isAdmin())
        {
            $link->active = $isActive;
            $link->save();
            return true;
        }

         return false;
    }

    public function getLinksForInstructor(User $instructor, $pageStart, $pageLength, $sort_dir="desc")
    {
        $links = CourseAccessLink::where("instructor_id", $instructor->id)
            ->select([DB::raw("SQL_CALC_FOUND_ROWS *")])
            ->orderBy("created_at", $sort_dir)
            ->offset($pageStart)->limit($pageLength)->get();
        $total = DB::select('SELECT FOUND_ROWS() as `row_count`');

        return [
            'links' => $links,
            'totalCount' => $total[0]->row_count
        ];
    }

    public function getLink ($link_uuid) 
    {
        $link = null;
        try
        {
            $link = CourseAccessLink::where("uuid", "=", $link_uuid)->firstOrFail();
        } catch (Exception $e) {}

        return $link;
    }
}