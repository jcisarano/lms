<?php

namespace App\Http\Controllers\Backend\Data;

use App\Repositories\Backend\Data\MetricDataRepository;
use App\Models\Data\MetricData;
use App\Repositories\Backend\SystemTypes\SystemTypeRepository;
use App\Models\SystemTypes\SystemType;
use App\Repositories\Backend\NLPTracking\EncounterInstanceRepository;
use App\Models\NLPTracking\Encounter\EncounterInstance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetricDataController extends Controller
{
    // called from 3rd Party Controller, not API route
    public static function RecordMetricData(Request $request, $result, $systemType) {
        $encounterInstanceToken = isset($request->encounterInstanceToken) ? $request->encounterInstanceToken : null;
        $personId = isset($request->personId) ? $request->personId: null;
        $data = isset($result) ? json_encode($result) : "";
        $systemTypeName = isset($systemType) ? $systemType : null;

        // Get encounter instance ID
        if($encounterInstanceToken) {
            $encounterInstanceRepo = new EncounterInstanceRepository(new EncounterInstance());
            $encounterInstanceId = $encounterInstanceRepo->getSingleEncounterInstanceByToken($encounterInstanceToken)->id;
        }

        // Get system type ID
        if($systemTypeName) {
            $systemTypeRepo = new SystemTypeRepository(new SystemType());
            $systemTypeId = $systemTypeRepo->GetSystemTypeByName($systemTypeName)->id;
        }

        // save metric data
        $metricData = [
            'encounter_instance_id' => isset($encounterInstanceId) ? $encounterInstanceId : 0,
            'person_id' => isset($personId) ? $personId : 0,
            'data' => isset($data) ? $data : '',
            'system_type_id' => isset($systemTypeId) ? $systemTypeId : 0
        ];

        $metricDataRepo = new MetricDataRepository(new MetricData());
        return $metricDataRepo->create($metricData);
    }
}

