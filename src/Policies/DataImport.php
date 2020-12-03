<?php

namespace LaravelEnso\DataImport\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use LaravelEnso\Core\Models\User;
use LaravelEnso\DataImport\Models\DataImport as Model;

class DataImport
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isAdmin() || $user->isSupervisor()) {
            return true;
        }
    }

    public function view(User $user, Model $import)
    {
        return $this->ownsDataImport($user, $import);
    }

    public function share(User $user, Model $import)
    {
        return $this->ownsDataImport($user, $import);
    }

    public function destroy(User $user, Model $import)
    {
        return $this->ownsDataImport($user, $import);
    }

    private function ownsDataImport(User $user, Model $import)
    {
        return $user->id === (int) $import->created_by;
    }
}
