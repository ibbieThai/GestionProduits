<?php

namespace App\Controller;
use App\Entity\Whishlist;
use App\Repository\ProductRepository;
use App\Repository\WhishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WhishlistController extends AbstractController
{

    private $entityManager;
    private $productRepository;
    private $whishlistRepository;

    public function __construct(EntityManagerInterface $entityManager,ProductRepository $productRepository , WhishlistRepository $whishlistRepository)
    {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
        $this->whishlistRepository = $whishlistRepository;
    }

    #[Route('/whishlist/{ProductId}/add', name: 'add_whishlist',methods: ['POST'])]
    public function addProductToWishlist(Request $request, int $productId): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], 401);
        }

        $product = $this->entityManager->getRepository(Product::class)->find($productId);
        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], 404);
        }

        $wishlist = $this->entityManager->getRepository(Wishlist::class)->findOneBy(['user' => $user]);
        if (!$wishlist) {
            $wishlist = new Whishlist();
            $wishlist->setUser($user);
            $this->entityManager->persist($wishlist);
        }

        $wishlist->addProduct($product);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Product added to wishlist'], 200);
    }

    #[Route('/whishlist', name: 'view_whishlist',methods: ['GET'])]
    public function viewWishlist(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], 401);
        }

        $wishlist = $this->entityManager->getRepository(Wishlist::class)->findOneBy(['user' => $user]);

        if (!$wishlist) {
            return new JsonResponse(['message' => 'Wishlist is empty'], 200);
        }

        $products = [];
        foreach ($wishlist->getProducts() as $product) {
            $products[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
            ];
        }

        return new JsonResponse(['products' => $products], 200);
    }

    #[Route('/whishlist/{ProductId}/remove', name: 'remove_from_whishlist', methods: ['POST'])]
    public function removeProductFromWhishlist(Request $request, int $whishlistId): JsonResponse
   {
       $user = $this->getUser();
       if (!$user) {
           return new JsonResponse(['message' => 'User not authenticated'], 401);
       }

       $wishlist = $this->whishlistRepository->find($whishlistId);
       if (!$wishlist || $wishlist->getUser() !== $user) {
           return new JsonResponse(['message' => 'wishlist not found'], 404);
       }

       $productId = $request->get('product_id');
       $product = $this->productRepository->find($productId);

       if (!$product) {
           return new JsonResponse(['message' => 'Product not found'], 404);
       }

       $wishlist->removeProduct($product);
       $this->entityManager->flush();

       return new JsonResponse(['message' => 'Product removed from wishlist']);
   }
}
