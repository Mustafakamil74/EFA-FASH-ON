<?php

namespace App\Controllers;

use App\Models\Factory;

class FactoryController extends ContactController
{
    protected function model(): string { return Factory::class; }
    protected function module(): string { return 'factories'; }
    protected function partyType(): string { return 'factory'; }
    protected function hasContactName(): bool { return true; }
}
