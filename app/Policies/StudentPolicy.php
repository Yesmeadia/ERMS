<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    /**
     * Determine whether the user can generate a hall ticket for the student.
     */
    public function generateHallTicket(User $user, Student $student): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        if ($user->hasRole('school-admin')) {
            return $user->school_id !== null && $student->school_id === $user->school_id;
        }

        return false;
    }

    /**
     * Determine whether the user can download the hall ticket for the student.
     */
    public function downloadHallTicket(User $user, Student $student): bool
    {
        return $this->generateHallTicket($user, $student);
    }

    /**
     * Determine whether the user can print the hall ticket for the student.
     */
    public function printHallTicket(User $user, Student $student): bool
    {
        return $this->generateHallTicket($user, $student);
    }
}
