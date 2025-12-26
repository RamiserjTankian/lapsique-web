<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Post;
use Illuminate\Contracts\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->published()
            ->with('author', 'media')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(12);

        $featuredEvent = Event::query()
            ->orderByDesc('is_featured')
            ->orderByDesc('starts_at')
            ->orderByDesc('created_at')
            ->first();

        return view('posts.index', compact('posts', 'featuredEvent'));
    }

    public function show(Post $post): View
    {
        // Only show published posts to public
        if (!$post->is_published || ($post->published_at && $post->published_at > now())) {
            abort(404);
        }

        $post->load(['author', 'media']);
        $post->incrementViews();

        // Get related posts
        $relatedPosts = Post::query()
            ->published()
            ->where('id', '!=', $post->id)
            ->with('media')
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return view('posts.show', compact('post', 'relatedPosts'));
    }
}
