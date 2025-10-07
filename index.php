<?php

use Cydran\System\Core\App;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>

<body>
    <?php App::header(); ?>
    <main>
        <?php App::slot(); ?>
    </main>
    <?php App::footer(); ?>
</body>

</html>