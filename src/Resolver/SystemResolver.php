<?php
/*
 * This file is part of PHP DNS Server.
 *
 * (c) Yif Swery <yiftachswr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace yswery\DNS\Resolver;

use yswery\DNS\Rdata;
use yswery\DNS\UnsupportedTypeException;
use yswery\DNS\ResourceRecord;
use yswery\DNS\RecordTypeEnum;

/**
 * Use the host system's configured DNS.
 */
class SystemResolver extends AbstractResolver
{
    /**
     * SystemResolver constructor.
     *
     * @param bool $recursionAvailable
     * @param bool $authoritative
     */
    public function __construct($recursionAvailable = true, $authoritative = false)
    {
        $this->allowRecursion = (bool) $recursionAvailable;
        $this->isAuthoritative = (bool) $authoritative;
    }

    /**
     * @param ResourceRecord[] $question
     *
     * @return ResourceRecord[]
     *
     * @throws UnsupportedTypeException
     */
    public function getAnswer(array $question): array
    {
        $answer = [];
        $query = $question[0];

        $records = $this->getRecordsRecursively($query->getName(), $query->getType());
        foreach ($records as $record) {
            $answer[] = (new ResourceRecord())
                ->setName($query->getName())
                ->setClass($query->getClass())
                ->setTtl($record['ttl'])
                ->setRdata($record['rdata']);
        }

        return $answer;
    }

    /**
     * @param string $domain
     * @param int    $type
     *
     * @return array
     *
     * @throws UnsupportedTypeException
     */
    private function getRecordsRecursively(string $domain, int $type): array
    {
        $records = dns_get_record($domain, $this->IANA2PHP($type));
        $result = [];

        foreach ($records as $record) {
            $result[] = [
                'rdata' => $this->extractPhpRdata($record),
                'ttl' => $record['ttl'],
            ];
        }

        return $result;
    }

    /**
     * @param array $resourceRecord
     *
     * @return Rdata
     *
     * @throws UnsupportedTypeException
     */
    protected function extractPhpRdata(array $resourceRecord): Rdata
    {
        $rdata = new Rdata(RecordTypeEnum::getTypeFromName($resourceRecord['type']));

        switch ($rdata->getType()) {
            case RecordTypeEnum::TYPE_A:
                $rdata->setAddress($resourceRecord['ip']);
                break;
            case RecordTypeEnum::TYPE_AAAA:
                $rdata->setAddress($resourceRecord['ipv6']);
                break;
            case RecordTypeEnum::TYPE_NS:
            case RecordTypeEnum::TYPE_CNAME:
            case RecordTypeEnum::TYPE_DNAME:
            case RecordTypeEnum::TYPE_PTR:
                $rdata->setTarget($resourceRecord['target']);
                break;
            case RecordTypeEnum::TYPE_SOA:
                $rdata->setMname($resourceRecord['mname'])
                    ->setRname($resourceRecord['rname'])
                    ->setSerial($resourceRecord['serial'])
                    ->setRefresh($resourceRecord['refresh'])
                    ->setRetry($resourceRecord['retry'])
                    ->setExpire($resourceRecord['expire'])
                    ->setMinimum($resourceRecord['minimum-ttl']);
                break;
            case RecordTypeEnum::TYPE_MX:
                $rdata->setPreference($resourceRecord['pri'])
                    ->setExchange($resourceRecord['host']);
                break;
            case RecordTypeEnum::TYPE_TXT:
                $rdata->setText($resourceRecord['txt']);
                break;
            case RecordTypeEnum::TYPE_SRV:
                $rdata->setPriority($resourceRecord['pri'])
                    ->setWeight($resourceRecord['weight'])
                    ->setPort($resourceRecord['port'])
                    ->setTarget($resourceRecord['target']);
                break;
            default:
                throw new UnsupportedTypeException(
                    sprintf('Resource Record type "%s" is not a supported type.', RecordTypeEnum::getName($type))
                );
        }

        return $rdata;
    }

    /**
     * Maps an IANA Rdata type to the built-in PHP DNS constant.
     *
     * @example $this->IANA_to_PHP(5) //Returns DNS_CNAME int(16)
     *
     * @param int $type the IANA RTYPE
     *
     * @return int the built-in PHP DNS_<type> constant or `false` if the type is not defined
     *
     * @throws UnsupportedTypeException|\InvalidArgumentException
     */
    private function IANA2PHP(int $type): int
    {
        $constantName = 'DNS_'.RecordTypeEnum::getName($type);
        if (!defined($constantName)) {
            throw new UnsupportedTypeException(sprintf('Record type "%d" is not a supported type.', $type));
        }

        $phpType = constant($constantName);

        if (!is_int($phpType)) {
            throw new \InvalidArgumentException(sprintf('Constant "%s" is not an integer.', $constantName));
        }

        return $phpType;
    }
}
