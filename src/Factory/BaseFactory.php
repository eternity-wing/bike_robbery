<?php


namespace App\Factory;

use App\Exception\InvalidObjectException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Validation;

/**
 * Class BaseFactory
 * @package App\Factory
 *
 * @author Wings <Eternity.mr8@gmail.com>
 */
class BaseFactory
{

    /**
     * @var ContainerInterface
     */
    public $container;
    /**
     * @var EntityManagerInterface
     */
    public $entityManager;

    /**
     * @var ValidatorInterface
     */
    public $validator;

    /**
     * BaseFactory constructor.
     * @param EntityManagerInterface $manager
     * @param ContainerInterface $container
     * @param ValidatorInterface $validator
     */
    public function __construct(EntityManagerInterface $manager, ContainerInterface $container, ValidatorInterface $validator)
    {
        $this->entityManager = $manager;
        $this->container = $container;
        $this->validator = $validator;
    }

    /**
     * @param object $obj
     * @param null $constraints
     * @param null $groups
     * @throws InvalidObjectException
     */
    public function validate(object $obj, $constraints = null, $groups = null)
    {
        $errors = $this->validator->validate($obj, $constraints, $groups);
        if (count($errors) > 0) {
            throw new InvalidObjectException();
        }
    }
}
