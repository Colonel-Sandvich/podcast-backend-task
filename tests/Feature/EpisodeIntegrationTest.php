<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Episode;

class EpisodeIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the ability to create and store episodes.
     *
     * @return void
     */
    public function testEpisodesAreCreated()
    {
        $this->withoutExceptionHandling();

        $episode = [
            'download_url' => 'http://foo',
            'title' => 'Title foo',
            'description' => 'Foo bar went to the food bar',
            'episode_number' => 10,
        ];

        $response = $this->json('POST', '/api/episodes', $episode);

        $response->assertOk();
        $this->assertDatabaseHas('episodes', $episode);
    }

    public function testEpisodesAreListable()
    {
        $this->withoutExceptionHandling();

        $amountOfEpisodesToGenerate = 10;

        Episode::factory()->count($amountOfEpisodesToGenerate)->create();

        $response = $this->json('GET', '/api/episodes');

        $response->assertOk()
            ->assertJsonStructure([
                '*' => ['id', 'download_url', 'title', 'description', 'episode_number', 'created_at', 'updated_at',]
            ]);
        
        $this->assertDatabaseCount('episodes', sizeof($response->json()));
    }
    
    public function testEpisodesAreUpdated()
    {
        $this->withoutExceptionHandling();

        $episode = Episode::factory()->create([
            'title' => 'Old value',
        ]);

        $payload = [
            'title' => 'New value',
        ];

        $response = $this->json('PUT', '/api/episodes/'.$episode->id, $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('episodes', [
            'id' => $episode->id,
            'title' => $payload['title'],
        ]);
    }

    public function testEpisodesAreDeleted()
    {
        $this->withoutExceptionHandling();

        $episode = Episode::factory()->create();

        $response = $this->json('DELETE', '/api/episodes/'.$episode->id);

        $response->assertStatus(204);

        $this->assertDeleted($episode);
    }
}
