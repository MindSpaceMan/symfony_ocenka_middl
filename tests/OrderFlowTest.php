<?php

namespace App\Tests;

use App\Entity\Order;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class OrderFlowTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();

        $this->client = static::createClient();
        $this->em = $this->getContainer()->get(EntityManagerInterface::class);

        $this->resetDatabaseSchema();
        $this->seedServices();
    }

    public function testGuestIsRedirectedToLoginWhenOpeningOrderForm(): void
    {

        $this->client->request('GET', '/');

        $this->assertResponseRedirects('/login', 302);
    }

    public function testAuthorizedUserSeesOrderFormFields(): void
    {

        $user = $this->createUser('u1@test.dev');
        $this->client->loginUser($user);
        $this->client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('select[name="order[service]"]');
        $this->assertSelectorExists('input[name="order[email]"]');
        $this->assertSelectorExists('#price-view');
        $this->assertSelectorExists('button[type="submit"]');
    }

    public function testOrderSubmitWithInvalidDataShowsErrorsAndDoesNotCreateOrder(): void
    {

        $user = $this->createUser('u2@test.dev');
        $this->client->loginUser($user);

        $this->client->request('GET', '/');

        $this->client->submitForm('Подтвердить', [
            'order[service]' => '',          // не выбрали услугу
            'order[email]' => 'not-an-email' // некорректный email
        ]);
        $this->assertResponseStatusCodeSame(422);

        $this->assertSelectorTextContains('#order_service_error1', 'Выберите услугу');
        $this->assertSelectorTextContains('#order_email_error1', 'Введите корректный email');

        $count = $this->em->getRepository(Order::class)->count([]);
        $this->assertEquals(0, $count);
    }

    public function testValidOrderSubmitCreatesOrderInDatabase(): void
    {
        $user = $this->createUser('u3@test.dev');
        $this->client->loginUser($user);

        $service = $this->em->getRepository(Service::class)->findOneBy([]);
        $this->assertNotNull($service);

        $this->client->request('GET', '/');

        $this->client->submitForm('Подтвердить', [
            'order[service]' => (string) $service->getId(),
            'order[email]' => 'buyer@test.dev',
        ]);

        $this->assertResponseRedirects('/', 302);
        $this->client->followRedirect();

        $orders = $this->em->getRepository(Order::class)->findAll();
        $this->assertCount(1, $orders);

        /** @var Order $order */
        $order = $orders[0];

        $this->assertSame('buyer@test.dev', $order->getEmail());
        $this->assertSame($user->getId(), $order->getUser()?->getId());
        $this->assertSame($service->getId(), $order->getService()?->getId());
        $this->assertSame($service->getPrice(), $order->getPriceSnapshot());
    }


    private function resetDatabaseSchema(): void
    {
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();

        if (!$metadata) {
            return;
        }
        $tool = new SchemaTool($this->em);
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);
    }

    private function seedServices(): void
    {
        $items = [
            ['Оценка стоимости автомобиля', 700],
            ['Оценка стоимости дома', 3000],
            ['Оценка стоимости антиквариата', 1000],
            ['Оценка стоимости квартиры', 2000],
            ['Оценка стоимости велосипеда', 400],
        ];


        foreach ($items as [$name, $price]) {
            $s = (new Service())->setName($name)->setPrice($price);
            $this->em->persist($s);
        }
        $this->em->flush();
    }

    private function createUser(string $email): User
    {
        $user = new User();
        $user->setEmail($email);
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($hasher->hashPassword($user, 'pass12345'));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
