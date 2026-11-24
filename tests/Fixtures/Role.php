<?php

namespace Jimbojsb\Gatekeeper\Tests\Fixtures;

enum Role: string
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Writer = 'writer';
}
