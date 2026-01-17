<?php

declare(strict_types=1);

namespace App\Page\Blog;

use Cydran\Contracts\HasAssets;

class BlogPageContext
{
    public \WP_Post|array|null $page;
    /** @var \WP_Post[]|int[] */
    public array $posts;

    public function __construct()
    {
        $this->page = get_page_by_path('blog');
        $this->posts = get_posts([
            'post_type' => 'post',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'category_name' => $this->page->slug,
        ]);
    }
}
