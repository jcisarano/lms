<?php
namespace App\Repositories\Backend\Instructors;

use App\Exceptions\DatabaseException;
use \Illuminate\Database\QueryException;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Auth\User;


/**
 * Class CourseAccessRepository
 */
class CourseAccessRepository extends BaseRepository
{
    public function getStudentsForInstructor($userId, $search_term, $order_column_name, $order_dir, $start, $length)
    {
        $instructorIds[] = $userId;
        $filteredStudents = User::whereHas('instructors', function($q) use($instructorIds) {
            $q->whereIn('instructor_id', $instructorIds);
                //->select('instructor_student.active AS canAccess');
        })->select('users.id','users.first_name','users.last_name')
        ->when( !empty($order_column_name), function($q) use ($order_column_name, $order_dir) {
            $q->orderBy($order_column_name, $order_dir);
        })
        ->when($search_term, function($q) use($search_term) {
            return $q->where(function ($q1) use ($search_term) {
                $q1->where('users.first_name', 'like', "%{$search_term}%")
                    ->orWhere('users.last_name', 'like', "%{$search_term}%");
                });
            })
        ->offset($start)->limit($length)->get();

        $totalFiltered = User::whereHas('instructors', function($q) use($instructorIds) {
            $q->whereIn('instructor_id', $instructorIds)
                ->select('instructor_student.active');
        })->select('users.id')
        ->when($search_term, function($q) use($search_term) {
            return $q->where(function ($q1) use ($search_term) {
                $q1->where('users.first_name', 'like', "%{$search_term}%")
                    ->orWhere('users.last_name', 'like', "%{$search_term}%");
                });
            })
        ->get();

        return [
            'students' => $filteredStudents,
            'filteredCount' => $totalFiltered->count()
        ];
    }

    public function setStudentCourseAccess(User $instructor, $studentId, $canAccess) {
        if ($instructor->hasStudent($studentId)) {
            $instructor->setAccessForStudent($studentId, $canAccess);
            return true;
        }
        return false;
    }

    public function removeInstructorStudentConnection(User $instructor, $studentId)
    {
        if (is_numeric($studentId) && $instructor->hasStudent($studentId))
        {
            $instructor->removeStudent($studentId);
            return true;
        }
        
        return false;
    }
}