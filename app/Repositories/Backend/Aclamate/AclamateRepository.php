<?php

namespace App\Repositories\Backend\Aclamate;

use App\Repositories\BaseRepository;
use App\Models\Aclamate\Aclamate;

/**
 * Class AclamateRepository.
 */
class AclamateRepository extends BaseRepository
{
    public function __construct(Aclamate $model)
    {
        $this->model = $model;
    }

    public function LogIn($username,$password,$rememberme = true)
    {
        return $this->model->LogIn($username,$password,$rememberme);
    }
    
    public function LogOut()
    {
        return $this->model->LogOut();
    }
    
    public function RequestMetricDataForPerson($token,$personId,$feature,$duration=10000)
    {
        return $this->model->RequestMetricDataForPerson($token,$personId,$feature,$duration);
    }
    
    public function Lifesign()
    {
        return $this->model->Lifesign();
    }
    
    public function GetSensorKitByName($token,$sensorKitName)
    {
        return $this->model->GetSensorKitByName($token,$sensorKitName);
    }
    
    public function GetAllActiveSensorkits($token)
    {
        return $this->model->GetAllActiveSensorkits($token);
    }
    
    public function GetPersonForSensorKit($token,$sensorKitName)
    {
        return $this->model->GetPersonForSensorKit($token,$sensorKitName);
    }
    
    public function SetEventForPerson($token,$personId,$eventType,$subType,$description,$timestamp,$timeoffset)
    {
        return $this->model->SetEventForPerson($token,$personId,$eventType,$subType,$description,$timestamp,$timeoffset);
    }
    
    public function GetRemoteServerTimeOffset($token)
    {
        return $this->model->GetRemoteServerTimeOffset($token);
    }
    
    public function GetPersonInfo($token,$personId)
    {
        return $this->model->GetPersonInfo($token,$personId);
    }
}
