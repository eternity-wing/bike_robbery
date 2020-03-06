<?php


namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BaseKernelTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    public $entityManager;

    public function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
