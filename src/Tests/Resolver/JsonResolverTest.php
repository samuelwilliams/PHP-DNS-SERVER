<?php

namespace yswery\DNS\Tests\Resolver;

use yswery\DNS\Rdata;
use yswery\DNS\Resolver\JsonResolver;
use yswery\DNS\ResourceRecord;
use yswery\DNS\RecordTypeEnum;

class JsonResolverTest extends AbstractResolverTest
{
    /**
     * @throws \yswery\DNS\UnsupportedTypeException
     */
    public function setUp()
    {
        $files = [
            __DIR__.'/../Resources/example.com.json',
            __DIR__.'/../Resources/test_records.json',
        ];
        $this->resolver = new JsonResolver($files, 300);
    }

    public function testResolveLegacyRecord()
    {
        $question[] = (new ResourceRecord())
            ->setName('test.com.')
            ->setType(RecordTypeEnum::TYPE_A)
            ->setQuestion(true);

        $expectation[] = (new ResourceRecord())
            ->setName('test.com.')
            ->setTtl(300)
            ->setRdata((new Rdata(RecordTypeEnum::TYPE_A))->setAddress('111.111.111.111'));

        $this->assertEquals($expectation, $this->resolver->getAnswer($question));
    }

    /**
     * @throws \yswery\DNS\UnsupportedTypeException
     */
    public function testIsWildcardDomain()
    {
        $input1 = '*.example.com.';
        $input2 = '*.sub.domain.com.';
        $input3 = '*';
        $input4 = 'www.test.com.au.';

        $resolver = new JsonResolver([]);

        $this->assertTrue($resolver->isWildcardDomain($input1));
        $this->assertTrue($resolver->isWildcardDomain($input2));
        $this->assertTrue($resolver->isWildcardDomain($input3));
        $this->assertFalse($resolver->isWildcardDomain($input4));
    }
}
