<?php


namespace App\Controller\API;

use App\Exception\TransactionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BaseAPIController
 * @package App\Controller\API
 * @author Wings <eternity.mr8@gmail.com>
 */
class BaseAPIController extends AbstractController
{
    const DEFAULT_PAGE_SIZE = 25;


    /**
     * @param array $data
     * @param int $statusCode
     * @param array $groups
     * @return Response
     */
    public function createApiResponse(array $data, int $statusCode = 200, array $groups = ['Default']): Response
    {
        $json = $this->serialize($data, $groups);
        return new Response($json, $statusCode, ['Content-Type' => 'application/json']);
    }


    /**
     * @param array $data
     * @param array $groups
     * @return false|string
     */
    public function serialize(array $data, array $groups = ['Default']): ?string
    {
        return json_encode($data);
//        $context = new SerializationContext();
//        $context->setSerializeNull(true);
//        $context->setGroups($groups);
//        return $this->get('jms_serializer')->serialize($data, 'json', $context);
    }


    /**
     * @param $data
     * @param int $statusCode
     * @return Response
     */
    public function createSimpleApiResponse($data, $statusCode = 200): Response
    {
        return new Response($data, $statusCode, ['Content-Type' => 'application/json']);
    }


    /**
     * @param Request $request
     * @param FormInterface $form
     * @return void
     */
    public function processForm(Request $request, FormInterface $form):void
    {
        $data = json_decode($request->getContent(), true);
//        if ($data === null) {
//            $apiProblem = new ApiProblem(400, ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT);
//
//            throw new ApiProblemException($apiProblem);
//        }

        $clearMissing = $request->getMethod() != 'PATCH';
        $form->submit($data, $clearMissing);
    }


    /**
     * @param FormInterface $form
     * @return array
     */
    public function getErrorsFromForm(FormInterface $form): array
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if (($childForm instanceof FormInterface) && $childErrors = $this->getErrorsFromForm($childForm)) {
                $errors[$childForm->getName()] = $childErrors;
            }
        }

        return $errors;
    }


    /**
     * @param callable $callback
     * @throws TransactionException
     */
    public function executeCallableInTransaction(callable $callback):void
    {
        $em = $this->getDoctrine()->getManager();
        try {
            $callback();
            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollBack();
            throw new TransactionException($e->getMessage());
        }
    }


    /**
     * @param FormInterface $form
     * @return Response|null
     */
    public function createInvalidDataResponseIfNeeded(FormInterface $form): ?Response{
        if ($form->isSubmitted() && $form->isValid()) {
            return null;
        }
        $errors = $this->getErrorsFromForm($form);
        return $this->createApiResponse($errors, 400);
    }

}