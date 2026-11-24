<?php

namespace Jimbojsb\Gatekeeper\Tests\Fixtures;

enum Role: string
{
    case SuperAdmin = 'super-admin';
    case Admin = 'admin';
    case Editor = 'editor';
    case Writer = 'writer';
}
