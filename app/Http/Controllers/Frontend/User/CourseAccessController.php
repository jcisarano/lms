<?php

namespace App\Http\Controllers\Frontend\User;

use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DataTables;
use Illuminate\Support\Facades\DB;

use App\Models\Auth\User;
use App\Http\Controllers\Frontend\Auth\LoginController;

use App\Repositories\Backend\Instructors\CourseAccessRepository;
use App\Repositories\Backend\Instructors\CourseAccessLinkRepository;
use App\Models\Courses\CourseAccessLink;

use App\Notifications\Frontend\Auth\UserCourseAccessNotification;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Class CourseAccessController.
 */
class CourseAccessController extends Controller
{
    public function joinInstructor($token)
    {
        $instructor = CourseAccessLink::GetInstructorForToken($token, true);
        if (is_null($instructor))
        {
            return view('frontend.joininstructor.invalidtoken');
        }

        if (Auth::check())
        {
            $user = Auth::user();
            $instructor->addStudent($user->id);
            
            return view('frontend.joininstructor.tokensuccess');
        }
        else
        {
            LoginController::setLoginRedirectPath(route('frontend.auth.join.instructor', 1));
            return to_route('frontend.auth.join.login', $token);
        }
    }

    public function getStudentsIndexView ()
    {
        $seats_available = 99;
        $seats_used = 101;
        return view("backend.instructors.student_index", ["seats_available" => $seats_available, "seats_used" => $seats_used, "hide_button" => false]);
    }

    public function getStudentsForInstructor (Request $request)
    {
        $user = auth()->user();
        if( !($user->isAdmin() ) && !( $user->hasPermissionTo(config('access.permissions.instructors.student-list'))))
        {
            abort(403);
        }

        // paging
        $pageStart = $request->get('start');
        $pageLength = $request->get('length');

        $tableColumns = $request->get('columns');
        
        // sort by column
        $sortColumns = $request->get('order');
        $sortColumnIndex = $sortColumns[0]['column'];
        $sortColumnName = $tableColumns[$sortColumnIndex]['data'];
        $sortDirection = $sortColumns[0]['dir'];
        
        // search for data
        $searchTerms = $request->get('search');
        $search_term = $searchTerms['value'];

        $repo = new CourseAccessRepository();
        $results = $repo->getStudentsForInstructor($user->id, $search_term, $sortColumnName, $sortDirection, $pageStart, $pageLength);

        $totalCount = $user->students->count();

        $dt = Datatables::of($results['students'])
            ->setTotalRecords($totalCount)
            ->setFilteredRecords($results['filteredCount'])
            ->setOffset($pageStart)
            ->addColumn('actions', function($student) {
                return view("backend.instructors.includes.actions", ["student"=> $student])->render();
            })
            ->addColumn("active", function($student) use ($user) {
                $instructor = $student->instructors()->where('instructor_id', $user->id)->get();
                return $instructor[0]->pivot->active == 1 ? "Yes" : "No";
            })
            ->rawColumns(["actions", "active"])
            ->make(true);

        return $dt;
    }
    
    public function setStudentCourseAccess(Request $request)
    {
        $instructor = auth()->user();
        if( !($instructor->isAdmin() ) && !( $instructor->hasPermissionTo(config('access.permissions.instructors.student-list'))) )
        {
            abort(403);
        }

        $studentId = $request->get('studentId');
        $canAccess = $request->get('canAccess');

        $repo = new CourseAccessRepository();
        $success = $repo->setStudentCourseAccess($instructor, $studentId, $canAccess);

        $emailSent = false;
        if( $success && $canAccess )
        {
            $student = User::find($studentId);
            if( $student )
            {
                $emailSent = $this->sendCourseAccessSuccessEmail($instructor, $student);
            }
        }

        return [
            'student_id' => $studentId,
            'canAccess' => $canAccess,
            'success' => $success,
            'emailSuccess' => $emailSent
        ];
    }

    public function sendCourseAccessSuccessEmail(User $instructor, User $student)
    {
        try
        {
            $student->notify(new UserCourseAccessNotification($instructor, $student));
        } catch (TransportExceptionInterface $e)
        {
            $err = [
                "CourseAccessController::sendCourseAccessSuccessEmail() failed to send email to {$student->email}",
                $e->getMessage()
            ];
            Log::error( print_r($err, true));
            return false;
        }
        
        return true;
    }

    public function removeInstructorStudentConnection(Request $request)
    {
        $user = auth()->user();
        if( !($user->isAdmin()) && !($user->hasPermissionTo(config('access.permissions.instructors.student-list'))))
        {
            abort(403);
        }

        $studentId = $request->get('studentId');
        $repo = new CourseAccessRepository();
        $success = $repo->removeInstructorStudentConnection($user, $studentId);

        return [
            'student_id' => $studentId,
            'success' => $success
        ];
    }
    
    public function getStudentLinkIndexView(Request $request)
    {
        $user = auth()->user();
        if( !($user->isAdmin()) && !($user->hasPermissionTo(config('access.permissions.instructors.student-list'))))
        {
            abort(403);
        }

        return view("backend.instructors.link_index", ["user_id" => $user->id]);
    }

    public function getInstructorCourseLinks(Request $request) 
    {
        $user = auth()->user();
        if( !($user->isAdmin()) && !($user->hasPermissionTo(config('access.permissions.instructors.student-list'))))
        {
            abort(403);
        }

        // paging
        $pageStart = $request->get('start');
        $pageLength = $request->get('length');

        $tableColumns = $request->get('columns');
        $sortColumns = $request->get('order');
        $sortColumnIndex = $sortColumns[0]['column'];
        $sortColumnName = $tableColumns[$sortColumnIndex]['data'];
        $sortDirection = $sortColumns[0]['dir'];

        $repo = new CourseAccessLinkRepository();
        $results = $repo->getLinksForInstructor($user, $pageStart, $pageLength, $sortDirection);

        $dt = Datatables::of($results['links'])
            ->setTotalRecords($results['totalCount'])
            ->setFilteredRecords($results['totalCount'])
            ->setOffset($pageStart)
            ->addColumn('actions', function($link) {
                return view("backend.instructors.includes.link_actions", ["link"=> $link])->render();
            })
            ->editColumn('uuid', function ($link) {
                return url("/join/{$link->uuid}");
            })
            ->editColumn('created_at', function ($link) {
                return $link->created_at->format('m-d-Y h:i:s');
            })
            ->editColumn('active', function($link) {
                return $link->active == 1 ? __('labels.general.active') : __('labels.general.inactive');
            })
            ->rawColumns(["actions"])
            ->make(true);

        return $dt;
    }

    public function setCourseLinkAccess(Request $request) 
    {
        $user = auth()->user();
        if( !($user->isAdmin()) && !($user->hasPermissionTo(config('access.permissions.instructors.student-list'))))
        {
            abort(403);
        }

        $linkId = $request->get('link_id');
        $isActive = $request->get('is_active');

        $repo = new CourseAccessLinkRepository();
        $success = $repo->setLinkStatus($user, $linkId, $isActive);

        return [
            'link_id' => $linkId,
            'is_active' => $isActive,
            'success' => $success
        ];
    }

    public function deleteCourseLinkAccess(Request $request) 
    {
        $user = auth()->user();
        if( !($user->isAdmin()) && !($user->hasPermissionTo(config('access.permissions.instructors.student-list'))))
        {
            abort(403);
        }
        $linkId = $request->get('link_id');

        $repo = new CourseAccessLinkRepository();
        $success = $repo->deleteLink($user, $linkId);

        return [
            'link_id' => $linkId,
            'success' => $success
        ];
    }

    public function createCourseLinkAccess(Request $request)
    {
        $user = auth()->user();
        if( !($user->isAdmin()) && !($user->hasPermissionTo(config('access.permissions.instructors.student-list'))))
        {
            abort(403);
        }

        $repo = new CourseAccessLinkRepository();
        $success = $repo->createLink($user);

        return [
            'success' => $success
        ];
    }
}
