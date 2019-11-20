<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\AgentRepository;
use App\Repository\AgentStatRepository;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/event")
 */
class EventController extends AbstractController
{
    /**
     * @Route("/", name="event_index", methods={"GET"})
     */
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render(
            'event/index.html.twig', [
                'events' => $eventRepository->findAll(),
            ]
        );
    }

    /**
     * @Route("/new", name="event_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render(
            'event/new.html.twig', [
                'event' => $event,
                'form'  => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="event_show", methods={"GET"})
     */
    public function show(Event $event, AgentRepository $agentRepository, AgentStatRepository $statRepository): Response
    {
        // foreach ($agentRepository->findAll() as $agent) {
        //
        // }
        // $entries = $statRepository->findByDateAndAgent($event->getDateStart(), $event->getDateEnd());
        $entries = $statRepository->findByDate($event->getDateStart(), $event->getDateEnd());

        $previousEntries = [];
        $currentEntries = [];

        foreach ($entries as $entry) {
            $agentName = $entry->getAgent()->getNickname();

            if (false === isset($previousEntries[$agentName])) {
                $previousEntries[$agentName] = $entry;//$statRepository->getPrevious($entry);
            } else
            {
                $currentEntries[$agentName] = $entry;
            }
        }

        $apGains = [];

        foreach (array_keys($currentEntries) as $agentName) {
                $apGains[$agentName] = $currentEntries[$agentName]->getAp() - $previousEntries[$agentName]->getAp();
        }

        arsort($apGains);

        return $this->render(
            'event/show.html.twig', [
                'entries' => $entries,// @todo temporal
                'event' => $event,
                'apGains' => $apGains,
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="event_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Event $event): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('event_index');
        }

        return $this->render(
            'event/edit.html.twig', [
                'event' => $event,
                'form'  => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="event_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Event $event): Response
    {
        if ($this->isCsrfTokenValid(
            'delete'.$event->getId(), $request->request->get('_token')
        )
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('event_index');
    }
}