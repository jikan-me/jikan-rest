<?php

namespace Tests\Unit;

use App\Producers;
use Tests\TestCase;

/**
 * Regression: the parser emits `name` and `external_links`; neither was fillable, so
 * strict mode threw (500) and production silently dropped them.
 */
final class ProducersFillableTest extends TestCase
{
    public function testParserShapedPayloadIsFullyFillable(): void
    {
        $p = new Producers;
        $p->fill(['mal_id' => 1, 'name' => 'Studio Pierrot', 'external_links' => [['name' => 'Official', 'url' => 'https://x.test']]]);

        $this->assertSame('Studio Pierrot', $p->name);
        $this->assertSame('Official', $p->external_links[0]['name']);
    }
}
