<?php


namespace App\Controller\API;

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


    public function createApiResponse($data, $statusCode = 200, $groups = ['Default'])
    {
        $json = $this->serialize($data, $groups);
        return new Response($json, $statusCode, ['Content-Type' => 'application/json']);
    }

    public function serialize($data, $groups = ['Default'])
    {
        return json_encode($data);
//        $context = new SerializationContext();
//        $context->setSerializeNull(true);
//        $context->setGroups($groups);
//        return $this->get('jms_serializer')->serialize($data, 'json', $context);
    }

    public function createSimpleApiResponse($data, $statusCode = 200)
    {
        return new Response($data, $statusCode, ['Content-Type' => 'application/json']);
    }

    public function processForm(Request $request, FormInterface $form) {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
//            $apiProblem = new ApiProblem(400, ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT);
//
//            throw new ApiProblemException($apiProblem);
        }

        $clearMissing = $request->getMethod() != 'PATCH';
        $form->submit($data, $clearMissing);
    }

    public function getErrorsFromForm(FormInterface $form) {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }



}