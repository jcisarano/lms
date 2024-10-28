<?php
namespace App\Repositories\Backend\Data;

use App\Models\Auth\User;

use App\Exceptions\GeneralException;
use \Illuminate\Database\QueryException;
use App\Exceptions\DatabaseException;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


/**
 * Class UserDataRepository.
 */
class UserDataRepository extends BaseRepository
{
    /**
     * EventRepository constructor.
     *
     */
    public function __construct()
    {

    }

    public function GetStudentDataForInstructor($instructorId)
    {
        try {
            $students = DB::table('users')
                ->leftJoin('instructor_user', 'users.id', '=', 'instructor_user.user_id')
                ->leftJoin('third_party_id', 'users.id', '=', 'third_party_id.user_id')
                ->select('users.id', 'users.first_name', 'users.last_name', 'third_party_id.third_party_id')
                ->where('instructor_user.instructor_id', '=', $instructorId)
                ->distinct()
                ->get();
        }
        catch (QueryException $ex) {
            $ex->errorInfo;
        }

        if ($students) {
            return $students;
        }

        // Could not retrieve list. Return response with error message.
        $response = [
               'response_msg' => trans('exceptions.backend.metricData.create_error'),
               'error_info' => isset($error_info) ? $error_info : null
           ];
        throw new DatabaseException(json_encode($response));
    }
}


