<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Gallery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_files_pagination()
    {
        $user = User::factory()->create();

        // Create 15 galleries for this user
        for ($i = 1; $i <= 15; $i++) {
            Gallery::create([
                'user_id' => $user->id,
                'nama_tampilan' => "File-$i.txt",
                'ukuran' => 100,
                'file' => "file-$i.txt",
                'path' => "users/{$user->id}/original/file-$i.txt",
                'status' => 'ready',
                'preview_type' => 'text',
                'extension' => 'txt',
                'mime_type' => 'text/plain',
                'izin' => 1,
            ]);
        }

        // Authenticate the user
        $this->actingAs($user);

        // Fetch page 1
        $response1 = $this->getJson('/api/files?page=1&per_page=10');
        $response1->assertStatus(200);
        $data1 = $response1->json();

        $this->assertCount(10, $data1['data']);
        $this->assertEquals(15, $data1['total']);
        $this->assertEquals(1, $data1['page']);
        $this->assertEquals(10, $data1['perPage']);
        $this->assertEquals(2, $data1['lastPage']);

        // Fetch page 2
        $response2 = $this->getJson('/api/files?page=2&per_page=10');
        $response2->assertStatus(200);
        $data2 = $response2->json();

        $this->assertCount(5, $data2['data']);

        // Check that page 1 and page 2 are disjoint
        $ids1 = collect($data1['data'])->pluck('id')->toArray();
        $ids2 = collect($data2['data'])->pluck('id')->toArray();
        $intersect = array_intersect($ids1, $ids2);
        $this->assertEmpty($intersect, "Page 1 and Page 2 contain duplicate items");
    }
}
