<?php

namespace App\Controller;

use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    private $cartRepository;
    private $productRepository;
    private $entityManager;

    public function __construct(CartRepository $cartRepository, ProductRepository $productRepository, EntityManagerInterface $entityManager)
    {
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/cart', name: 'get_cart', methods: ['GET'])]
    public function getCart(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'User not authenticated'], 401);
        }

        $cart = $this->cartRepository->findOneBy(['user' => $user]);

        if (!$cart) {
            return new JsonResponse(['message' => 'Cart not found'], 404);
        }

        $products = [];
        foreach ($cart->getProducts() as $product) {
            $products[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'quantity' => $product->getQuantity(), 
            ];
        }

        return new JsonResponse(['cart' => $products]);
    }

    #[Route('/cart/{cartId}/add', name: 'add_to_cart', methods: ['POST'])]
     public function addToCart(Request $request, int $cartId): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'User not authenticated'], 401);
        }

        $cart = $this->cartRepository->find($cartId);
        if (!$cart || $cart->getUser() !== $user) {
            return new JsonResponse(['message' => 'Cart not found'], 404);
        }

        $productId = $request->get('product_id');
        $product = $this->productRepository->find($productId);

        if (!$product) {
            return new JsonResponse(['message' => 'Product not found'], 404);
        }

        $cart->addProduct($product);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'add to cart', 'product' => $product->getName()]);
    }

    #[Route('/cart/{cartId}/remove', name: 'remove_from_cart', methods: ['POST'])]
     public function removeFromCart(Request $request, int $cartId): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'User not authenticated'], 401);
        }

        $cart = $this->cartRepository->find($cartId);
        if (!$cart || $cart->getUser() !== $user) {
            return new JsonResponse(['message' => 'Cart not found'], 404);
        }

        $productId = $request->get('product_id');
        $product = $this->productRepository->find($productId);

        if (!$product) {
            return new JsonResponse(['message' => 'Product not found'], 404);
        }

        $cart->removeProduct($product);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Product removed from cart']);
    }
}
