<?php

namespace PrintService;

/**
 * Class Job
 * @package PrintService
 */
class Job 
{
    protected $file;
    protected $jobId;
    protected $metadata;
    protected $printer;
    protected $sentAt;
    protected $status;
    protected $title;

    /**
     * Set the file
     *
     * @param mixed $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Return the file
     *
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set the jobId
     *
     * @param mixed $jobId
     * @return $this
     */
    public function setJobId($jobId)
    {
        $this->jobId = $jobId;

        return $this;
    }

    /**
     * Return the jobId
     *
     * @return mixed
     */
    public function getJobId()
    {
        return $this->jobId;
    }

    /**
     * Set the metadata
     *
     * @param mixed $metadata
     * @return $this
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Return the metadata
     *
     * @return mixed
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set the printer
     *
     * @param mixed $printer
     * @return $this
     */
    public function setPrinter($printer)
    {
        $this->printer = $printer;

        return $this;
    }

    /**
     * Return the printer
     *
     * @return mixed
     */
    public function getPrinter()
    {
        return $this->printer;
    }

    /**
     * Set the time the job was sent
     *
     * @param \DateTime $sentAt
     * @return $this
     */
    public function setSentAt(\DateTime $sentAt)
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * Return the time the job was sent
     *
     * @return \DateTime
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * Set the status
     *
     * @param mixed $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Return the status
     *
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the title
     *
     * @param mixed $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Return the title
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }
}