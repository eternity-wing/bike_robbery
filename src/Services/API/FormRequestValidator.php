<?php


namespace App\Services\API;


use App\Exception\InvalidFormDataException;
use App\Exception\InvalidJsonFormatException;
use App\Services\FormDataSubmitter;
use App\Services\Utils;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FormRequestValidator
 * @package App\Services\API
 *
 * @author Wings <Eternity.mr8@gmail.com>
 */
class FormRequestValidator
{

    /**
     * @var FormDataSubmitter
     */
    private $formDataSubmitter;

    /**
     * FormRequestSubmitter constructor.
     * @param FormDataSubmitter $formDataSubmitter
     */
    public function __construct(FormDataSubmitter $formDataSubmitter)
    {
        $this->formDataSubmitter = $formDataSubmitter;
    }

    /**
     * @param FormInterface $form
     * @param Request $request
     * @return array|null
     */
    public function validate(FormInterface $form, Request $request):?array
    {
        try {
            $data = Utils::parseJson($request->getContent());
            $clearMissing = $request->getMethod() !== 'PATCH';
            $this->formDataSubmitter->submit($form, $clearMissing, $data);
            return null;
        }catch (InvalidJsonFormatException $exception){
            return ['data' => ['error' => 'Invalid json format'], 'status' => 400];
        }catch (InvalidFormDataException $exception){
            return ['data' => ['error' => $this->getErrorsFromForm($form)], 'status' => 400];
        }
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
}