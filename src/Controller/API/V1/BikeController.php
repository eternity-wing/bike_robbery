<?php


namespace App\Controller\API\V1;


use App\Entity\Bike;
use App\Entity\Police;
use App\Exception\TransactionException;
use App\Factory\BikeFactory;
use App\Form\Filters\BikeFilterType;
use App\Repository\BikeRepository;
use App\Services\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BikeController
 * @package App\Controller\API\V1
 * @author Wings <eternity.mr8@gmail.com>
 *
 * @Route("/api/v1/bikes")
 */
class BikeController extends BaseController
{
    /**
     * @Route("/", name="api_v1_bikes_index", methods={"GET"})
     */
    public function index(Request $request, BikeRepository $bikeRepository, Paginator $paginator): Response
    {
        $filterForm = $this->createForm(BikeFilterType::class, null, ['allow_extra_fields' => true]);
        $response = $this->validateRequest($filterForm, $request);
        if($response !== null){
            return $response;
        }
        $query = $bikeRepository->getFilterableQuery($filterForm->getData());
        [$offset, $limit] = $this->extractDefaultFilters($request);
        return $this->createApiResponse($paginator->paginate($query, $offset, $limit));
    }

    /**
     * @Route("/", name="api_v1_bikes_new", methods={"POST"})
     */
    public function new(Request $request, BikeFactory $bikeFactory): Response
    {
        $bike = new Bike();
        $form = $this->createForm(BikeFilterType::class, $bike);
        $response = $this->validateRequest($form, $request);
        if($response !== null){
            return $response;
        }
        $bikeFactory->store($bike);
        $bikeFactory->assignResponsible($bike, null);
        return $this->createApiResponse($bike, 201);
    }

    /**
     * @Route("/", name="api_v1_bikes_edit", methods={"PUT", "PATCH"})
     */
    public function edit(Bike $bike, Request $request, BikeFactory $bikeFactory): Response
    {
        $form = $this->createForm(BikeFilterType::class, $bike);
        $response = $this->validateRequest($form, $request);
        if($response !== null){
            return $response;
        }
        $bikeFactory->store($bike);
        $bikeFactory->assignResponsible($bike, null);
        return $this->createApiResponse($bike, 201);
    }


    /**
     * @Route("/{id}/respolve", name="api_v1_bikes_resolve", methods={"POST"})
     */
    public function resolve(Bike $bike, BikeFactory $bikeFactory): Response
    {
        $bikeFactory->resolve($bike);
        return $this->createApiResponse($bike);
    }

    /**
     * @Route("/{id}", name="api_v1_bikes_delete", methods={"DELETE"})
     */
    public function delete(Bike $bike, BikeFactory $bikeFactory): Response
    {
        $bikeFactory->delete($bike);
        return $this->createApiResponse([]);
    }


    /**
     * @Route("/{id}", name="api_v1_bikes_show", methods={"GET"})
     */
    public function show(Bike $bike): Response
    {
        return $this->createApiResponse($bike);
    }

}