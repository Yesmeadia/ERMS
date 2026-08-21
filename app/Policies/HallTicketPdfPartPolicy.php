<?php

namespace App\Policies;

use App\Models\HallTicketPdfPart;
use App\Models\User;

class HallTicketPdfPartPolicy
{
    /**
     * Determine whether the user can view or download the PDF part.
     */
    public function view(User $user, HallTicketPdfPart $part): bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        if ($user->hasRole('school-admin')) {
            $batch = $part->batch;
            return $batch !== null && $user->school_id !== null && $batch->school_id === $user->school_id;
        }

        return false;
    }

    /**
     * Determine whether the user can download this PDF part.
     */
    public function download(User $user, HallTicketPdfPart $part): bool
    {
        return $this->view($user, $part);
    }
}
