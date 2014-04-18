<?php

namespace PrintService\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Post\PostFile;
use PrintService\Exception\ConnectionException;
use PrintService\Job;
use PrintService\Printer;
use PrintService\PrintServiceInterface;
use Vespolina\Media\FileInterface;

/**
 * Class GoogleCloudPrintService
 * @package Fruit\MatterBundle\Service
 */
class GoogleCloudPrintService implements PrintServiceInterface
{
    protected $refreshToken;
    protected $client;
    protected $googleClient;

    public function __construct(\Google_Client $googleClient)
    {
        $this->googleClient = $googleClient;
    }

    /**
     * Set the refresh token
     *
     * @param string $refreshToken
     * @return $this
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Return the refresh token
     *
     * @return string
     */
    public function getRefreshTokens()
    {
        return $this->refreshToken;
    }

    /**
     * {@inheritdoc}
     */
    public function discoverPrinters()
    {
        $response = $this->postRequest('search');
        $printers = [];
        foreach ($response['printers'] as $metadata) {
            $printer = new Printer();
            $printer->setVendorId($metadata['id'])
                ->setName($metadata['displayName'])
                ->setDescription($metadata['description'])
                ->setMetadata($metadata)
            ;
            $printers[] = $printer;
        }

        return $printers;
    }

    /**
     * {@inheritdoc}
     */
    public function queryPrinter(Printer $printer)
    {
        $parameters = [
            'printerid' => $printer->getVendorId(),
            'extra_fields' => 'connectionStatus',
        ];
        $response = $this->postRequest('printer', $parameters);

        return $response['printers'][0]['connectionStatus'];
    }

    /**
     * {@inheritdoc}
     */
    public function submitPrintJob(Printer $printer, FileInterface $file)
    {
        $title = 'file-'.$file->getId();
        $parameters = [
            'printerid' => $printer->getVendorId(),
            'title' => $title,
            'ticket' => '{ "version": "1.0", "print": {} }',
            'content' => new PostFile('content', fopen($file->getLocalPath(), 'r')),
        ];
        $response = $this->postRequest('submit', $parameters);
        $setAt =new \DateTime();
        $setAt->setTimestamp(substr($response['job']['createTime'], 0, 10));
        $job = new Job();
        $job->setFile($file)
            ->setJobId($response['job']['id'])
            ->setMetadata($response['job'])
            ->setPrinter($printer)
            ->setSentAt($setAt)
            ->setStatus($response['job']['status'])
            ->setTitle($response['job']['title'])
        ;

        return $job;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    protected function getClient()
    {
        if (!$this->client) {
            $this->client = new Client();
        }

        return $this->client;
    }

    protected function getToken()
    {
        $this->googleClient->refreshToken($this->refreshToken);
        $tokens = json_decode($this->googleClient->getAccessToken(), true);

        return $tokens['access_token'];
    }

    protected function postRequest($path, array $parameters = [])
    {
        $url = 'https://www.google.com/cloudprint/' . $path;
        $options = [
            'headers' => ['Authorization' => 'OAuth '.$this->getToken()],
            'body' => $parameters,
        ];
        $response = $this->getClient()->post($url, $options);
        $body = $response->json();

        if ($body['success'] === false) {
            throw new ConnectionException($body['message'], $body['errorCode']);
        }

        return $body;
    }
} 