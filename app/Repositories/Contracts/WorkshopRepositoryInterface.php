<?php

namespace App\Repositories\Contracts;

interface WorkshopRepositoryInterface
{
    public function getAllNewWorkhops();
    public function find($id);
    public function getPrice($workshopId);
}
