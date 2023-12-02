<?php

declare(strict_types=1);

namespace Zenstruck\Foundry\Tests\Integration\InMemory;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Tests\Fixture\Entity\Address\StandardAddress;
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\Address\StandardAddressFactory;
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\Contact\StandardContactFactory;
use Zenstruck\Foundry\Tests\Fixture\InMemory\InMemoryStandardAddressRepository;
use Zenstruck\Foundry\Tests\Fixture\InMemory\InMemoryStandardContactRepository;
use function Zenstruck\Foundry\Persistence\enable_in_memory;
use function Zenstruck\Foundry\Persistence\repository;

final class InMemoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private InMemoryStandardAddressRepository $addressRepository;
    private InMemoryStandardContactRepository $contactRepository;

    protected function setUp(): void
    {
        enable_in_memory();

        $this->addressRepository = self::getContainer()->get(InMemoryStandardAddressRepository::class);
        $this->contactRepository = self::getContainer()->get(InMemoryStandardContactRepository::class);
    }

    /**
     * @test
     */
    public function create_one_does_not_persist_in_database(): void
    {
        $address = StandardAddressFactory::createOne();
        self::assertInstanceOf(StandardAddress::class, $address);

        StandardAddressFactory::assert()->count(0);

        // id is autogenerated from the db, then it should be null
        self::assertNull($address->id);
    }

    /**
     * @test
     */
    public function create_many_does_not_persist_in_database(): void
    {
        $addresses = StandardAddressFactory::createMany(2);
        self::assertContainsOnlyInstancesOf(StandardAddress::class, $addresses);

        StandardAddressFactory::assert()->count(0);

        foreach ($addresses as $address) {
            // id is autogenerated from the db, then it should be null
            self::assertNull($address->id);
        }
    }

    /**
     * @test
     */
    public function object_should_be_accessible_from_in_memory_repository(): void
    {
        $address = StandardAddressFactory::createOne();

        self::assertSame([$address], $this->addressRepository->all());
    }

    /**
     * @test
     */
    public function nested_objects_should_be_accessible_from_their_respective_repository(): void
    {
        $contact = StandardContactFactory::createOne();

        self::assertSame([$contact], $this->contactRepository->all());
        self::assertSame([$contact->getAddress()], $this->addressRepository->all());
    }
}
