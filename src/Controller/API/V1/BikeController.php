<?php


namespace App\Controller\API\V1;


use App\Entity\Bike;
use App\Entity\Police;
use App\Exception\TransactionException;
use App\Form\Filters\BikeFilterType;
use App\Repository\BikeRepository;
use App\Services\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

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
        $filterForm = $this->createForm(BikeFilterType::class);
        $this->processFilterForm($request, $filterForm);
        $filters = $filterForm->isSubmitted() && $filterForm->isValid() ? $filterForm->getData() : [];
        $query = $bikeRepository->getFilterableQuery($filters);

        $offset = $request->query->getInt('offset', 1);
        $limit = $request->query->getInt('limit', self::DEFAULT_PAGE_SIZE);
        return $this->createApiResponse($paginator->paginate($query, $offset, $limit));
    }

    /**
     * @Route("/", name="api_v1_bikes_new", methods={"POST"})
     */
    public function new(Request $request): Response
    {
        $bike = new Bike();
        $form = $this->createForm(BikeFilterType::class, $bike);
        $this->processForm($request, $form);

        $invalidDataResponse = $this->createInvalidSubmittedDataResponseIfNeeded($form);
        if ($invalidDataResponse) {
            return $invalidDataResponse;
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($bike);
        $em->flush();
        try {
            $this->assignBikeToAvailableOfficerIfExists($bike);
        } catch (TransactionException $transactionException) {
        }
        $em->refresh($bike);
        return $this->createApiResponse($bike);

    }

    /**
     * @Route("/{id}/respolve", name="api_v1_bikes_resolve", methods={"POST"})
     */
    public function resolve(Bike $bike, SerializerInterface $serializer): Response
    {
        $isNotResolvedYet = $bike->getIsResolved() !== true;
        if ($isNotResolvedYet) {
            $responsibleOfficer = $bike->getResponsible();
            $em = $this->getDoctrine()->getManager();
            $em->persist($responsibleOfficer);
            try {
                $this->executeCallableInTransaction(static function () use ($bike, $responsibleOfficer) {
                    $bike->setIsResolved(true);
                    $responsibleOfficer->setIsAvailable(true);
                });
            } catch (TransactionException $transactionException) {
            }
            $em->refresh($bike);
        }
        return $this->createApiResponse($bike);
    }


    /**
     * @param Bike $bike
     * @return void
     * @throws \App\Exception\TransactionException
     */
    private function assignBikeToAvailableOfficerIfExists(Bike $bike): void
    {
        $em = $this->getDoctrine()->getManager();
        $availableOfficer = $em->getRepository(Police::class)->findOneBy(['isAvailable' => true]);
        if ($availableOfficer) {
            $this->executeCallableInTransaction(static function () use ($bike, $availableOfficer) {
                $availableOfficer->setIsAvailable(false);
                $bike->setResponsible($availableOfficer);
            });
        }
    }

}