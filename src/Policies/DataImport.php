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

    public function view(User $user, Model $dataImport)
    {
        return $this->ownsDataImport($user, $dataImport);
    }

    public function share(User $user, Model $dataImport)
    {
        return $this->ownsDataImport($user, $dataImport);
    }

    public function destroy(User $user, Model $dataImport)
    {
        return $this->ownsDataImport($user, $dataImport);
    }

    private function ownsDataImport(User $user, Model $dataImport)
    {
        return $user->id === (int) $dataImport->created_by;
    }
}
