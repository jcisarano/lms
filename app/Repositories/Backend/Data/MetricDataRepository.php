<?php
namespace App\Repositories\Backend\Data;

use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use App\Models\Data\MetricData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class MetricDataRepository.
 */
class MetricDataRepository extends BaseRepository
{
    /**
     * EventRepository constructor.
     *
     * @param  MetricData  $model
     */
    public function __construct(MetricData $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     * @throws \Throwable
     * @return MetricData
     */
    public function create(array $data) : MetricData
    {
        return DB::transaction(function () use ($data) {
            $item = $this->model::create([
		        'encounter_instance_id' => $data['encounter_instance_id'],
                'person_id' => $data['person_id'],
                'data' => $data['data'],
                'system_type_id' => $data['system_type_id']
            ]);

            if ($item) {
                return $item;
            }

            throw new GeneralException(__('exceptions.backend.events.create_error'));
        });
    }
}

