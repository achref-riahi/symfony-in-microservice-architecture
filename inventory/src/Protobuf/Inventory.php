<?php

namespace App\Protobuf;

use App\Entity\Product;
use App\Entity\Category;
use Spiral\RoadRunner\GRPC;
use App\Protobuf\GRPCHelper;
use Doctrine\Persistence\ManagerRegistry;
use App\Protobuf\Generated\InventoryInterface;
use App\Protobuf\Generated\GetCategoriesResponse;
use App\Protobuf\Generated\GetProductByIdRequest;
use App\Protobuf\Generated\GetCategoryByIdRequest;
use App\Protobuf\Generated\GetProductByIdResponse;
use App\Protobuf\Generated\GetCategoryByIdResponse;
use App\Protobuf\Generated\Product as ProductMessage;
use App\Protobuf\Generated\Category as CategoryMessage;

class Inventory implements InventoryInterface
{
    /** @var ManagerRegistry */
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @inheritDoc
     */
    public function GetProductById(GRPC\ContextInterface $ctx, GetProductByIdRequest $in): GetProductByIdResponse
    {
        /** @var Product */
        $product = $this->doctrine->getRepository(Product::class)->find($in->getId());
        if ($product == null) {
            throw new GRPC\Exception\GRPCException(
                "Invalid product id.",
                GRPC\StatusCode::INVALID_ARGUMENT
            );
        }
        $categoryMessageArray = new CategoryMessage(GRPCHelper::messageParser([
            'id' => $product->getCategory()?->getId(),
            'name' => $product->getCategory()?->getName(),
        ]));
        $productMessageArray = GRPCHelper::messageParser([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'quantity' => $product->getQuantity(),
            'category' => $categoryMessageArray
        ]);
        return new GetProductByIdResponse([
            'product' => new ProductMessage($productMessageArray)
        ]);
    }

    /**
     * @inheritDoc
     */
    public function GetCategoryById(GRPC\ContextInterface $ctx, GetCategoryByIdRequest $in): GetCategoryByIdResponse
    {
        /** @var Category */
        $category = $this->doctrine->getRepository(Category::class)->find($in->getId());
        if ($category == null) {
            throw new GRPC\Exception\GRPCException(
                "Invalid category id.",
                GRPC\StatusCode::INVALID_ARGUMENT
            );
        }
        $productsMessageArray = array_map(
            fn ($product) => new ProductMessage(
                GRPCHelper::messageParser([
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'quantity' => $product->getQuantity()
                ])
            ),
            $category->getProducts()->toArray()
        );
        return new GetCategoryByIdResponse([
            'category' => new CategoryMessage(
                GRPCHelper::messageParser([
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'products' => $productsMessageArray
                ])
            )
        ]);
    }

    /**
     * @inheritDoc
     */
    public function GetCategories(GRPC\ContextInterface $ctx, \Google\Protobuf\GPBEmpty $in): GetCategoriesResponse
    {
        /** @var array<Category> */
        $categories = $this->doctrine->getRepository(Category::class)->findAll();
        return new GetCategoriesResponse(
            [
                'categories' => array_map(fn ($category) => new CategoryMessage([
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                ]), $categories)
            ]
        );
    }
}
