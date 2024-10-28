<?php

namespace App\Http\Controllers\Backend\Aclamate;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Backend\Aclamate\AclamateRepository;
use App\Http\Controllers\Backend\NLPTracking\EventController;
use App\Http\Controllers\Backend\Data\MetricDataController;

use Illuminate\Support\Facades\Log;

class AclamateController extends Controller
{
    protected $aclamateRepository;
    const SYSTEM_TYPE = 'ACLAMATE';
    
    public function __construct(AclamateRepository $aclamateRepository)
    {
        $this->aclamateRepository = $aclamateRepository;
    }
    
    public function LogIn(Request $request)
    {        
        $uname  = AclamateController::RetrieveAndCleanUpInput($request,'username');
        $pwd    = AclamateController::RetrieveAndCleanUpInput($request,'password');
        
        $result = $this->aclamateRepository->LogIn($uname,$pwd);
        return response()->json( $result );
    }
    
    public function LogOut()
    {
        $result = $this->aclamateRepository->LogOut();
        return response()->json( $result );
    }
    
    public function RequestMetricDataForPerson(Request $request)
    {
        $personId = AclamateController::RetrieveAndCleanUpInput($request,'personId');
        $feature  = AclamateController::RetrieveAndCleanUpInput($request,'feature');
        $duration = AclamateController::RetrieveAndCleanUpInput($request,'duration');
        $token    = AclamateController::RetrieveAndCleanUpInput($request,'token');
	$encounterInstanceToken = AclamateController::RetrieveAndCleanUpInput($request, 'encounterInstanceToken');
        
        $result = $this->aclamateRepository->RequestMetricDataForPerson($token,$personId,$feature,$duration);

	// save Metric Data to Database
        $recordData = MetricDataController::RecordMetricData($request, $result, self::SYSTEM_TYPE);

        return response()->json( $result );
    }
    
    public function Lifesign()
    {
        $result = $this->aclamateRepository->Lifesign();
        return response()->json( $result );
    }
    
    public function RequestSensorKit(Request $request)
    {
        $token = AclamateController::RetrieveAndCleanUpInput($request,'token');
        $name  = AclamateController::RetrieveAndCleanUpInput($request,'sensorkitname');
        
        $result = $this->aclamateRepository->GetSensorKitByName($token,$name);
        return response()->json( $result );
    }
    
    public function RequestAllActiveSensorkits(Request $request)
    {
        $token = AclamateController::RetrieveAndCleanUpInput($request,'token');
        $result = $this->aclamateRepository->GetAllActiveSensorkits($token);
        return response()->json( $result );        
    }
        
    public function RequestPersonForSensorKit(Request $request)
    {
        $token = AclamateController::RetrieveAndCleanUpInput($request,'token');
        $name  = AclamateController::RetrieveAndCleanUpInput($request, 'sensorkitname');
        
        $result = $this->aclamateRepository->GetPersonForSensorKit($token,$name);
        return response()->json( $result );
    }
    
    public function SetEventForPerson(Request $request)
    {       
        $token          = AclamateController::RetrieveAndCleanUpInput($request,'token');  
        $personId       = AclamateController::RetrieveAndCleanUpInput($request,'personId');
        $timestamp      = AclamateController::RetrieveAndCleanUpInput($request,'timestamp');
        $timeOffset     = AclamateController::RetrieveAndCleanUpInput($request,'timeoffset');
        $eventType      = AclamateController::RetrieveAndCleanUpInput($request,'eventType');
        $description    = AclamateController::RetrieveAndCleanUpInput($request,'eventDescription');
        $subType        = AclamateController::RetrieveAndCleanUpInput($request,'eventSubtype');

        // save event data to our database
        EventController::RecordEventData($request, self::SYSTEM_TYPE);

        $result = $this->aclamateRepository->SetEventForPerson($token,$personId,$eventType,$subType,$description,$timestamp,$timeOffset);
        return response()->json( $result );
    }
    
    public function GetRemoteServerTimeOffset(Request $request)
    {
        $token = AclamateController::RetrieveAndCleanUpInput($request,'token'); 
        return $this->aclamateRepository->GetRemoteServerTimeOffset($token); 
    }
    
    public function GetPersonInfo(Request $request)
    {
        $token       = AclamateController::RetrieveAndCleanUpInput($request,'token');  
        $personId    = AclamateController::RetrieveAndCleanUpInput($request,'personId');
        return $this->aclamateRepository->GetPersonInfo($token,$personId); 
    }

    public function RetrieveAndCleanUpInput(Request $request, $name)
    {
        //strips hidden chars from string, e.g. EOL, %E2%80%8B
        if($request->has($name))
        {
            //strips hidden chars from string, e.g. EOL, %E2%80%8B
            return preg_replace('/\p{C}+/u', "", $request->input($name));
        }
        
        return "";
    }
}
