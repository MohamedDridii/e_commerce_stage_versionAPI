<?php
namespace App\Services;

use Symfony\Component\HttpFoundation\RequestStack;

class BucketSession
{
    private $requestStack;
    private const SESSION_KEY = 'bucket';

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getBucket(): array
    {
        return $this->requestStack->getSession()->get(self::SESSION_KEY, []);
    }

    public function setBucket(array $bucket): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, $bucket);
    }

    public function addProductToBucket(int $productId, int $quantity = 1): void
    {
        $bucket = $this->getBucket();
        
        if (isset($bucket[$productId])) {
            $bucket[$productId] += $quantity;
        } else {
            $bucket[$productId] = $quantity;
        }
        
        $this->setBucket($bucket);
    }

    public function removeProduct(int $productId): void
    {
        $bucket = $this->getBucket();
        unset($bucket[$productId]);
        $this->setBucket($bucket);
    }

    public function updateQuantity(int $productId, int $quantity): void
    {
        $bucket = $this->getBucket();
        $bucket[$productId] = $quantity;
        $this->setBucket($bucket);
    }

    public function clear(): void
    {
        $this->setBucket([]);
    }

    public function countProducts(): int
    {
        return array_sum($this->getBucket());
    }
}