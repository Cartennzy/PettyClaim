<?php

namespace App\Repositories\Interfaces;

use App\Models\Claim;
use Illuminate\Support\Collection;

interface ClaimRepositoryInterface
{
    public function create(array $data): Claim;
    public function getPendingClaims(): Collection;
    public function updateStatus(string $id, string $status, ?string $note = null): Claim;
}