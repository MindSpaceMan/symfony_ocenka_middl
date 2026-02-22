<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Service;
use App\Entity\User;
use App\Form\OrderType;
use App\Service\OrderCreator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OrderController extends AbstractController
{
    #[Route('/', name: 'app_order')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, OrderCreator $orderCreator): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            \assert($user instanceof User);

            $service = $order->getService();
            \assert($service instanceof Service);

            $result = $orderCreator->create(
                $user,
                $service,
                (string)$form->get('email')->getData()
            );

            if ($result->isSuccess()) {
                $this->addFlash('success', 'Заказ сохранён ✅');
                return $this->redirectToRoute('app_order');
            }

            foreach ($result->errors as $error) {
                $this->addFlash('error', $error);
            }
        }
        return $this->render('order/index.html.twig', [
            'form' => $form,
        ]);
    }
}
