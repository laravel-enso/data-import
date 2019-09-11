<?php

namespace LaravelEnso\DataImport\app\Policies;

use LaravelEnso\Core\app\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use LaravelEnso\DataImport\app\Models\DataImport;

class Policy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isAdmin() || $user->isSupervisor()) {
            return true;
        }
    }

    public function view(User $user, DataImport $dataImport)
    {
        return $this->ownsDataImport($user, $dataImport);
    }
    
    public function share(User $user, DataImport $dataImport)
    {
        return $this->ownsDataImport($user, $dataImport);
    }

    public function destroy(User $user, DataImport $dataImport)
    {
        return $this->ownsDataImport($user, $dataImport);
    }

    private function ownsDataImport(User $user, DataImport $dataImport)
    {
        return $user->id === (int) $dataImport->created_by;
    }
}
