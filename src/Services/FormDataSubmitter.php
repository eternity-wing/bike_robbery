<?php


namespace App\Services;

use App\Exception\InvalidFormDataException;
use Symfony\Component\Form\FormInterface;

/**
 * Class FormDataSubmitter
 * @package App\Services
 *
 * @author Wings <Eternity.mr8@gmail.com>
 */
class FormDataSubmitter
{
    /**
     * @param FormInterface $form
     * @param bool $clearMissing
     * @param array $data
     * @throws InvalidFormDataException
     */
    public function submit(FormInterface $form, bool $clearMissing, array $data)
    {
        $form->submit($data, $clearMissing);
        $formIsNotValid = !($form->isSubmitted() && $form->isValid());
        if ($formIsNotValid) {
            throw new InvalidFormDataException();
        }
    }
}
