<?php

// app/Contracts/ChangeRequestHandlerInterface.php
namespace App\Contracts;

use App\Models\ChangeRequest;

interface ChangeRequestHandlerInterface
{
    public function handle(ChangeRequest $changeRequest): mixed;
}
