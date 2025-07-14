<?php
namespace App\Service;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Summary of FirebaseMessagingService
 * Ce service permet d'envoyer des notifications via firebase
 */
class FirebaseMessagingService
{
    private string $projectId;
    private string $credentialsPath;

    public function __construct(
        private readonly HttpClientInterface $client
    ) {
        //ici on specifie le chemins vers le fichier json genere dans le compte de service de la console firebase
        $this->credentialsPath = __DIR__ . '/../../config/firebase-credentials.json';
        //ici on specifie l'ID du projet firebase
        $this->projectId = 'komercy-e7d0d';
    }

    /**
     * Summary of sendToDevice
     * Cette fonction permet d'envoyer une ou plusieurs notification.
     * Elle prend en paramêtre le token client, le titre de la notification et ainsi que le corps de la notification
     * Elle permet :
     *  1 -  La génération d'un  access_token à partir du fichier json du compte de service
     *  2 -  L'envoi une requête vers un seule client
     *  3 -  (TODO) L'envoi des notifications de masse vers tout les devices  
     * @param string $deviceToken
     * @param string $title
     * @param string $body
     * @throws \Exception
     * @return string
     */
    public function sendToDevice(string $deviceToken, string $title, string $body):?string
    {
        // génération d'un access_token firebase grace au fichier json du compte de service et à la libraire google/auth
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];
        $credentials = new ServiceAccountCredentials($scopes, $this->credentialsPath);
        $accessToken = $credentials->fetchAuthToken()['access_token'];

        //URL de l'endpoint firebase pour l'envoi des notifications push. C'est une chaine formatter qui prend en param l'id du projet firebase
        $url = sprintf(
            'https://fcm.googleapis.com/v1/projects/%s/messages:send',
            $this->projectId
        );

        /*ici le payload minimal pour envoyer une  notification avec body constituant le corps principal
            du message et title le titre à afficher. La paire [token => value] est obligatoire (firebase
            rejettera un payload qui n'a pas de value pour la clef token) 
        */
        $payload = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
            ]
        ];

        // ici on envoi une requête simple sans gestion d'erreur
        // $this->client->request('POST', $url, [
        //     'headers' => [
        //         'Authorization' => 'Bearer ' . $accessToken,
        //         'Content-Type' => 'application/json',
        //     ],
        //     'json' => $payload,
        // ]);


        /*ici on envoi une requête vers l'endpoint de firebase en utilisant le client http de symfony
            En cas de success $content aura le format :
                {
                    "name": "projects/PROJECT_ID/messages/REFERENCE_DU_MESSAGE"
                }
            sinon un exception sera levé
         */
        try {
            $response = $this->client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);
            return $content = $response->getContent();
        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {
            $response = $e->getResponse();
            $content = $response ? $response->getContent(false) : $e->getMessage();
            throw new \Exception('Erreur FCM : ' . $content);
        }
    }

    /**
     * Summary of sendToMultiple
     * cette fonction permet d'envoyer des notifications vers de multiple client
     * @param array $payload
     * @return array
     */
    public function sendToMultiple(array $payload = []):?array
    {
        //TODO discuter de la necessite
        return [];
    }
}
