<?php


namespace App\Controller\API\V1;

use App\Entity\Bike;
use App\Entity\Police;
use App\Form\PoliceType;
use App\Repository\PoliceRepository;
use App\Services\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(Request $request, PoliceRepository $policeRepository, Paginator $paginator){
        $offset = $request->query->getInt('offset', 1);
        $limit = $request->query->getInt('limit', self::DEFAULT_PAGE_SIZE);
        return $this->createApiResponse($paginator->paginate($policeRepository->findAllQuery(), $offset, $limit));
    }

    /**
     * @Route("/", name="api_v1_polices_new", methods={"POST"})
     */
    public function new(Request $request){
        $police = new Police();
        $form = $this->createForm(PoliceType::class, $police);
        $this->processForm($request, $form);
        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction(); // suspend auto-commit
            try {
                $bikeNeedsResponsible = $em->getRepository(Bike::class)->findOneBikeNeedsResponsible();
                if($bikeNeedsResponsible){
                    $police->setIsAvailable(false);
                    $bikeNeedsResponsible->setResponsible($police);
                }

                $em->persist($police);
                $em->flush();
                $em->getConnection()->commit();
                $em->refresh($police);
                return $this->createApiResponse($police);
            } catch (\Exception $e) {
                $em->getConnection()->rollBack();
                return $this->createApiResponse($police, 400);
            }
        }else{
            $errors = $this->getErrorsFromForm($form);
            return $this->createApiResponse($errors, 400);
        }

    }
}