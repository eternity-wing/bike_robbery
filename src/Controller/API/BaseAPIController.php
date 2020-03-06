<?php


namespace App\Controller\API;

use App\Exception\InvalidFormDataException;
use App\Exception\InvalidJsonFormatException;
use App\Services\FormDataSubmitter;
use App\Services\Utils;
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
     * @var FormDataSubmitter
     */
    private $formDataSubmitter;

    /**
     * BaseAPIController constructor.
     * @param FormDataSubmitter $formDataSubmitter
     */
    public function __construct(FormDataSubmitter $formDataSubmitter)
    {
        $this->serializer = SerializerBuilder::create()->build();
        $this->formDataSubmitter = $formDataSubmitter;
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
     * @param Request $request
     * @return array
     */
    public function extractDefaultFilters(Request $request): array
    {
        $offset = $request->query->getInt('offset', 1);
        $limit = $request->query->getInt('limit', self::DEFAULT_PAGE_SIZE);
        return [$offset, $limit];
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @return Response|null
     */
    public function validateRequest(FormInterface $form, Request $request): ?Response
    {
        try {
            $data = Utils::parseJson($request->getContent());
            $clearMissing = $request->getMethod() !== 'PATCH';
            $this->formDataSubmitter->submit($form, $clearMissing, $data);
            return null;
        } catch (InvalidJsonFormatException $exception) {
            return $this->createApiResponse(['error' => 'Invalid json format'], 400);
        } catch (InvalidFormDataException $exception) {
            return $this->createApiResponse(['error' => $this->getErrorsFromForm($form)], 400);
        }
    }
}