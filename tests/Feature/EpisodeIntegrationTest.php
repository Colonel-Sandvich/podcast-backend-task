<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
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

        $response = $this->postJson('/api/episodes', $episode);

        $response->assertOk();
        $this->assertDatabaseHas('episodes', $episode);
    }

    public function testEpisodesAreListable()
    {
        $this->withoutExceptionHandling();

        $amountOfEpisodesToGenerate = 10;

        Episode::factory()->count($amountOfEpisodesToGenerate)->create();

        $response = $this->getJson('/api/episodes');

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

        $response = $this->putJson('/api/episodes/'.$episode->id, $payload);

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

        $response = $this->deleteJson('/api/episodes/'.$episode->id);

        $response->assertStatus(204);

        $this->assertDeleted($episode);
    }

    public function testEpisodesAreUploadable()
    {
        $this->withoutExceptionHandling();

        Storage::fake('episodes');

        $file = UploadedFile::fake()->create('episode1.wav', 100);

        $response = $this->post('/api/episodes/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(200);

        $path = 'episodes/'.$file->hashName();

        Storage::disk('episodes')->assertExists($path);

        $this->assertFileEquals($file, Storage::disk('episodes')->path($path));
    }

    public function testEpisodesUploadedAreNotTooLarge()
    {
        Storage::fake('episodes');

        $file = UploadedFile::fake()->create('episode1.wav', 10000000);

        $response = $this->post('/api/episodes/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(302);

        $path = 'episodes/'.$file->hashName();

        Storage::disk('episodes')->assertMissing($path);
    }

    public function testWeCannotUploadNonAudioFiles()
    {
        Storage::fake('episodes');

        $file = UploadedFile::fake()->create('episode1.pdf', 100);

        $response = $this->post('/api/episodes/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(302);

        $path = 'episodes/'.$file->hashName();

        Storage::disk('episodes')->assertMissing($path);
    }
}
