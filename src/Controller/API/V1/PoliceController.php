<?php


namespace App\Controller\API\V1;

use App\Entity\Police;
use App\Factory\PoliceFactory;
use App\Form\PoliceType;
use App\Repository\PoliceRepository;
use App\Services\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PoliceController
 * @package App\Controller\API\V1
 * @author Wings <eternity.mr8@gmail.com>
 *
 * @Route("/api/v1/polices")
 */
class PoliceController extends BaseController
{
    /**
     * @Route("", name="api_v1_polices_index", methods={"GET"})
     */
    public function index(Request $request, PoliceRepository $policeRepository, Paginator $paginator): Response
    {
        [$offset, $limit] = $this->extractDefaultFilters($request);
        return $this->createApiResponse($paginator->paginate($policeRepository->findAllQuery(), $offset, $limit));
    }

    /**
     * @Route("", name="api_v1_polices_new", methods={"POST"})
     */
    public function new(Request $request, PoliceFactory $policeFactory): Response
    {
        $police = new Police();
        $form = $this->createForm(PoliceType::class, $police);

        $response = $this->validateJsonRequest($form, $request);
        if ($response !== null) {
            return $response;
        }
        $policeFactory->store($police);
        $policeFactory->assignResponsibility($police, null);
        return $this->createApiResponse($police, 201);
    }

    /**
     * @Route("/{id}", name="api_v1_polices_show", methods={"GET"})
     *
     */
    public function show(Police $police): Response
    {
        return $this->createApiResponse($police, 200, ['Default', 'details']);
    }

    /**
     * @Route("/{id}", name="api_v1_polices_edit", methods={"PUT", "PATCH"})
     */
    public function edit(Police $police, Request $request, PoliceFactory $policeFactory): Response
    {
        $form = $this->createForm(PoliceType::class, $police);
        $response = $this->validateJsonRequest($form, $request);
        if ($response !== null) {
            return $response;
        }
        $policeFactory->store($police);
        $policeFactory->assignResponsibility($police, null);
        return $this->createApiResponse($police);
    }


    /**
     * @Route("/{id}", name="api_v1_polices_delete", methods={"DELETE"})
     */
    public function delete(Police $police, PoliceFactory $policeFactory): Response
    {
        $policeFactory->delete($police);
        return $this->createApiResponse([]);
    }
}
