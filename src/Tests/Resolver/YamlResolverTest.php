<?php

namespace yswery\DNS\Tests\Resolver;

use Symfony\Component\Yaml\Exception\ParseException;
use yswery\DNS\Rdata;
use yswery\DNS\Resolver\YamlResolver;
use yswery\DNS\ResourceRecord;
use yswery\DNS\RecordTypeEnum;

class YamlResolverTest extends AbstractResolverTest
{
    /**
     * @throws \Exception
     */
    public function setUp()
    {
        $files = [
            __DIR__.'/../Resources/records.yml',
            __DIR__.'/../Resources/example.com.yml',
        ];
        $this->resolver = new YamlResolver($files);
    }

    /**
     * @throws \Exception
     */
    public function testParseException()
    {
        $this->expectException(ParseException::class);
        new YamlResolver([__DIR__.'/../Resources/invalid_dns_records.json']);
    }

    public function testResolveLegacyRecord()
    {
        $question[] = (new ResourceRecord())
            ->setName('test2.com.')
            ->setType(RecordTypeEnum::TYPE_MX)
            ->setQuestion(true);

        $expectation[] = (new ResourceRecord())
            ->setName('test2.com.')
            ->setType(RecordTypeEnum::TYPE_MX)
            ->setTtl(300)
            ->setRdata((new Rdata(RecordTypeEnum::TYPE_MX))
                ->setPreference(20)
                ->setExchange('mail-gw1.test2.com.')
            );

        $expectation[] = (new ResourceRecord())
            ->setName('test2.com.')
            ->setType(RecordTypeEnum::TYPE_MX)
            ->setTtl(300)
            ->setRdata((new Rdata(RecordTypeEnum::TYPE_MX))
                ->setPreference(30)
                ->setExchange('mail-gw2.test2.com.')
            );

        $this->assertEquals($expectation, $this->resolver->getAnswer($question));
    }
}
