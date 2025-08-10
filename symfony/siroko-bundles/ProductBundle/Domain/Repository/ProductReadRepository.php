<?php

namespace ProductBundle\Domain\Repository;

interface ProductReadRepository
{
    public function findAll(): array;
}
