<?php
namespace App\Repositories\Backend\Scenarios;

use App\Exceptions\DatabaseException;
use \Illuminate\Database\QueryException;
use App\Repositories\BaseRepository;
use App\Models\Scenarios\Scenario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class ScenarioRepository
 */
class ScenarioRepository extends BaseRepository
{
    /**
     * ScenarioRepository constructor
     * @param Scenario $location
     */
    public function __construct(Scenario $scenario)
    {
        $this->model = $scenario;
    }

    /** Gets active scenarios and non-draft scenarios for view scenario page */
    public function getActiveScenarios($start, $length, $order_column_name, $order_sort_dir, $search_value, $filter_arr, $instructor_ids = null)
    {
        $filter_arr_length = count($filter_arr);
        $sort_by_column = $order_column_name ? true : false;

        $data = DB::table('scenarios')
            ->leftJoin('locations', 'locations.id', '=', 'scenarios.location_id')
            ->leftJoin('character_models', 'character_models.id', '=', 'scenarios.character_model_id')
            ->select('scenarios.*', 'locations.location_name', 'character_models.character_model_name')
            ->where('scenarios.active','=',1)
            ->where('scenarios.draft','=',0)
            ->when( $instructor_ids != null, function($query) use ($instructor_ids) {
                    return $query->whereIn('created_by', $instructor_ids)
                        ->orWhere('is_public', '=', 1);
            })
            ->when($search_value, function($query) use ($search_value) {
                return $query->where(fn($query2) =>
                    $query2->where('scenarios.scenario_name', 'like', '%' . $search_value . '%')
                        ->orWhere('scenarios.case_id', 'like', '%' . $search_value . '%')
                        ->orWhere('scenarios.encounter_id', 'like', '%' . $search_value . '%')
                        ->orWhere('locations.location_name', 'like', '%' . $search_value . '%')
                        ->orWhere('character_models.character_model_name', 'like', '%' . $search_value . '%')
                    );
            })
            ->when($filter_arr_length > 0, function($query) use ($filter_arr) {
                foreach($filter_arr as $filter)
                {
                    if($filter['column'] == 'location_name') {
                        $query->where('locations.location_name', '=', $filter['value']);
                    }
                    else if($filter['column'] == 'character_model_name') {
                        $query->where('character_models.character_model_name', '=', $filter['value']);
                    }
                }
            })
            ->when($sort_by_column, function($query) use ($order_column_name, $order_sort_dir) {
                $query->orderBy($order_column_name, $order_sort_dir);
            })
            ->offset($start)
            ->limit($length)
            ->get();

        $totalRecords = DB::table('scenarios')
            ->leftJoin('locations', 'locations.id', '=', 'scenarios.location_id')
            ->leftJoin('character_models', 'character_models.id', '=', 'scenarios.character_model_id')
            ->select('scenarios.*', 'locations.location_name', 'character_models.character_model_name')
            ->where('scenarios.active','=',1)
            ->where('scenarios.draft','=',0)
            ->when( $instructor_ids != null && count($instructor_ids) > 0, function($query) use ($instructor_ids) {
                    return $query->whereIn('created_by', $instructor_ids)
                        ->orWhere('is_public', '=', 1);
            })
            ->count();

        $totalRecordsWithFilter = DB::table('scenarios')
            ->leftJoin('locations', 'locations.id', '=', 'scenarios.location_id')
            ->leftJoin('character_models', 'character_models.id', '=', 'scenarios.character_model_id')
            ->select('scenarios.*', 'locations.location_name', 'character_models.character_model_name')
            ->where('scenarios.active','=',1)
            ->where('scenarios.draft','=',0)
            ->when($search_value, function($query) use ($search_value) {
                return $query->where(fn($query2) =>
                    $query2->where('scenarios.scenario_name', 'like', '%' . $search_value . '%')
                        ->orWhere('scenarios.case_id', 'like', '%' . $search_value . '%')
                        ->orWhere('scenarios.encounter_id', 'like', '%' . $search_value . '%')
                        ->orWhere('locations.location_name', 'like', '%' . $search_value . '%')
                        ->orWhere('character_models.character_model_name', 'like', '%' . $search_value . '%')
                    );
            })
            ->when($filter_arr_length > 0, function($query) use ($filter_arr) {
                foreach($filter_arr as $filter)
                {
                    if($filter['column'] == 'location_name') {
                        $query->where('locations.location_name', '=', $filter['value']);
                    }
                    else if($filter['column'] == 'character_model_name') {
                        $query->where('character_models.character_model_name', '=', $filter['value']);
                    }
                }
            })
            ->when( $instructor_ids != null && count($instructor_ids) > 0, function($query) use ($instructor_ids) {
                    return $query->whereIn('created_by', $instructor_ids)
                        ->orWhere('is_public', '=', 1);
            })
            ->count();

        $data_arr = [
            'scenarios' => $data,
            'totalRecords' => $totalRecords,
            'totalRecordsWithFilter' => $totalRecordsWithFilter
        ];
        return $data_arr;
    }

    public function changeActiveStatus($id)
    {
        try {
            $scenario = Scenario::find($id);
            if($scenario)
            {
                if($scenario->active == 1)
                    $scenario->active = 0;
                else
                    $scenario->active = 1;

                $scenario->save();
            }
        }
        catch (Exception $ex) {
            return false;
        }
        return true;
    }
}

