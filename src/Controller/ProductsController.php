<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Products;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/api/v1/products')]
class ProductsController extends AbstractController
{
    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'api_products_list', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $products = $this->entityManager->getRepository(Products::class)->findAll();

        if (empty($products)) {
            return $this->json(['message' => 'No products found'], Response::HTTP_NOT_FOUND);
        }

        // Generate an ETag based on the content of the products
        $etag = md5(serialize($products));

        $lastModified = new \DateTime();
        foreach ($products as $product) {
            $productLastModified = $product->getCreatedAt();
            if ($productLastModified < $lastModified) {
                $lastModified = $productLastModified;
            }
        }

        // Set cache headers
        $response = $this->json($products, Response::HTTP_OK);
        $response->setEtag($etag);
        $response->setLastModified($lastModified);
        $response->headers->set('Cache-Control', 'max-age=3600, public'); // Cache for 1 hour
        $response->isNotModified($request);

        return $response;
    }

    #[Route('', name: 'api_products_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['message' => 'Invalid JSON format'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $product = new Products();
        $product->setProductName($data['productName'] ?? '');
        $product->setProductprice($data['productprice'] ?? '0');  // Default to '0' if missing
        $product->setProductdescription($data['productdescription'] ?? '');
        $product->setCreatedAt(new \DateTime());

        $errors = $this->validator->validate($product);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new JsonResponse(['errors' => $errorsString], JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $serializedProduct = $this->serializer->serialize($product, 'json');

        return new JsonResponse([
            'message' => 'Product created successfully',
            'product' => json_decode($serializedProduct)
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_products_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $product = $this->entityManager->getRepository(Products::class)->find($id);

        if (!$product) {
            return $this->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($product, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'api_products_update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = $this->entityManager->getRepository(Products::class)->find($id);

        if (!$product) {
            return $this->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['message' => 'Invalid JSON format'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $product->setProductName($data['productName'] ?? $product->getProductName());
        $product->setProductprice($data['productprice'] ?? $product->getProductprice());
        $product->setProductdescription($data['productdescription'] ?? $product->getProductdescription());

        // Validate the entity
        $errors = $this->validator->validate($product);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new JsonResponse(['errors' => $errorsString], JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();


        $serializedProduct = $this->serializer->serialize($product, 'json');

        return new JsonResponse([
            'message' => 'Product Updated successfully',
            'product' => json_decode($serializedProduct)
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/{id}', name: 'api_products_partial_update', methods: ['PATCH'])]
    public function partialUpdate(Request $request, int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = $this->entityManager->getRepository(Products::class)->find($id);

        if (!$product) {
            return $this->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['message' => 'Invalid JSON format'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Update only the fields provided in the request
        if (isset($data['productName'])) {
            $product->setProductName($data['productName']);
        }
        if (isset($data['productprice'])) {
            $product->setProductprice($data['productprice']);
        }
        if (isset($data['productdescription'])) {
            $product->setProductdescription($data['productdescription']);
        }

        $errors = $this->validator->validate($product);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new JsonResponse(['errors' => $errorsString], JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->entityManager->flush();

        $serializedProduct = $this->serializer->serialize($product, 'json');

        return new JsonResponse([
            'message' => 'Product updated successfully',
            'product' => json_decode($serializedProduct)
        ], JsonResponse::HTTP_OK);
    }


    #[Route('/{id}', name: 'api_products_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = $this->entityManager->getRepository(Products::class)->find($id);

        if (!$product) {
            return $this->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Product deleted successfully',
        ], Response::HTTP_OK);
    }
}
