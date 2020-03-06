<?php


namespace App\Factory;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * BaseFactory constructor.
     * @param EntityManagerInterface $manager
     * @param ContainerInterface $container
     */
    public function __construct(EntityManagerInterface $manager, ContainerInterface $container)
    {
        $this->entityManager = $manager;
        $this->container = $container;
    }
}