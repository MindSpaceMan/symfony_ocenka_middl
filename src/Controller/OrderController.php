<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
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
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $order = new Order();

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service = $order->getService();

            $order->setEmail($form->get('email')->getData());
            $order->setUser($this->getUser());
            $order->setPriceSnapshot($service->getPrice()); // фиксируем цену на момент заказа
            $order->setCreatedAt(new \DateTimeImmutable());

            $em->persist($order);
            $em->flush();

            $this->addFlash('success', 'Заказ сохранён ✅');

            return $this->redirectToRoute('app_order');
        }

        return $this->render('order/index.html.twig', [
            'form' => $form,
        ]);
    }
}
