<?php

namespace LaravelEnso\DataImport\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\Users\Models\User;

class Policy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isAdmin() || $user->isSupervisor()) {
            return true;
        }
    }

    public function view(User $user, Import $import)
    {
        return $this->ownsDataImport($user, $import);
    }

    public function share(User $user, Import $import)
    {
        return $this->ownsDataImport($user, $import);
    }

    public function destroy(User $user, Import $import)
    {
        return $this->ownsDataImport($user, $import);
    }

    private function ownsDataImport(User $user, Import $import)
    {
        return $user->id === (int) $import->created_by;
    }
}
