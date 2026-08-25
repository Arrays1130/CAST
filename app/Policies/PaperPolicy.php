<?php

namespace App\Policies;

use App\Models\Paper;
use App\Models\User;

class PaperPolicy
{
    public function view(User $user, Paper $paper): bool
    {
        return $user->isTeacher() || $paper->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isStudent();
    }

    public function updateFile(User $user, Paper $paper): bool
    {
        return $user->isStudent() && $paper->user_id === $user->id;
    }

    public function updateStatus(User $user, Paper $paper): bool
    {
        return $user->isTeacher();
    }

    public function updateDetails(User $user, Paper $paper): bool
    {
        return $this->view($user, $paper);
    }

    public function archive(User $user, Paper $paper): bool
    {
        return $user->isTeacher();
    }

    public function comment(User $user, Paper $paper): bool
    {
        return $this->view($user, $paper);
    }

    public function download(User $user, Paper $paper): bool
    {
        return $this->view($user, $paper);
    }
}
