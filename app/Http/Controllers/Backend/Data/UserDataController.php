<?php
namespace App\Http\Controllers\Backend\Data;

use App\Repositories\Backend\Data\UserDataRepository;
use App\Models\Auth\User;
use App\Repositories\Backend\Auth\UserRepository;
use App\Models\NLPTracking\Encounter\EncounterInstance;
use App\Repositories\Backend\NLPTracking\EncounterInstanceRepository;
use App\Models\NLPTracking\Data\ReplayData;
use App\Repositories\Backend\NLPTracking\ReplayDataRepository;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserDataController extends Controller
{
    // Return all student data assigned to a single instructor
    function GetAllStudentDataForInstructor(Request $request)
    {
	$instructorId = isset($request->authToken["user_id"]) ? $request->authToken["user_id"] : null;

	// TODO: Hard-coded instructor ID for testing only. Remove
	$instructorId = 30;

        if(!$instructorId) {
            return response()->json(['success' => 0, 'response_msg' => 'Error getting student list', 'error_msg' => 'No instructor ID given'], 200);
        }

        // confirm this is an instructor
        $userRepo = new UserRepository(new User());
        $instructorUser = $userRepo->confirmUserRole($instructorId, "instructor");
        if(!$instructorUser) {
            return response()->json(['success' => 0, 'response_msg' => 'Error getting student list', 'error_msg' => 'User is not an instructor'], 200);
        }

        // Get list of students assigned to instructor
        $userDataRepo = new UserDataRepository();
        $students = $userDataRepo->GetStudentDataForInstructor($instructorId);
        if(!$students) {
            return response()->json(['success' => 0, 'response_msg' => 'Error getting student list', 'error_msg' => 'No students found linked to this instructor'], 200);
        }

        // All good. Return list of students
        return response()->json(['success' => 1, 'response_msg' => 'Success in getting student list', 'students' => json_encode($students), 'error_msg' => ''], 200);
   }

    // Return all Encounter Instances for selected user
    function GetEncounterInstanceListForUser(Request $request)
    {
	$userUuid = isset($request['userUuid']) ? $request['userUuid'] : null;
        if(!$userUuid)
            return response()->json(['success' => 0, 'response_msg' => 'Error getting encounter instance list', 'error_msg' => 'No user ID found'], 200);

        // Get user
        $userRepo = new UserRepository(new User());
        $userId = $userRepo->getUserByUuid($userUuid);
        if(!$userId)
            return response()->json(['success' => 0, 'response_msg' => 'Error getting encounter instance list', 'error_msg' => 'No user found'], 200);

        // Get all encounter instances for user
        $encounterInstanceRepo = new EncounterInstanceRepository(new EncounterInstance());
        $encounterInstanceList = $encounterInstanceRepo->getEncountersByUserId($userId);
        if(!$encounterInstanceList)
            return response()->json(['success' => 0, 'response_msg' => 'Error getting encounter instance list', 'error_msg' => 'No encounter instances found for this user'], 200);

        // All good. Return list of encounter instances
        return response()->json(['success' => 1, 'response_msg' => 'Succes in getting encounter instance list', 'encounterInstanceList' => json_encode($encounterInstanceList), 'error_msg' => null], 200);
    }

    // Return all Replay Data for one user and Encounter
    function GetAllReplayDataForUserAndEncounter(Request $request)
    {
 	    $encounterData = isset($request['encounterData']) ? json_decode($request['encounterData']) : null;
        if(!$encounterData)
            return response()->json(['success' => 0, 'response_msg' => 'Error getting replay data', 'error_msg' => 'No user id, scenario id, and encounter id given'], 200);

	    $userUuid = isset($encounterData->user_uuid) ? $encounterData->user_uuid : null;
        $caseId = isset($encounterData->scenario_id) ? $encounterData->scenario_id : null;
        $encounterId = isset($encounterData->encounter_id) ? $encounterData->encounter_id : null;
        if(!$userUuid || !$caseId || !$encounterId)
            return response()->json(['success' => 0, 'response_msg' => 'Error getting encounter instance list', 'error_msg' => 'No user id, scenario id, and/or encounter id given'], 200);

        // Get user
        $userRepo = new UserRepository(new User());
        $userId = $userRepo->getUserByUuid($userUuid);
        if(!$userId)
            return response()->json(['success' => 0, 'response_msg' => 'Error getting encounter instance list', 'error_msg' => 'No user found'], 200);

	    // Get replay data
        $replayDataRepo = new ReplayDataRepository(new ReplayData());
        $data = [
            'user_id' => $userId,
            'case_id' => $caseId,
            'encounter_id' => $encounterId,
            'start' => isset($encounterData->start_num) ? $encounterData->start_num : 0,
            'count' => isset($encounterData->batch_amount) ? $encounterData->batch_amount : 0
        ];

        if($data['count'] > 0)
            $replayData = $replayDataRepo->getByCaseAndEncounterWithCountLimit($data);
        else
            $replayData = $replayDataRepo->getByCaseAndEncounter($data);

        if(!$replayData)
            return response()->json(['success' => 0, 'response_msg' => 'Error getting replay data', 'error_msg' => 'No replay data found'], 200);

        // All good. Return list of replay data
        return response()->json(['success' => 1, 'response_msg' => 'Succes in getting replay data', 'replayData' => json_encode($replayData), 'error_msg' => null], 200);
    }

    // Return all Replay Data
    function GetReplayData(Request $request)
    {
  	$eiToken = isset($request['eiToken']) ? $request['eiToken'] : null;
        if(!$eiToken)
            return response()->json(['success' => 0, 'response_msg' => 'Error getting replay data', 'error_msg' => 'No encounter instance token given'], 200);

        // Get encounter instance
        $encounterInstanceRepo = new EncounterInstanceRepository(new EncounterInstance());
        $encounterInstance = $encounterInstanceRepo->getSingleEncounterInstanceByToken($eiToken);
	$encounterInstanceId = isset($encounterInstance->id) ? $encounterInstance->id : null;
        if(!$encounterInstanceId)
            return response()->json(['success' => 0, 'response_msg' => 'Error getting replay data', 'error_msg' => 'No encounter instance found'], 200);

        // Get replay data
        $replayDataRepo = new ReplayDataRepository(new ReplayData());
        $replayData = $replayDataRepo->getByEncounterInstanceId($encounterInstanceId);
        if(!$replayData)
            return response()->json(['success' => 0, 'response_msg' => 'Error getting replay data', 'error_msg' => 'No replay data found'], 200);

        // All good. Return replay data
        return response()->json(['success' => 1, 'response_msg' => 'Succes in getting replay data', 'replayData' => json_encode($replayData), 'error_msg' => null], 200);
    }
}


