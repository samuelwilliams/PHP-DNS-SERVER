<?php

namespace yswery\DNS;

class Rdata
{
    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $mname;

    /**
     * @var string
     */
    private $rname;

    /**
     * @var int
     */
    private $serial;

    /**
     * @var int
     */
    private $refresh;

    /**
     * @var int
     */
    private $retry;

    /**
     * @var int
     */
    private $expire;

    /**
     * @var int
     */
    private $minimum;

    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $target;

    /**
     * @var int
     */
    private $preference;

    /**
     * @var string
     */
    private $exchange;

    /**
     * @var string
     */
    private $text;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var int
     */
    private $weight;

    /**
     * @var int
     */
    private $port;

    public function __construct(int $type)
    {
        if (!RecordTypeEnum::isValid($type)) {
            throw new \InvalidArgumentException(sprintf('RDATA type "%d" is not a valid type.', $type));
        }

        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getMname(): string
    {
        $this->allowed([RecordTypeEnum::TYPE_SOA], 'mname');

        return $this->mname;
    }

    /**
     * @param string $mname
     *
     * @return Rdata
     */
    public function setMname(string $mname): Rdata
    {
        $this->allowed([RecordTypeEnum::TYPE_SOA], 'mname');

        $this->mname = $mname;

        return $this;
    }

    /**
     * @return string
     */
    public function getRname(): string
    {
        $this->allowed([RecordTypeEnum::TYPE_SOA], 'rname');

        return $this->rname;
    }

    /**
     * @param string $rname
     *
     * @return Rdata
     */
    public function setRname(string $rname): Rdata
    {
        $this->allowed([RecordTypeEnum::TYPE_SOA], 'rname');

        $this->rname = $rname;

        return $this;
    }

    /**
     * @return int
     */
    public function getSerial(): int
    {
        $this->allowed([RecordTypeEnum::TYPE_SOA], 'serial');

        return $this->serial;
    }

    /**
     * @param int $serial
     *
     * @return Rdata
     */
    public function setSerial(int $serial): Rdata
    {
        $this->allowed([RecordTypeEnum::TYPE_SOA], 'serial');

        $this->serial = $serial;

        return $this;
    }

    /**
     * @return int
     */
    public function getRefresh(): int
    {
        $this->allowed([RecordTypeEnum::TYPE_SOA], 'refresh');

        return $this->refresh;
    }

    /**
     * @param int $refresh
     *
     * @return Rdata
     */
    public function setRefresh(int $refresh): Rdata
    {
        $this->allowed([RecordTypeEnum::TYPE_SOA], 'refresh');
        $this->refresh = $refresh;

        return $this;
    }

    /**
     * @return int
     */
    public function getRetry(): int
    {
        $this->allowed([RecordTypeEnum::TYPE_SOA], 'retry');

        return $this->retry;
    }

    /**
     * @param int $retry
     *
     * @return Rdata
     */
    public function setRetry(int $retry): Rdata
    {
        $this->allowed([RecordTypeEnum::TYPE_SOA], 'retry');

        $this->retry = $retry;

        return $this;
    }

    /**
     * @return int
     */
    public function getExpire(): int
    {
        $this->allowed([RecordTypeEnum::TYPE_SOA], 'expire');

        return $this->expire;
    }

    /**
     * @param int $expire
     *
     * @return Rdata
     */
    public function setExpire(int $expire): Rdata
    {
        $this->allowed([RecordTypeEnum::TYPE_SOA], 'expire');

        $this->expire = $expire;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinimum(): int
    {
        $this->allowed([RecordTypeEnum::TYPE_SOA], 'minimum');

        return $this->minimum;
    }

    /**
     * @param int $minimum
     *
     * @return Rdata
     */
    public function setMinimum(int $minimum): Rdata
    {
        $this->allowed([RecordTypeEnum::TYPE_SOA], 'minimum');

        $this->minimum = $minimum;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        $this->allowed([RecordTypeEnum::TYPE_A, RecordTypeEnum::TYPE_AAAA], 'address');

        return $this->address;
    }

    /**
     * @param string $address
     *
     * @return Rdata
     */
    public function setAddress(string $address): Rdata
    {
        $this->allowed([RecordTypeEnum::TYPE_A, RecordTypeEnum::TYPE_AAAA], 'address');

        if (!filter_var($address, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException(sprintf('The IP address "%s" is invalid.', $address));
        }

        $this->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        $this->allowed([
            RecordTypeEnum::TYPE_CNAME,
            RecordTypeEnum::TYPE_DNAME,
            RecordTypeEnum::TYPE_PTR,
            RecordTypeEnum::TYPE_NS,
            RecordTypeEnum::TYPE_SRV,
        ], 'target');

        return $this->target;
    }

    /**
     * @param string $target
     *
     * @return Rdata
     */
    public function setTarget(string $target): Rdata
    {
        $this->allowed([
            RecordTypeEnum::TYPE_CNAME,
            RecordTypeEnum::TYPE_DNAME,
            RecordTypeEnum::TYPE_PTR,
            RecordTypeEnum::TYPE_NS,
            RecordTypeEnum::TYPE_SRV,
        ], 'target');

        $this->target = $target;

        return $this;
    }

    /**
     * @return int
     */
    public function getPreference(): int
    {
        $this->allowed([RecordTypeEnum::TYPE_MX], 'preference');

        return $this->preference;
    }

    /**
     * @param int $preference
     *
     * @return Rdata
     */
    public function setPreference(int $preference): Rdata
    {
        $this->allowed([RecordTypeEnum::TYPE_MX], 'preference');

        $this->preference = $preference;

        return $this;
    }

    /**
     * @return string
     */
    public function getExchange(): string
    {
        $this->allowed([RecordTypeEnum::TYPE_MX], 'exchange');

        return $this->exchange;
    }

    /**
     * @param string $exchange
     *
     * @return Rdata
     */
    public function setExchange(string $exchange): Rdata
    {
        $this->allowed([RecordTypeEnum::TYPE_MX], 'exchange');

        $this->exchange = $exchange;

        return $this;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        $this->allowed([RecordTypeEnum::TYPE_TXT], 'text');

        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return Rdata
     */
    public function setText(string $text): Rdata
    {
        $this->allowed([RecordTypeEnum::TYPE_TXT], 'text');

        if (255 < strlen($text)) {
            throw new \InvalidArgumentException('Text must be less than 256 characters.');
        }

        $this->text = $text;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        $this->allowed([RecordTypeEnum::TYPE_SRV], 'priority');

        return $this->priority;
    }

    /**
     * @param int $priority
     *
     * @return Rdata
     */
    public function setPriority(int $priority): Rdata
    {
        $this->allowed([RecordTypeEnum::TYPE_SRV], 'priority');

        $this->priority = $priority;

        return $this;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        $this->allowed([RecordTypeEnum::TYPE_SRV], 'weight');

        return $this->weight;
    }

    /**
     * @param int $weight
     *
     * @return Rdata
     */
    public function setWeight(int $weight): Rdata
    {
        $this->allowed([RecordTypeEnum::TYPE_SRV], 'weight');

        $this->weight = $weight;

        return $this;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        $this->allowed([RecordTypeEnum::TYPE_SRV], 'port');

        return $this->port;
    }

    /**
     * @param int $port
     *
     * @return Rdata
     */
    public function setPort(int $port): Rdata
    {
        $this->allowed([RecordTypeEnum::TYPE_SRV], 'port');

        $this->port = $port;

        return $this;
    }

    /**
     * Test if a property is valid for a type.
     *
     * @param array $types
     * @param $propertyName
     *
     * @throws \InvalidArgumentException
     */
    private function allowed(array $types, $propertyName)
    {
        if (!in_array($this->type, $types)) {
            throw new \InvalidArgumentException(\
                sprintf('The property "%s" is not valid for the RDATA type "%s".', $propertyName, RecordTypeEnum::getName($this->type))
            );
        }
    }
}
