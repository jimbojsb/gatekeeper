<?php

namespace Jimbojsb\Gatekeeper\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Jimbojsb\Gatekeeper\Concerns\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    protected $guarded = [];

    public $timestamps = false;
}
