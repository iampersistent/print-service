<?php

namespace PrintService\Test\Service;

use Vespolina\Media\Entity\File;
use PrintService\Printer;
use PrintService\Service\GoogleCloudPrintService;

class GoogleCloudPrintServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException           \PrintService\Exception\ConnectionException
     * @expectedExceptionMessage    Failure
     * @expectedExceptionCode       5
     */
    public function testException()
    {
        $responseData = [
            'success' => false,
            'message' => 'Failure',
            'errorCode' => 5,
        ];

        $response = $this->getMock('GuzzleHttp\Response', ['json']);
        $response->expects($this->once())
            ->method('json')
            ->will($this->returnValue($responseData));

        $client = $this->getMock('GuzzleHttp\Client');
        $client->expects($this->once())
            ->method('post')
            ->will($this->returnValue($response));

        $service = $this->getMock('PrintService\Service\GoogleCloudPrintService', ['getClient']);
        $service->expects($this->once())
            ->method('getClient')
            ->will($this->returnValue($client));

        $service->discoverPrinters();
    }

    public function testDiscoverPrinters()
    {
        $service = $this->getMock('PrintService\Service\GoogleCloudPrintService', ['postRequest']);
        $service->expects($this->once())
            ->method('postRequest')
            ->with('search', [])
            ->will($this->returnValue($this->discoverData));

        $printers = $service->discoverPrinters();
        $this->assertCount(2, $printers);

        foreach ($printers as $printer) {
            $id = $printer->getVendorId();
            $expected = $this->getExpectedDiscoverData($id);
            $this->assertSame($expected['name'], $printer->getName());
            $this->assertSame($expected['description'], $printer->getDescription());
            $this->assertSame($expected['metadata'], $printer->getMetadata());
        }
    }

    public function testQueryPrinter()
    {
        $printer = new Printer();
        $printer->setVendorId('9c11268c-4dd8-c8f4-e63e-3a5f26f0fc8f');
        $parameters = [
            'printerid' => '9c11268c-4dd8-c8f4-e63e-3a5f26f0fc8f',
            'extra_fields' => 'connectionStatus',
        ];
        $service = $this->getMock('PrintService\Service\GoogleCloudPrintService', ['postRequest']);
        $service->expects($this->once())
            ->method('postRequest')
            ->with('printer', $parameters)
            ->will($this->returnValue($this->queryData));

        $this->assertSame('ONLINE', $service->queryPrinter($printer));
    }

    public function testSubmitJob()
    {
        $service = $this->getMock('PrintService\Service\GoogleCloudPrintService', ['postRequest']);
        $service->expects($this->once())
            ->method('postRequest')
            ->will($this->returnValue($this->printJobData));

        $printer = new Printer();
        $printer->setVendorId('9c11268c-4dd8-c8f4-e63e-3a5f26f0fc8f');
        $exampleFile = realpath(__DIR__ . '/../fixtures/example.pdf');
        $file = new File();
        $file->setFilesystemPath($exampleFile);
        $rp = new \ReflectionProperty($file, 'id');
        $rp->setAccessible(true);
        $rp->setValue($file, 853);
        $rp->setAccessible(false);
        $job = $service->submitPrintJob($printer, $file);

        $this->assertSame('4f616ca1-6979-e090-e3a3-705ec5db4d21', $job->getJobId());
        $this->assertSame('file-853', $job->getTitle());
        $this->assertSame($printer, $job->getPrinter());
        $this->assertSame($file, $job->getFile());
        $this->assertSame('QUEUED', $job->getStatus());
        $this->assertEquals(new \DateTime('2014-04-08 03:58:13'), $job->getSentAt());
        $this->assertSame($this->printJobData['job'], $job->getMetadata());
    }

    protected function getExpectedDiscoverData($printer)
    {
        $data = [
            '9c11268c-4dd8-c8f4-e63e-3a5f26f0fc8f' => [
                'name' => 'EPSON5C9F73__WorkForce_630_',
                'description' => 'EPSON5C9F73 (WorkForce 630)',
                'metadata' => array (
                    'tags' =>
                        array (
                            0 => '^recent',
                            1 => '^own',
                            2 => '^connector',
                            3 => '^can_share',
                            4 => '^can_update',
                            5 => '__cp__chrome_version=33.0.1750.152 unknown',
                            6 => '__cp__copies=1',
                            7 => '__cp__device-uri=dnssd://EPSON5C9F73%20(WorkForce%20630)._printer._tcp.local./',
                            8 => '__cp__finishings=3',
                            9 => '__cp__job-hold-until=no-hold',
                            10 => '__cp__job-priority=50',
                            11 => '__cp__job-sheets=none,none',
                            12 => '__cp__marker-change-time=1396820529',
                            13 => '__cp__marker-colors=#000000,#ff00ff,#ffff00,#00ffff',
                            14 => '__cp__marker-levels=53,28,49,18',
                            15 => '__cp__marker-names=Black,Magenta,Yellow,Cyan',
                            16 => '__cp__marker-types=inkCartridge,inkCartridge,inkCartridge,inkCartridge',
                            17 => '__cp__number-up=1',
                            18 => '__cp__printer-commands=Clean,PrintSelfTestPage,ReportLevels',
                            19 => '__cp__printer-info=EPSON5C9F73 (WorkForce 630)',
                            20 => '__cp__printer-is-accepting-jobs=true',
                            21 => '__cp__printer-is-shared=true',
                            22 => '__cp__printer-location=',
                            23 => '__cp__printer-make-and-model=EPSON WF 630 Series',
                            24 => '__cp__printer-state=3',
                            25 => '__cp__printer-state-change-time=1396820534',
                            26 => '__cp__printer-state-reasons=com.epson.status-reply.20130911-130143.00.d1.40424443205354320d0a6900010104060201ff0e01010f0d03010064040236050341030139100301194e130101190c0000000000756e6b6e6f776e1b01002031006600006564616961676562606862666563615757575757576862666562616862666562616861676,com.epson.status-reply.20130911-130143.01.d1.56261676266656361240200000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000,com.epson.status-reply.20130911-130143.02.5f.0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000',
                            27 => '__cp__printer-type=75534348',
                            28 => '__cp__printer-uri-supported=ipp://localhost:631/printers/EPSON5C9F73__WorkForce_630_',
                            29 => '__cp__system_driverinfo=E',
                            30 => '__cp__system_name=Mac OS X',
                            31 => '__cp__system_version=10.9.2',
                            32 => '__cp__tagshash=3faaecd00918e1837e8f6d016b1fd82b',
                        ),
                    'createTime' => '1396427887881',
                    'accessTime' => '1396427887881',
                    'supportedContentTypes' => 'application/pdf,application/postscript,image/jpeg,image/png,image/gif',
                    'updateTime' => '1396820659202',
                    'status' => '3',
                    'ownerId' => 'develop@zestic.com',
                    'gcpVersion' => '1.0',
                    'capsHash' => '7dfb406d28f355c153603a923c991ed9',
                    'isTosAccepted' => false,
                    'type' => 'GOOGLE',
                    'id' => '9c11268c-4dd8-c8f4-e63e-3a5f26f0fc8f',
                    'description' => 'EPSON5C9F73 (WorkForce 630)',
                    'proxy' => '0CD97388-D4C9-7B62-E678-C77E616877E4',
                    'name' => '\\\\\\EPSON5C9F73__WorkForce_630_',
                    'defaultDisplayName' => '',
                    'local_settings' =>
                        array (
                            'current' =>
                                array (
                                    'xmpp_timeout_value' => 300,
                                ),
                        ),
                    'displayName' => 'EPSON5C9F73__WorkForce_630_',
                ),
            ],
            '__google__docs' => [
                'name' => 'Save to Google Drive',
                'description' => 'Save your document as a PDF in Google Drive',
                'metadata' => array (
                    'tags' =>
                        array (
                            0 => '^recent',
                            1 => '__google__drive_enabled',
                            2 => 'save',
                            3 => 'docs',
                            4 => 'pdf',
                            5 => 'google',
                        ),
                    'createTime' => '1311368403894',
                    'accessTime' => '1316132041869',
                    'updateTime' => '1370287324050',
                    'status' => '',
                    'ownerId' => 'cloudprinting@gmail.com',
                    'gcpVersion' => '1.0',
                    'capsHash' => '',
                    'isTosAccepted' => false,
                    'type' => 'DRIVE',
                    'id' => '__google__docs',
                    'description' => 'Save your document as a PDF in Google Drive',
                    'proxy' => 'google-wide',
                    'name' => 'Save to Google Docs',
                    'defaultDisplayName' => 'Save to Google Drive',
                    'capsFormat' => 'xps',
                    'capabilities' =>
                        array (
                            0 =>
                                array (
                                    'psf:DataType' => 'xsd:string',
                                    'name' => '__goog__drive_file_name',
                                    'psk:DisplayName' => 'Google Drive File Name',
                                    'type' => 'ParameterDef',
                                ),
                        ),
                    'displayName' => 'Save to Google Drive',
                ),
            ],
        ];

        return $data[$printer];
    }

    protected $discoverData = array (
        'success' => true,
        'xsrf_token' => 'AIp06DgSFFOL3L6d4hbIefbovdKLRdWlQA:1396825537759',
        'request' =>
            array (
                'time' => '0',
                'users' =>
                    array (
                        0 => 'develop@zestic.com',
                    ),
                'params' =>
                    array (
                        'connection_status' =>
                            array (
                                0 => '',
                            ),
                        'extra_fields' =>
                            array (
                                0 => '',
                            ),
                        'use_cdd' =>
                            array (
                                0 => 'false',
                            ),
                        'q' =>
                            array (
                                0 => '',
                            ),
                        'type' =>
                            array (
                                0 => '',
                            ),
                    ),
                'user' => 'develop@zestic.com',
            ),
        'printers' =>
            array (
                0 =>
                    array (
                        'tags' =>
                            array (
                                0 => '^recent',
                                1 => '^own',
                                2 => '^connector',
                                3 => '^can_share',
                                4 => '^can_update',
                                5 => '__cp__chrome_version=33.0.1750.152 unknown',
                                6 => '__cp__copies=1',
                                7 => '__cp__device-uri=dnssd://EPSON5C9F73%20(WorkForce%20630)._printer._tcp.local./',
                                8 => '__cp__finishings=3',
                                9 => '__cp__job-hold-until=no-hold',
                                10 => '__cp__job-priority=50',
                                11 => '__cp__job-sheets=none,none',
                                12 => '__cp__marker-change-time=1396820529',
                                13 => '__cp__marker-colors=#000000,#ff00ff,#ffff00,#00ffff',
                                14 => '__cp__marker-levels=53,28,49,18',
                                15 => '__cp__marker-names=Black,Magenta,Yellow,Cyan',
                                16 => '__cp__marker-types=inkCartridge,inkCartridge,inkCartridge,inkCartridge',
                                17 => '__cp__number-up=1',
                                18 => '__cp__printer-commands=Clean,PrintSelfTestPage,ReportLevels',
                                19 => '__cp__printer-info=EPSON5C9F73 (WorkForce 630)',
                                20 => '__cp__printer-is-accepting-jobs=true',
                                21 => '__cp__printer-is-shared=true',
                                22 => '__cp__printer-location=',
                                23 => '__cp__printer-make-and-model=EPSON WF 630 Series',
                                24 => '__cp__printer-state=3',
                                25 => '__cp__printer-state-change-time=1396820534',
                                26 => '__cp__printer-state-reasons=com.epson.status-reply.20130911-130143.00.d1.40424443205354320d0a6900010104060201ff0e01010f0d03010064040236050341030139100301194e130101190c0000000000756e6b6e6f776e1b01002031006600006564616961676562606862666563615757575757576862666562616862666562616861676,com.epson.status-reply.20130911-130143.01.d1.56261676266656361240200000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000,com.epson.status-reply.20130911-130143.02.5f.0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000',
                                27 => '__cp__printer-type=75534348',
                                28 => '__cp__printer-uri-supported=ipp://localhost:631/printers/EPSON5C9F73__WorkForce_630_',
                                29 => '__cp__system_driverinfo=E',
                                30 => '__cp__system_name=Mac OS X',
                                31 => '__cp__system_version=10.9.2',
                                32 => '__cp__tagshash=3faaecd00918e1837e8f6d016b1fd82b',
                            ),
                        'createTime' => '1396427887881',
                        'accessTime' => '1396427887881',
                        'supportedContentTypes' => 'application/pdf,application/postscript,image/jpeg,image/png,image/gif',
                        'updateTime' => '1396820659202',
                        'status' => '3',
                        'ownerId' => 'develop@zestic.com',
                        'gcpVersion' => '1.0',
                        'capsHash' => '7dfb406d28f355c153603a923c991ed9',
                        'isTosAccepted' => false,
                        'type' => 'GOOGLE',
                        'id' => '9c11268c-4dd8-c8f4-e63e-3a5f26f0fc8f',
                        'description' => 'EPSON5C9F73 (WorkForce 630)',
                        'proxy' => '0CD97388-D4C9-7B62-E678-C77E616877E4',
                        'name' => '\\\\\\EPSON5C9F73__WorkForce_630_',
                        'defaultDisplayName' => '',
                        'local_settings' =>
                            array (
                                'current' =>
                                    array (
                                        'xmpp_timeout_value' => 300,
                                    ),
                            ),
                        'displayName' => 'EPSON5C9F73__WorkForce_630_',
                    ),
                1 =>
                    array (
                        'tags' =>
                            array (
                                0 => '^recent',
                                1 => '__google__drive_enabled',
                                2 => 'save',
                                3 => 'docs',
                                4 => 'pdf',
                                5 => 'google',
                            ),
                        'createTime' => '1311368403894',
                        'accessTime' => '1316132041869',
                        'updateTime' => '1370287324050',
                        'status' => '',
                        'ownerId' => 'cloudprinting@gmail.com',
                        'gcpVersion' => '1.0',
                        'capsHash' => '',
                        'isTosAccepted' => false,
                        'type' => 'DRIVE',
                        'id' => '__google__docs',
                        'description' => 'Save your document as a PDF in Google Drive',
                        'proxy' => 'google-wide',
                        'name' => 'Save to Google Docs',
                        'defaultDisplayName' => 'Save to Google Drive',
                        'capsFormat' => 'xps',
                        'capabilities' =>
                            array (
                                0 =>
                                    array (
                                        'psf:DataType' => 'xsd:string',
                                        'name' => '__goog__drive_file_name',
                                        'psk:DisplayName' => 'Google Drive File Name',
                                        'type' => 'ParameterDef',
                                    ),
                            ),
                        'displayName' => 'Save to Google Drive',
                    ),
            ),
    );

    protected $queryData =
        array (
            'success' => true,
            'xsrf_token' => 'AIp06Dh7oXa7DWuctLjAgtHWxDxUTlgStA:1396830910877',
            'request' =>
                array (
                    'time' => '0',
                    'users' =>
                        array (
                            0 => 'iampersistent@gmail.com',
                        ),
                    'params' =>
                        array (
                            'extra_fields' =>
                                array (
                                    0 => 'connectionStatus',
                                ),
                            'printerid' =>
                                array (
                                    0 => '9c11268c-4dd8-c8f4-e63e-3a5f26f0fc8f',
                                ),
                        ),
                    'user' => 'iampersistent@gmail.com',
                ),
            'printers' =>
                array (
                    0 =>
                        array (
                            'tags' =>
                                array (
                                    0 => '^recent',
                                    1 => '^own',
                                    2 => '^connector',
                                    3 => '^can_share',
                                    4 => '^can_update',
                                    5 => '__cp__chrome_version=33.0.1750.152 unknown',
                                    6 => '__cp__copies=1',
                                    7 => '__cp__device-uri=dnssd://EPSON5C9F73%20(WorkForce%20630)._printer._tcp.local./',
                                    8 => '__cp__finishings=3',
                                    9 => '__cp__job-hold-until=no-hold',
                                    10 => '__cp__job-priority=50',
                                    11 => '__cp__job-sheets=none,none',
                                    12 => '__cp__marker-change-time=1396820529',
                                    13 => '__cp__marker-colors=#000000,#ff00ff,#ffff00,#00ffff',
                                    14 => '__cp__marker-levels=53,28,49,18',
                                    15 => '__cp__marker-names=Black,Magenta,Yellow,Cyan',
                                    16 => '__cp__marker-types=inkCartridge,inkCartridge,inkCartridge,inkCartridge',
                                    17 => '__cp__number-up=1',
                                    18 => '__cp__printer-commands=Clean,PrintSelfTestPage,ReportLevels',
                                    19 => '__cp__printer-info=EPSON5C9F73 (WorkForce 630)',
                                    20 => '__cp__printer-is-accepting-jobs=true',
                                    21 => '__cp__printer-is-shared=true',
                                    22 => '__cp__printer-location=',
                                    23 => '__cp__printer-make-and-model=EPSON WF 630 Series',
                                    24 => '__cp__printer-state=3',
                                    25 => '__cp__printer-state-change-time=1396820534',
                                    26 => '__cp__printer-state-reasons=com.epson.status-reply.20130911-130143.00.d1.40424443205354320d0a6900010104060201ff0e01010f0d03010064040236050341030139100301194e130101190c0000000000756e6b6e6f776e1b01002031006600006564616961676562606862666563615757575757576862666562616862666562616861676,com.epson.status-reply.20130911-130143.01.d1.56261676266656361240200000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000,com.epson.status-reply.20130911-130143.02.5f.0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000',
                                    27 => '__cp__printer-type=75534348',
                                    28 => '__cp__printer-uri-supported=ipp://localhost:631/printers/EPSON5C9F73__WorkForce_630_',
                                    29 => '__cp__system_driverinfo=E',
                                    30 => '__cp__system_name=Mac OS X',
                                    31 => '__cp__system_version=10.9.2',
                                    32 => '__cp__tagshash=3faaecd00918e1837e8f6d016b1fd82b',
                                ),
                            'createTime' => '1396427887881',
                            'accessTime' => '1396427887881',
                            'supportedContentTypes' => 'application/pdf,application/postscript,image/jpeg,image/png,image/gif',
                            'updateTime' => '1396820659202',
                            'status' => '3',
                            'ownerId' => 'IamPersistent@gmail.com',
                            'gcpVersion' => '1.0',
                            'capsHash' => '7dfb406d28f355c153603a923c991ed9',
                            'isTosAccepted' => false,
                            'access' =>
                                array (
                                    0 =>
                                        array (
                                            'scope' => 'IamPersistent@gmail.com',
                                            'membership' => 'MANAGER',
                                            'email' => 'IamPersistent@gmail.com',
                                            'name' => 'Richard Shank',
                                            'role' => 'OWNER',
                                            'type' => 'USER',
                                        ),
                                ),
                            'type' => 'GOOGLE',
                            'id' => '9c11268c-4dd8-c8f4-e63e-3a5f26f0fc8f',
                            'description' => 'EPSON5C9F73 (WorkForce 630)',
                            'proxy' => '0CD97388-D4C9-7B62-E678-C77E616877E4',
                            'name' => '\\\\\\EPSON5C9F73__WorkForce_630_',
                            'defaultDisplayName' => '',
                            'connectionStatus' => 'ONLINE',
                            'local_settings' =>
                                array (
                                    'current' =>
                                        array (
                                            'xmpp_timeout_value' => 300,
                                        ),
                                ),
                            'capsFormat' => 'ppd',
                            'displayName' => 'EPSON5C9F73__WorkForce_630_',
                        ),
                ),
        );
    protected $printJobData = array (
        'success' => true,
        'message' => 'Print job added.',
        'xsrf_token' => 'AIp06Di9Fa1Qv2smepl_LPmoTZbCtxZ_Tg:1396929493097',
        'request' =>
            array (
                'time' => '0',
                'users' =>
                    array (
                        0 => 'develop@zestic.com',
                    ),
                'params' =>
                    array (
                        'title' =>
                            array (
                                0 => 'file-853',
                            ),
                        'xsrf' =>
                            array (
                                0 => 'AIp06DgujpQc-R3zsU6cmdzhquQAMPB9zA:1396844367726',
                            ),
                        'ticket' =>
                            array (
                                0 => '{
                                      "version": "1.0",
                                      "print": {}
                                    }',
                            ),
                        'printerid' =>
                            array (
                                0 => '9c11268c-4dd8-c8f4-e63e-3a5f26f0fc8f',
                            ),
                        'contentType' =>
                            array (
                                0 => 'application/pdf',
                            ),
                        'jobid' =>
                            array (
                                0 => '',
                            ),
                    ),
                'user' => 'iampersistent@gmail.com',
            ),
        'job' =>
            array (
                'tags' =>
                    array (
                        0 => '^own',
                    ),
                'createTime' => '1396929493219',
                'printerName' => '',
                'updateTime' => '1396929493974',
                'status' => 'QUEUED',
                'ownerId' => 'develop@zestic.com',
                'rasterUrl' => 'https://www.google.com/cloudprint/download?id=4f616ca1-6979-e090-e3a3-705ec5db4d21&forcepwg=1',
                'ticketUrl' => 'https://www.google.com/cloudprint/ticket?format=ppd&output=json&jobid=4f616ca1-6979-e090-e3a3-705ec5db4d21',
                'printerid' => '9c11268c-4dd8-c8f4-e63e-3a5f26f0fc8f',
                'semanticState' =>
                    array (
                        'state' =>
                            array (
                                'type' => 'QUEUED',
                            ),
                        'delivery_attempts' => 1,
                        'version' => '1.0',
                    ),
                'contentType' => 'application/pdf',
                'fileUrl' => 'https://www.google.com/cloudprint/download?id=4f616ca1-6979-e090-e3a3-705ec5db4d21',
                'id' => '4f616ca1-6979-e090-e3a3-705ec5db4d21',
                'message' => '',
                'title' => 'file-853',
                'errorCode' => '',
                'numberOfPages' => 1,
            ),
    )
        ;
}