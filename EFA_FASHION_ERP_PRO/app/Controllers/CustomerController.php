<?php

namespace App\Controllers;

use App\Models\Customer;

class CustomerController extends ContactController
{
    protected function model(): string { return Customer::class; }
    protected function module(): string { return 'customers'; }
    protected function partyType(): string { return 'customer'; }
    protected function hasContactName(): bool { return false; }
}
