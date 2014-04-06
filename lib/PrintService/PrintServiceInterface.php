<?php

namespace PrintService;

interface PrintServiceInterface 
{
    /**
     * Submit a file to be printed
     *
     * @param FileInterface $file
     * @return PrintJob
     */
    public function submitJob(FileInterface $file);

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
} 