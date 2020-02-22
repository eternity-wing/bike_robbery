<?php


namespace App\Controller\API\V1;


use App\Entity\Bike;
use App\Entity\Police;
use App\Form\BikeType;
use App\Repository\BikeRepository;
use App\Services\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(Request $request, BikeRepository $bikeRepository, Paginator $paginator)
    {
        $queryParameters = $request->query->all();
        $filters = [];
        $query = $bikeRepository->getFilterableQuery($filters);

        $offset = $request->query->getInt('offset', 1);
        $limit = $request->query->getInt('limit', self::DEFAULT_PAGE_SIZE);
        return $this->createApiResponse($paginator->paginate($query, $offset, $limit));
    }

    /**
     * @Route("/", name="api_v1_bikes_new", methods={"POST"})
     */
    public function new(Request $request)
    {
        $bike = new Bike();
        $form = $this->createForm(BikeType::class, $bike);
        $this->processForm($request, $form);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($bike);
            $em->flush();
            $availableOfficer = $em->getRepository(Police::class)->findOneBy(['isAvailable' => true]);
            if ($availableOfficer) {
                $em->getConnection()->beginTransaction(); // suspend auto-commit
                try {
                    $availableOfficer->setIsAvailable(false);
                    $bike->setResponsible($availableOfficer);
                    $em->getConnection()->commit();
                    $em->refresh($bike);
                    return $this->createApiResponse($bike);
                } catch (\Exception $e) {
                    $em->getConnection()->rollBack();
                    return $this->createApiResponse($bike, 400);
                }
            }
        } else {
            $errors = $this->getErrorsFromForm($form);
            return $this->createApiResponse($errors, 400);
        }

    }

    /**
     * @Route("/{id}/respolve", name="api_v1_bikes_resolve", methods={"POST"})
     */
    public function resolve(Bike $bike)
    {
        $isNotResolvedYet = $bike->getIsResolved() != true;
        if ($isNotResolvedYet) {
            $bike->setIsResolved(true);
            $responsibleOfficer = $bike->getResponsible();
            $responsibleOfficer->setIsAvailable(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($responsibleOfficer);
            $em->flush();
        }
        return $this->createApiResponse($bike);
    }

}