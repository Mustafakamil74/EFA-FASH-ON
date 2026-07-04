<?php

namespace App\Controllers;

use App\Models\Shop;

class ShopController extends ContactController
{
    protected function model(): string { return Shop::class; }
    protected function module(): string { return 'shops'; }
    protected function partyType(): string { return 'shop'; }
    protected function hasContactName(): bool { return true; }
}
