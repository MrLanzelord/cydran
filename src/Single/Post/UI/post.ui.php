<main>
	<?php if (have_posts()): ?>
       	<?php while (have_posts()): ?>
            <?php the_post(); ?>
            <h1><?= get_the_title() ?></h1>
            <?php the_content(); ?>
       	<?php endwhile; ?>
	<?php endif; ?>
</main>
