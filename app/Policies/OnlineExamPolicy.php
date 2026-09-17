<?php

namespace App\Policies;

use App\Models\OnlineExam;
use App\Models\User;

/**
 * OnlineExamPolicy
 *
 * Enforces per-exam authorization for exam-admin users.
 *
 * Rules:
 *  - super-admin: full access to all exams (before() short-circuit)
 *  - exam-admin: can only manage exams they personally created (created_by = auth user id)
 */
class OnlineExamPolicy
{
    /**
     * Super-admins bypass all policy checks.
     */
    public function before(User $user): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return null; // defer to individual method checks
    }

    /**
     * Exam listing — any authenticated exam-admin may list exams
     * (filtering to owned exams is done at the query level in the controller).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('exam-admin');
    }

    /**
     * Viewing a specific exam — exam-admin can only view exams they created.
     */
    public function view(User $user, OnlineExam $exam): bool
    {
        return $user->hasRole('exam-admin') && (int) $exam->created_by === (int) $user->id;
    }

    /**
     * Creating a new exam.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('exam-admin');
    }

    /**
     * Updating exam settings — must own the exam.
     */
    public function update(User $user, OnlineExam $exam): bool
    {
        return $user->hasRole('exam-admin') && (int) $exam->created_by === (int) $user->id;
    }

    /**
     * Deleting an exam — must own the exam.
     */
    public function delete(User $user, OnlineExam $exam): bool
    {
        return $user->hasRole('exam-admin') && (int) $exam->created_by === (int) $user->id;
    }

    /**
     * Manage student enrollment / question bank / live monitoring.
     * Consolidated permission for all sub-resources.
     */
    public function manageSubResource(User $user, OnlineExam $exam): bool
    {
        return $user->hasRole('exam-admin') && (int) $exam->created_by === (int) $user->id;
    }
}
