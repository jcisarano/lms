<?php

namespace App\Http\Controllers\Backend\Scenarios;

use App\Http\Controllers\Backend\NLPTracking\IntentController;
use App\Http\Controllers\Backend\Scripts\ScriptController;
use App\Http\Controllers\Controller;
use App\Repositories\Backend\Locations\LocationRepository;
use App\Repositories\Backend\NLPTracking\IntentRepository;
use App\Models\NLPTracking\Intents\Intent;
use Illuminate\Http\Request;
use DataTables;
use App\Repositories\Backend\Scenarios\ScenarioRepository;
use App\Models\Scenarios\Scenario;
use App\Models\Locations\Location;
use App\Repositories\Backend\CharacterModels\CharacterModelRepository;
use App\Models\CharacterModel\CharacterModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Auth\User;
use App\Models\Auth\AuthToken;
use App\Repositories\Backend\Auth\UserRepository;

class ScenarioController extends Controller
{
    /**
     * @var ScenarioRepository
     */
    protected $scenarioRepo;

    /**
     * ScenarioController constructor
     *
     * @param ScenarioRepository $scenarioRepository
     */
    public function __construct(ScenarioRepository $repository)
    {
        $this->scenarioRepo = $repository;
    }

    /**
      * List of scenarios accessible by a given user. Includes public scenarios and
      * scenarios created by instructors the user is attached to.
      */
    public function getScenarioJsonListForUser($studentId) 
    {
        //$token = $request->get('auth_token');
        //$studentId = null;
        //$authToken = AuthToken::GetByToken($token);
        //if ($authToken != null)
        //{
        //    $studentId = $authToken->user_id;
        //}

        $errMsg = "";
        $success = false;
        $scenarioData = [];
        if ($studentId != null)
        {
            $student = User::find($studentId);
            $instructor_ids = [-1];
            foreach ($student->instructors as $instructor)
            {
                if($instructor->pivot->active == 1)
                    $instructor_ids[] = $instructor->id;
            }

            $repo = new ScenarioRepository(new Scenario());
            $scenarios = $repo->GetActiveScenarios(0, 9999, "", "", "", [], $instructor_ids);
            
            $output = ['scenarioData' => []];
            foreach($scenarios['scenarios'] as $scenario)
            {
                $results =  $this->getScenarioJsonFileContents($scenario->id, "/scenarios/", ".json");
                if ($results != null && $results['success'])
                    $scenarioData['scenarioData'][] = $results['data']['scenarioData'][0];
            }

            $success = true;
        }
        else
            $errMsg = "Invalid user id";

        return [
            "success" => $success,
            "error" => $errMsg,
            "scenarioData" => $scenarioData
        ];
    }
    
    /**
      * Verify user access to scenario. Expects auth token and scenario id.
      */
    public function verifyScenarioAccess(Request $request)
    {
        $token = $request->get('auth_token');
        $studentId = null;
        $authToken = AuthToken::GetByToken($token);
        if ($authToken != null)
        {
            $studentId = $authToken->user_id;
        }

        $scenarioId = null;
        $success = false;
        $hasAccess = false;
        if ($studentId != null)
        {
            $scenarioId = $request->get('scenario_id');
            $scenario = Scenario::find($scenarioId);
            if ($scenario)
            {
                $student = User::find($studentId);
                foreach ($student->instructors as $instructor)
                {
                    if ($instructor->id == $scenario->created_by 
                        && $instructor->pivot->active == 1)
                    {
                        $hasAccess = true;
                        
                    }
                }
                $success = true;
            }
        }

        return json_encode([
            "success" => $success,
            "student_id" => $studentId,
            "scenario_id" => $scenarioId,
            "has_access" => $hasAccess,
        ]);
    }
}

