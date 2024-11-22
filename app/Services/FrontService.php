<?php

namespace App\Services;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\WorkshopRepositoryInterface;

class FrontService
{
    protected $categotyRepository;
    protected $workshopRepository;

    public function __construct(
        CategoryRepositoryInterface $categotyRepository,
        WorkshopRepositoryInterface $workshopRepository
    ) {
        $this->categotyRepository = $categotyRepository;
        $this->workshopRepository = $workshopRepository;
    }

    public function getFrontPageData(): array
    {
        $categories = $this->categotyRepository->getAllCategories();
        $newWorkshops = $this->workshopRepository->getAllNewWorkhops();

        return compact('categories', 'newWorkshops');
    }
}
