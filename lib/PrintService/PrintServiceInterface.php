<?php

namespace PrintService;

use Vespolina\Media\FileInterface;

interface PrintServiceInterface
{
    /**
     * Discover the available printers
     *
     * @return Printer[]
     */
    public function discoverPrinters();

    /**
     * Check the current state of the printer
     *
     * @param Printer $printer
     * @return array
     */
    public function queryPrinter(Printer $printer);

    /**
     * Submit a file to be printed
     *
     * @param FileInterface $file
     * @return PrintJob
     */
    public function submitPrintJob(FileInterface $file);
} 