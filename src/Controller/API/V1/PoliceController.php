<?php


namespace App\Controller\API\V1;

use App\Entity\Bike;
use App\Entity\Police;
use App\Exception\TransactionException;
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
     * @Route("/", name="api_v1_polices_index", methods={"GET"})
     */
    public function index(Request $request, PoliceRepository $policeRepository, Paginator $paginator): Response
    {
        $offset = $request->query->getInt('offset', 1);
        $limit = $request->query->getInt('limit', self::DEFAULT_PAGE_SIZE);
        return $this->createApiResponse($paginator->paginate($policeRepository->findAllQuery(), $offset, $limit));
    }

    /**
     * @Route("/", name="api_v1_polices_new", methods={"POST"})
     */
    public function new(Request $request): Response
    {
        $police = new Police();
        $form = $this->createForm(PoliceType::class, $police);

        $this->processForm($request, $form);
        $invalidDataResponse = $this->createInvalidSubmittedDataResponseIfNeeded($form);
        if ($invalidDataResponse) {
            return $invalidDataResponse;
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($police);
        $em->flush();
        try {
            $this->assignTheftBikeResponsibilityIfExists($police);
        } catch (TransactionException $transactionException) {
        }
        $em->refresh($police);
        return $this->createApiResponse($police);

    }

    /**
     * @Route("/{id}", name="api_v1_polices_show", methods={"GET"})
     */
    public function show(Police $police): Response
    {
        return $this->createApiResponse($police, $statusCode = 200, $groups = ['Default', 'details']);

    }

    /**
     * @param Police $police
     * @return void
     * @throws \App\Exception\TransactionException
     */
    private function assignTheftBikeResponsibilityIfExists(Police $police): void
    {
        $em = $this->getDoctrine()->getManager();
        $bikeNeedsResponsible = $em->getRepository(Bike::class)->findOneBikeNeedsResponsible();
        if ($bikeNeedsResponsible) {
            $this->executeCallableInTransaction(static function () use ($police, $bikeNeedsResponsible) {
                $police->setIsAvailable(false);
                $bikeNeedsResponsible->setResponsible($police);
            });
        }
    }

}