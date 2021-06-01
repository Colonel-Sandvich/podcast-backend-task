<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

use App\Models\Episode;

class EpisodeIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function testEpisodesAreCreated()
    {
        $this->withoutExceptionHandling();

        $episode = $this->metaData();

        $response = $this->postJson('/api/episodes', $episode);

        $response->assertCreated();
        $this->assertDatabaseHas('episodes', $episode);
    }

    public function testEpisodesAreNotCreatedIfInvalidUrl()
    {
        $episode = array_merge($this->metaData(), [
            'download_url' => "I'm not a URL!"
        ]);

        $response = $this->postJson('/api/episodes', $episode);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'download_url'
                ]
            ]);
        
        $this->assertDatabaseMissing('episodes', $episode);
    }

    public function testEpisodesAreNotCreatedIfTitleIsTooLong()
    {
        $episode = array_merge($this->metaData(), [
            'title' => str_repeat('A',256)
        ]);

        $response = $this->postJson('/api/episodes', $episode);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'title'
                ]
            ]);

        $this->assertDatabaseMissing('episodes', $episode);
    }

    public function testEpisodesAreNotCreatedIfDescriptionIsTooLong()
    {
        $episode = array_merge($this->metaData(), [
            'description' => str_repeat('A',5001)
        ]);

        $response = $this->postJson('/api/episodes', $episode);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'description'
                ]
            ]);

        $this->assertDatabaseMissing('episodes', $episode);
    }

    public function testEpisodesAreNotCreatedIfInvalidEpisodeNumber()
    {
        $episode = array_merge($this->metaData(), [
            'episode_number' => "I am not a number"
        ]);

        $response = $this->postJson('/api/episodes', $episode);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'episode_number'
                ]
            ]);

        $this->assertDatabaseMissing('episodes', $episode);
    }

    public function testEpisodesAreListed()
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

        $response->assertOk();

        $this->assertDatabaseHas('episodes', [
            'id' => $episode->id,
            'title' => $payload['title'],
        ]);
    }

    public function testEpisodesAreNotUpdatedIfInvalidUrl()
    {
        $old_episode = Episode::factory()->create();

        $episode = array_merge($this->metaData(), [
            'download_url' => "I'm not a URL!"
        ]);

        $response = $this->putJson('/api/episodes/'.$old_episode->id, $episode);
        
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'download_url'
                ]
            ]);

        $this->assertDatabaseMissing('episodes', $episode);
        $this->assertFalse($old_episode->wasChanged('download_url'));
    }

    public function testEpisodesAreNotUpdatedIfTitleIsTooLong()
    {
        $old_episode = Episode::factory()->create();

        $episode = array_merge($this->metaData(), [
            'title' => str_repeat('A',256)
        ]);

        $response = $this->putJson('/api/episodes/'.$old_episode->id, $episode);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'title'
                ]
            ]);

        $this->assertDatabaseMissing('episodes', $episode);
        $this->assertFalse($old_episode->wasChanged('title'));
    }

    public function testEpisodesAreNotUpdatedIfDescriptionIsTooLong()
    {
        $old_episode = Episode::factory()->create();

        $episode = array_merge($this->metaData(), [
            'description' => str_repeat('A',5001)
        ]);

        $response = $this->putJson('/api/episodes/'.$old_episode->id, $episode);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'description'
                ]
            ]);

        $this->assertDatabaseMissing('episodes', $episode);
        $this->assertFalse($old_episode->wasChanged('description'));
    }

    public function testEpisodesAreNotUpdatedIfInvalidEpisodeNumber()
    {
        $old_episode = Episode::factory()->create();

        $episode = array_merge($this->metaData(), [
            'episode_number' => "I am not a number"
        ]);

        $response = $this->putJson('/api/episodes/'.$old_episode->id, $episode);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'episode_number'
                ]
            ]);

        $this->assertDatabaseMissing('episodes', $episode);
        $this->assertFalse($old_episode->wasChanged('episode_number'));
    }

    public function testEpisodesAreDeleted()
    {
        $this->withoutExceptionHandling();

        $episode = Episode::factory()->create();

        $response = $this->deleteJson('/api/episodes/'.$episode->id);

        $response->assertNoContent();

        $this->assertDeleted($episode);
    }

    public function testEpisodesAreUploaded()
    {
        $this->withoutExceptionHandling();

        Storage::fake('episodes');

        $file = UploadedFile::fake()->create('episode1.wav', 100);

        $response = $this->post('/api/episodes/upload', [
            'file' => $file,
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'url'
            ]);

        $path = 'episodes/'.$file->hashName();

        Storage::assertExists($path);

        $this->assertFileEquals($file, Storage::path($path));
    }

    public function testEpisodesUploadedAreNotTooLarge()
    {
        Storage::fake('episodes');

        $file = UploadedFile::fake()->create('episode1.wav', 10000000);

        $response = $this->postJson('/api/episodes/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'file'
                ]
            ]);

        $path = 'episodes/'.$file->hashName();

        Storage::assertMissing($path);
    }

    public function testEpisodesUploadedAreAudioFiles()
    {
        Storage::fake('episodes');

        $file = UploadedFile::fake()->create('episode1.pdf', 100);

        $response = $this->postJson('/api/episodes/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'file'
                ]
            ]);

        $path = 'episodes/'.$file->hashName();

        Storage::assertMissing($path);
    }

    private function metaData()
    {
        return $episode = [
            'download_url' => 'http://foo',
            'title' => 'Title foo',
            'description' => 'Foo bar went to the food bar',
            'episode_number' => 10,
        ];

        // Clean meta data array that can be overridden with array_merge to make invalid data tests
    }
}
