<?php

namespace App\Http\Controllers\Backend\Scenarios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Backend\Scenarios\ScenarioSeatRepository;
use Illuminate\Support\Facades\Log;
use App\Models\Auth\User;

class ScenarioSeatController extends Controller
{
    protected $ScenarioSeatRepository;

    public function __construct(ScenarioSeatRepository $repository)
    {
        $this->ScenarioSeatRepository = $repository;
    }

    public function Index(Request $request)
    {
        $instructor = auth()->user();
        $seatData = ScenarioSeatRepository::SeatDataForInstructor($instructor);
        $seatsAvailable = $seatData["free_seats"];
        $totalSeats = $seatData["total_seats"];
        $nextBillDate = "11/11/11";
        $nextBillAmt = "11.11";
        $vars = [ "freeSeats" => $seatsAvailable,
                    "totalSeats" => $totalSeats,
                    "hideButton" => true,
                    "nextBillDate" => $nextBillDate,
                    "nextBillAmount" => $nextBillAmt,
                ];

        return view("backend.instructors.seat_index", $vars);
    }
}