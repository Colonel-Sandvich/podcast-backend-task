<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function testDatabaseIsUsingMemory()
    {
        $this->assertTrue($this->usingInMemoryDatabase());
    }

    public function testDatabaseIsSqlite()
    {
        $this->assertSame('sqlite', $this->getConnection()->getConfig()['driver']);
    }
}
