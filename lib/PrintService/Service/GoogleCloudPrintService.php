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
    protected $accessToken;
    protected $client;
    protected $googleClient;

    public function __construct(\Google_Client $googleClient)
    {
        $this->googleClient = $googleClient;
    }

    /**
     * Pass the authentication code in for a token
     *
     * @param string $code
     * @return $this
     */
    public function authenticate($code)
    {
        $response = json_decode($this->googleClient->authenticate($code), true);
        $this->accessToken = $response['access_token'];

        return $this;
    }

    /**
     * Return the accessToken
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
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
            'content' => new PostFile('content', fopen($file->getFileSystemPath(), 'r')),
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

    protected function postRequest($path, array $parameters = [])
    {
        $url = 'https://www.google.com/cloudprint/' . $path;
        $options = [
            'headers' => ['Authorization' => 'OAuth '.$this->accessToken],
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