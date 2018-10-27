<?php

namespace yswery\DNS\Resolver;

use yswery\DNS\Rdata;
use yswery\DNS\ResourceRecord;
use yswery\DNS\RecordTypeEnum;
use yswery\DNS\UnsupportedTypeException;

abstract class AbstractResolver implements ResolverInterface
{
    /**
     * @var bool
     */
    protected $allowRecursion;

    /**
     * @var bool
     */
    protected $isAuthoritative;

    /**
     * @var ResourceRecord[]
     */
    protected $resourceRecords = [];

    /**
     * Wildcard records are stored as an associative array of labels in reverse. E.g.
     * ResourceRecord for "*.example.com." is stored as ['com']['example']['*'][<CLASS>][<TYPE>][].
     *
     * @var ResourceRecord[]
     */
    protected $wildcardRecords = [];

    /**
     * @param ResourceRecord[] $queries
     *
     * @return array
     */
    public function getAnswer(array $queries): array
    {
        $answers = [];
        foreach ($queries as $query) {
            $answer = $this->resourceRecords[$query->getName()][$query->getType()][$query->getClass()] ?? [];
            if (empty($answer)) {
                $answer = $this->findWildcardEntry($query);
            }

            $answers = array_merge($answers, $answer);
        }

        return $answers;
    }

    /**
     * {@inheritdoc}
     */
    public function allowsRecursion(): bool
    {
        return $this->allowRecursion;
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthority($domain): bool
    {
        return $this->isAuthoritative;
    }

    /**
     * Determine if a domain is a wildcard domain.
     *
     * @param string $domain
     *
     * @return bool
     */
    public function isWildcardDomain(string $domain): bool
    {
        $domain = rtrim($domain, '.').'.';
        $pattern = '/^\*\.(?:[a-zA-Z0-9\-\_]+\.)*$/';

        return (bool) preg_match($pattern, $domain);
    }

    /**
     * @param ResourceRecord[] $resourceRecords
     */
    protected function addZone(array $resourceRecords): void
    {
        foreach ($resourceRecords as $resourceRecord) {
            if ($this->isWildcardDomain($resourceRecord->getName())) {
                $this->addWildcardRecord($resourceRecord);
                continue;
            }
            $this->resourceRecords[$resourceRecord->getName()][$resourceRecord->getType()][$resourceRecord->getClass()][] = $resourceRecord;
        }
    }

    /**
     * Add a wildcard ResourceRecord.
     *
     * @param ResourceRecord $resourceRecord
     */
    protected function addWildcardRecord(ResourceRecord $resourceRecord): void
    {
        $labels = explode('.', rtrim($resourceRecord->getName(), '.'));
        $labels = array_reverse($labels);

        $array = &$this->wildcardRecords;
        foreach ($labels as $label) {
            if ('*' === $label) {
                $array[$label][$resourceRecord->getClass()][$resourceRecord->getType()][] = $resourceRecord;
                break;
            }

            $array = &$array[$label];
        }
    }

    /**
     * @param ResourceRecord $query
     *
     * @return array
     */
    protected function findWildcardEntry(ResourceRecord $query): array
    {
        $labels = explode('.', rtrim($query->getName(), '.'));
        $labels = array_reverse($labels);

        /** @var ResourceRecord[] $wildcards */
        $wildcards = [];
        $array = &$this->wildcardRecords;
        foreach ($labels as $label) {
            if (array_key_exists($label, $array)) {
                $array = &$array[$label];
                continue;
            }

            if (array_key_exists('*', $array)) {
                $wildcards = $array['*'][$query->getClass()][$query->getType()] ?? null;
            }
        }

        $answers = [];
        foreach ($wildcards as $wildcard) {
            $rr = clone $wildcard;
            $rr->setName($query->getName());
            $answers[] = $rr;
        }

        return $answers;
    }

    /**
     * Add the parent domain to names that are not fully qualified.
     *
     * AbstractResolver::handleName('www', 'example.com.') //Outputs 'www.example.com.'
     * AbstractResolver::handleName('@', 'example.com.') //Outputs 'example.com.'
     * AbstractResolver::handleName('ns1.example.com.', 'example.com.') //Outputs 'ns1.example.com.'
     *
     * @param $name
     * @param $parent
     *
     * @return string
     */
    protected function handleName(string $name, string $parent)
    {
        if ('@' === $name || '' === $name) {
            return $parent;
        }

        if ('.' !== substr($name, -1, 1)) {
            return $name.'.'.$parent;
        }

        return $name;
    }

    /**
     * @param array  $resourceRecord
     * @param int    $type
     * @param string $parent
     *
     * @return Rdata
     *
     * @throws UnsupportedTypeException
     */
    protected function extractRdata(array $resourceRecord, int $type, string $parent): Rdata
    {
        $rdata = new Rdata($type);

        switch ($type) {
            case RecordTypeEnum::TYPE_A:
            case RecordTypeEnum::TYPE_AAAA:
                $rdata->setAddress($resourceRecord['address']);
                break;
            case RecordTypeEnum::TYPE_NS:
            case RecordTypeEnum::TYPE_CNAME:
            case RecordTypeEnum::TYPE_DNAME:
            case RecordTypeEnum::TYPE_PTR:
                $rdata->setTarget($resourceRecord['target']);
                break;
            case RecordTypeEnum::TYPE_SOA:
                $rdata->setMname($this->handleName($resourceRecord['mname'], $parent))
                    ->setRname($this->handleName($resourceRecord['rname'], $parent))
                    ->setSerial($resourceRecord['serial'])
                    ->setRefresh($resourceRecord['refresh'])
                    ->setRetry($resourceRecord['retry'])
                    ->setExpire($resourceRecord['expire'])
                    ->setMinimum($resourceRecord['minimum']);
                break;
            case RecordTypeEnum::TYPE_MX:
                $rdata->setPreference($resourceRecord['preference'])
                    ->setExchange($this->handleName($resourceRecord['exchange'], $parent));
                break;
            case RecordTypeEnum::TYPE_TXT:
                $rdata->setText($resourceRecord['text']);
                break;
            case RecordTypeEnum::TYPE_SRV:
                $rdata->setPriority($resourceRecord['priority'])
                    ->setWeight($resourceRecord['weight'])
                    ->setPort($resourceRecord['port'])
                    ->setTarget($this->handleName($resourceRecord['target'], $parent));
                break;
            default:
                throw new UnsupportedTypeException(
                    sprintf('Resource Record type "%s" is not a supported type.', RecordTypeEnum::getName($type))
                );
        }

        return $rdata;
    }
}
