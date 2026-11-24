<?php

namespace Jimbojsb\Gatekeeper\Tests\Fixtures;

enum Permission: string
{
    case ManagePosts = 'manage-posts';
    case ManageUsers = 'manage-users';
    case WriteDrafts = 'write-drafts';
}
