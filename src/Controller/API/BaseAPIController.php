<?php


namespace App\Controller\API;

use App\Exception\TransactionException;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Serializer;

/**
 * Class BaseAPIController
 * @package App\Controller\API
 * @author Wings <eternity.mr8@gmail.com>
 */
class BaseAPIController extends AbstractController
{
    /**
     * @const int page size
     */
    const DEFAULT_PAGE_SIZE = 25;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * BaseAPIController constructor.
     */
    public function __construct()
    {
        $this->serializer = SerializerBuilder::create()->build();
    }

    /**
     * @return Serializer
     */
    public function getSerializer(): Serializer
    {
        return $this->serializer;
    }

    /**
     * @param mixed $data Any data
     * @param int $statusCode
     * @param array $groups
     * @return Response
     */
    public function createApiResponse($data, int $statusCode = 200, array $groups = ['Default']): Response
    {
        $json = $this->serialize($data, $groups);
        return new Response($json, $statusCode, ['Content-Type' => 'application/json']);
    }


    /**
     * @param mixed $data Any data
     * @param array $groups
     * @return string
     */
    public function serialize($data, $groups = ['Default'])
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups($groups);
        return $this->getSerializer()->serialize($data, 'json', $context);
    }

    /**
     * @param Request $request
     * @param FormInterface $form
     * @return void
     */
    public function processForm(Request $request, FormInterface $form): void
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
     * @param Request $request
     * @param FormInterface $filterForm
     * @return void
     */
    public function processFilterForm(Request $request, FormInterface $filterForm): void
    {
        $formFields = array_keys($filterForm->all());
        $queryParameters = $request->query->all();

        $rawFilters = array_filter($queryParameters, static function ($value, $key) use ($formFields) {
            return in_array($key, $formFields, true);
        }, ARRAY_FILTER_USE_BOTH);

        $clearMissing = $request->getMethod() != 'PATCH';
        $filterForm->submit($rawFilters, $clearMissing);
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
    public function executeCallableInTransaction(callable $callback): void
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
    public function createInvalidSubmittedDataResponseIfNeeded(FormInterface $form): ?Response
    {
        if ($form->isSubmitted() && $form->isValid()) {
            return null;
        }
        $errors = $this->getErrorsFromForm($form);
        return $this->createApiResponse($errors, 400);
    }
}