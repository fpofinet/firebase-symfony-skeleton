<?php
namespace App\Controller;

use App\Service\FirebaseMessagingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FirebaseController extends AbstractController
{
    public function __construct(
        private readonly FirebaseMessagingService $firebase
    ) {}

    #[Route('/home',name:'home')]
    public function number(): Response
    {
        $number = random_int(0, 100);

        return new Response("FIKA MAGIC NUMBER : .$number.");
    }

    #[Route('/firebase/send', name: 'firebase_send', methods: ['POST'])]
    public function send(Request $request): JsonResponse
    {
        $deviceToken = $request->get('token');
        $title = $request->get('title', 'Notification par défaut');
        $body = $request->get('body', 'Contenu du message par défaut');

        if (!$deviceToken) {
            return new JsonResponse(['error' => 'Le paramètre "token" est requis'], 400);
        }

        try {
            $content = $this->firebase->sendToDevice($deviceToken, $title, $body);
            return new JsonResponse(['status' => 'Notification envoyée avec succès',
                                            'content' => $content]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}       
