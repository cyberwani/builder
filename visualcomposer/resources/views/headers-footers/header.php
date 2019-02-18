<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
/** @var string $sourceId */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php if (!current_theme_supports('title-tag')) : ?>
        <title>
            <?php echo wp_title(); ?>
        </title>
    <?php endif; ?>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php vchelper('Frontend')->renderContent($sourceId);
