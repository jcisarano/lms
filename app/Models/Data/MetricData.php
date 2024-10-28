<?php

namespace App\Models\Data;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetricData extends Model
{
    protected $fillable = [
        'encounter_instance_id',
        'person_id',
        'data',
	'system_type_id'
    ];
}

