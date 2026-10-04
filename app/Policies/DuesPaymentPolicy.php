<?php

namespace App\Policies;

use App\Models\DuesPayment;
use App\Models\User;

class DuesPaymentPolicy
{
    public function __construct(private MemberPolicy $memberPolicy) {}

    /**
     * Determine whether the user can delete a recorded payment.
     */
    public function delete(User $user, DuesPayment $duesPayment): bool
    {
        return $this->memberPolicy->manageDues($user, $duesPayment->member);
    }
}
