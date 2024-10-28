<?php

namespace App\Models\Auth\Traits\Relationship;

use App\Models\Auth\User;
use App\Models\Scenarios\Scenario;
use Illuminate\Support\Facades\Log;

/**
 * Class InstructorStudentRelationship.
 */
trait InstructorStudentRelationship
{
    public function instructors()
    {
        return $this->belongsToMany(User::class,'instructor_student','student_id','instructor_id')->withPivot('active')->withTimestamps();
    }

    public function students()
    {
        return $this->belongsToMany(User::class,'instructor_student','instructor_id','student_id')->withPivot('active')->withTimestamps();
    }

    public function addInstructor($instructorId)
    {
        $this->instructors()->syncWithoutDetaching([$instructorId]);
    }

    public function removeInstructor($instructorId)
    {
        $this->instructors()->detach($instructorId);
    }

    public function addStudent($studentId)
    {
        $this->students()->syncWithoutDetaching([$studentId]);
    }

    public function removeStudent($studentId)
    {
        $this->students()->detach($studentId);
    }

    public function hasStudent($studentId)
    {
        $student = $this->students()->where('student_id', $studentId)->first();
        if ($student) {
            return true;
        }

        return false;
    }

    public function hasInstructor($instructorId)
    {
        $instructor = $this->instructors()->where('instructor_id', $instructorId)->first();
        if ($instructor) {
            return true;
        }

        return false;
    }

    public function setAccessForStudent(int $studentId, int $canAccess)
    {
        $this->students()->updateExistingPivot($studentId, [
            'active' => $canAccess
        ]);
    }

    public function canAccessScenario($scenarioId)
    {
        $scenario = Scenario::find($scenarioId);
        if ($scenario != null)
        {
            return $this->hasInstructor($scenario->created_by);
        }

        return false;
    }
}
