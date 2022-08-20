<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ApiResource(
    collectionOperations: [],
    itemOperations: [
        'get' => [
            'openapi_context' => [
                'parameters' => [
                    [
                        'in' => 'query',
                        'name' => 'currency',
                        'type' => 'string',
                        'enum' => ['USD', 'EUR'],
                        'description' => 'The currency in which you wish to get the product price (Only USD and EUR are accepted) .',
                    ],
                ]
            ]
        ]
    ],
    normalizationContext: ['groups' => ['product:read']],
    denormalizationContext: ['groups' => ['product:write']],
)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:read', 'product:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotBlank]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 3)]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotBlank]
    private ?string $price = null;

    #[ORM\ManyToOne(inversedBy: 'products', cascade: ['persist'])]
    #[Groups(['product:read', 'product:write'])]
    private ?Category $category = null;

    /**
     * Get product id.
     *
     * @return integer|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get product name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set product name.
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get product quantity.
     *
     * @return integer|null
     */
    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    /**
     * Set product quantity.
     *
     * @param integer|null $quantity
     * @return self
     */
    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get product price.
     *
     * @return string|null
     */
    public function getPrice(): ?string
    {
        return $this->price;
    }

    /**
     * Set product price.
     *
     * @param string $price
     * @return self
     */
    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get product category.
     *
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Set product quantity.
     *
     * @param Category|null $category
     * @return self
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Convert price using currency exchange rate.
     *
     * @param float $exchangeRate
     * @return void
     */
    public function setPriceWithRate(float $exchangeRate): void
    {
        $convertedPriceValue = (float)$this->getPrice() * $exchangeRate;
        $integer = (int)($convertedPriceValue);
        $fraction = ceil((($convertedPriceValue - $integer) * 1000) / 250) * 250 ;
        $this->setPrice($integer . '.' . $fraction);
    }
}
