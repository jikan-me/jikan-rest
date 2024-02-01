<?php

namespace Tests\Unit;

use Tests\TestCase;

final class JikanApiSearchableModelFixture extends \App\JikanApiSearchableModel
{
    public string $titleAttributeNameFixture;

    public function typesenseQueryBy(): array
    {
        return [];
    }

    public function getTitleAttributeName(): string
    {
        return $this->titleAttributeNameFixture;
    }
}

final class JikanApiSearchableModelTest extends TestCase
{
    public function titleFieldDataProvider()
    {
        return [
            ["name"],
            ["username"],
            ["title"]
        ];
    }

    /**
     * @dataProvider titleFieldDataProvider
     */
    public function testGetCollectionSchemaShouldReturnSortableTitleFieldInSchemaConfig($titleAttributeNameFixture)
    {
        $fixture = new JikanApiSearchableModelFixture();
        $fixture->titleAttributeNameFixture = $titleAttributeNameFixture;
        $schema = $fixture->getCollectionSchema();

        $this->assertArrayHasKey('fields', $schema);
        $this->assertArrayHasKey('name', $schema['fields'][1]);
        $this->assertArrayHasKey('type', $schema['fields'][1]);
        $this->assertArrayHasKey('sort', $schema['fields'][1]);
        $this->assertArrayHasKey('optional', $schema['fields'][1]);
        $this->assertEquals($titleAttributeNameFixture, $schema['fields'][1]['name']);
        $this->assertEquals('string', $schema['fields'][1]['type']);
        $this->assertTrue($schema['fields'][1]['sort']);
        $this->assertFalse($schema['fields'][1]['optional']);
    }
}
