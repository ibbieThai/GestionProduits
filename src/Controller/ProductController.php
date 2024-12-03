<?php
namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends AbstractController
{
    #[Route('/addproduct', name: 'create_product', methods: ['POST'])]
    public function create(Request $request,EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser(); 
        if ($user->getUserIdentifier() !== 'admin@admin.com') {
            throw new AccessDeniedException('Access denied.');
        }

        $data = json_decode($request->getContent(), true);
        $product = new Product();
        $product->setCode($data['code'])
            ->setName($data['name'])
            ->setDescription($data['description'])
            ->setImage($data['image'])
            ->setCategory($data['category'])
            ->setPrice($data['price'])
            ->setQuantity($data['quantity'])
            ->setInternalReference($data['internalReference'])
            ->setShellId($data['shellId'])
            ->setInventoryStatus($data['inventoryStatus'])
            ->setRating($data['rating'])
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Product is created '], JsonResponse::HTTP_CREATED);
    }

    #[Route('/products', name: 'get_products', methods: ['GET'])]
    public function getAll( ProductRepository  $productRepository): JsonResponse
    {
        $products = $productRepository->findAll();
        return $this->json($products);
    }

    #[Route('/getproduct/{id}', name: 'get_product', methods: ['GET'])]
    public function get($id, ProductRepository  $productRepository): JsonResponse
    {
        $product = $productRepository->find($id);
        if (!$product) {
            return new JsonResponse(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($product);
    }

    #[Route('/updateProduct/{id}', name: 'update_product', methods: ['PATCH'])]
    public function update(Request $request, $id, ProductRepository  $productRepository,EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser(); 
        if ($user->getUserIdentifier() !== 'admin@admin.com') {
            throw new AccessDeniedException('Access denied.');
        }

        $data = json_decode($request->getContent(), true);
        $product = $productRepository->find($id);
        if (!$product) {
            return new JsonResponse(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
        $product->setName($data['name'] ?? $product->getName());
        $product->setPrice($data['price'] ?? $product->getPrice());
        $product->setQuantity($data['quantity'] ?? $product->getQuantity());
        $product->setUpdatedAt(time());

        $entityManager->flush();
        return $this->json($product);
    }

    #[Route('/deleteProduct/{id}', name: 'delete_product', methods: ['DELETE'])]  
    public function delete($id, ProductRepository  $productRepository,EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        if ($user->getUserIdentifier() !== 'admin@admin.com') {
            throw new AccessDeniedException('Access denied.');
        }

        $product = $productRepository->find($id);
        if (!$product) {
            return new JsonResponse(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Product deleted'], Response::HTTP_OK);
    }    
}
