<?php

namespace App\Policies;

use App\Models\HallTicketBatch;
use App\Models\User;

class HallTicketBatchPolicy
{
    /**
     * Determine whether the user can view the batch or its status.
     */
    public function view(User $user, HallTicketBatch $batch): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        if ($user->hasRole('school-admin')) {
            return $user->school_id !== null && $batch->school_id === $user->school_id;
        }

        return false;
    }

    /**
     * Determine whether the user can download PDFs from this batch.
     */
    public function download(User $user, HallTicketBatch $batch): bool
    {
        return $this->view($user, $batch);
    }

    /**
     * Determine whether the user can retry failed parts in this batch.
     */
    public function retry(User $user, HallTicketBatch $batch): bool
    {
        return $this->view($user, $batch);
    }

    /**
     * Determine whether the user can delete this batch.
     */
    public function delete(User $user, HallTicketBatch $batch): bool
    {
        return $user->hasRole('super-admin');
    }
}
