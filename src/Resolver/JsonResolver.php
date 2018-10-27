<?php

namespace yswery\DNS\Resolver;

use yswery\DNS\ClassEnum;
use yswery\DNS\RecordTypeEnum;
use yswery\DNS\ResourceRecord;
use yswery\DNS\UnsupportedTypeException;

class JsonResolver extends AbstractResolver
{
    /**
     * @var int
     */
    protected $defaultClass = ClassEnum::INTERNET;

    /**
     * @var int
     */
    protected $defaultTtl;

    /**
     * JsonResolver constructor.
     *
     * @param array $files
     * @param int   $defaultTtl
     *
     * @throws UnsupportedTypeException
     */
    public function __construct(array $files, $defaultTtl = 300)
    {
        $this->isAuthoritative = true;
        $this->allowRecursion = false;
        $this->defaultTtl = $defaultTtl;

        foreach ($files as $file) {
            $zone = json_decode(file_get_contents($file), true);
            $resourceRecords = $this->isLegacyFormat($zone) ? $this->processLegacyZone($zone) : $this->processZone($zone);
            $this->addZone($resourceRecords);
        }
    }

    /**
     * @param array $zone
     *
     * @return ResourceRecord[]
     *
     * @throws UnsupportedTypeException
     */
    protected function processZone(array $zone): array
    {
        $parent = rtrim($zone['domain'], '.').'.';
        $defaultTtl = $zone['default-ttl'];
        $rrs = $zone['resource-records'];
        $resourceRecords = [];

        foreach ($rrs as $rr) {
            $name = $rr['name'] ?? $parent;
            $class = isset($rr['class']) ? ClassEnum::getClassFromName($rr['class']) : $this->defaultClass;

            $resourceRecords[] = (new ResourceRecord())
                ->setName($this->handleName($name, $parent))
                ->setClass($class)
                ->setType($type = RecordTypeEnum::getTypeFromName($rr['type']))
                ->setTtl($rr['ttl'] ?? $defaultTtl)
                ->setRdata($this->extractRdata($rr, $type, $parent));
        }

        return $resourceRecords;
    }

    /**
     * Determine if a $zone is in the legacy format.
     *
     * @param array $zone
     *
     * @return bool
     */
    protected function isLegacyFormat(array $zone): bool
    {
        $keys = array_map(function ($value) {
            return strtolower($value);
        }, array_keys($zone));

        return
            (false === array_search('domain', $keys, true)) ||
            (false === array_search('resource-records', $keys, true));
    }

    /**
     * @param array $zones
     *
     * @return array
     *
     * @throws UnsupportedTypeException
     */
    protected function processLegacyZone(array $zones): array
    {
        $resourceRecords = [];
        foreach ($zones as $domain => $types) {
            $domain = rtrim($domain, '.').'.';
            foreach ($types as $type => $data) {
                $data = (array) $data;
                $type = RecordTypeEnum::getTypeFromName($type);
                foreach ($data as $rdata) {
                    switch ($type) {
                        case RecordTypeEnum::TYPE_A:
                        case RecordTypeEnum::TYPE_AAAA:
                            $rdata = ['address' => $rdata];
                            break;
                        case RecordTypeEnum::TYPE_NS:
                        case RecordTypeEnum::TYPE_CNAME:
                        case RecordTypeEnum::TYPE_DNAME:
                        case RecordTypeEnum::TYPE_PTR:
                            $rdata = ['target' => $rdata];
                            break;
                        case RecordTypeEnum::TYPE_TXT:
                            $rdata = ['text' => $rdata];
                            break;
                    }
                    $resourceRecords[] = (new ResourceRecord())
                        ->setName($domain)
                        ->setClass($this->defaultClass)
                        ->setTtl($this->defaultTtl)
                        ->setRdata($this->extractRdata($rdata, $type, $domain));
                }
            }
        }

        return $resourceRecords;
    }
}
