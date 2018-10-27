<?php
/*
 * This file is part of PHP DNS Server.
 *
 * (c) Yif Swery <yiftachswr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace yswery\DNS;

class ResourceRecord
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $type;

    /**
     * @var int
     */
    private $ttl;

    /**
     * @var Rdata
     */
    private $rdata;

    /**
     * @var int
     */
    private $class = ClassEnum::INTERNET;

    /**
     * @var bool
     */
    private $question = false;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return ResourceRecord
     */
    public function setName(string $name): ResourceRecord
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return ResourceRecord
     *
     * @throws \LogicException
     */
    public function setType(int $type): ResourceRecord
    {
        if (isset($this->rdata)) {
            throw new \LogicException('The Resource Record type can only be set if the Rdata has not been set.');
        }
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * @param int $ttl
     *
     * @return ResourceRecord
     */
    public function setTtl(int $ttl): ResourceRecord
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * @return Rdata
     */
    public function getRdata()
    {
        return $this->rdata;
    }

    /**
     * @param Rdata $rdata
     *
     * @return ResourceRecord
     */
    public function setRdata(Rdata $rdata): ResourceRecord
    {
        $this->rdata = $rdata;
        $this->type = $rdata->getType();

        return $this;
    }

    /**
     * @return int
     */
    public function getClass(): int
    {
        return $this->class;
    }

    /**
     * @param int $class
     *
     * @return ResourceRecord
     */
    public function setClass(int $class): ResourceRecord
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return bool
     */
    public function isQuestion(): bool
    {
        return $this->question;
    }

    /**
     * @param bool $question
     *
     * @return ResourceRecord
     */
    public function setQuestion(bool $question): ResourceRecord
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            '%s %s %s %s %s',
            $this->name,
            RecordTypeEnum::getName($this->type),
            ClassEnum::getName($this->class),
            $this->ttl,
            (string) $this->rdata
        );
    }
}
