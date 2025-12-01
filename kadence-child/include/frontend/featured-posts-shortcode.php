<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Featured Posts Shortcode
 * Usage: [show_featured_posts]
 */
function display_featured_posts_shortcode() {
    // 1. Query for posts
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => 'featured',
                'value'   => 'Featured',
                'compare' => 'LIKE'
            )
        )
    );

    $query = new WP_Query($args);
    $post_count = $query->post_count;

    if ( ! $query->have_posts() ) {
        return '';
    }

    // 2. Determine Layout Mode
    $layout_class = 'layout-mode-3'; 
    if ( $post_count == 1 ) {
        $layout_class = 'layout-mode-1';
    } elseif ( $post_count == 2 ) {
        $layout_class = 'layout-mode-2';
    }

    ob_start(); 
    ?>

<style>
.featured-posts-container {
    margin: 0;
    padding: 0px;
    box-sizing: border-box;
}

.fp-section-label {
    display: block;
    color: #D40000;
    font-size: 16px;
    font-weight: 500;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 20px;
    font-family: Montserrat;
}

/* --- GRID SYSTEM --- */
.featured-posts-grid {
    display: grid;
    gap: 30px;
    grid-template-columns: 1fr;
}

@media (min-width: 768px) {
    .layout-mode-2 .featured-posts-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .layout-mode-3 .featured-posts-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* --- CARD STYLING --- */
.featured-post-card {
    background: #fff;
    display: flex;
    flex-direction: column;
    border-radius: 8px;
}

.fp-header {
    margin-bottom: 20px;
}

.fp-title {
    margin: 0 0 10px 0;
    font-size: 20px;
    line-height: 1.3;
    font-weight: 600;
    color: #0A1128;
    font-family: Montserrat;
}

.layout-mode-1 .fp-title {
    font-size: 38px;
}

.fp-title a {
    text-decoration: none;
    color: inherit;
}

.fp-excerpt {
    color: #555;
    font-size: 18px;
    margin-bottom: 15px;
    line-height: 1.6;
    font-family: Montserrat;
}

/* META DATA (Date & Author) */
.fp-meta {
    display: flex;
    gap: 20px;
    font-size: 14px;
    color: #888;
    font-family: Montserrat;
}

.fp-meta span {
    display: flex;
    align-items: center;
    gap: 8px;
    /* Space between icon and text */
}

/* SVG Icon Styling */
.fp-meta svg {
    width: 14px;
    height: 14px;
    fill: #D40000;
    /* Icon color (Red match) or use 'currentColor' for gray */
}

/* --- IMAGE STYLING --- */
.fp-image {
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
    margin-top: auto;
}

.fp-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.fp-image:hover img {
    transform: scale(1.02);
}

.layout-mode-1 .fp-image {
    height: 500px;
}

.layout-mode-2 .fp-image {
    height: 350px;
}

.layout-mode-3 .fp-image {
    height: 250px;
}

@media (max-width: 767px) {
    .layout-mode-1 .fp-image {
        height: 250px;
    }

    .layout-mode-1 .fp-title {
        font-size: 26px;
    }
}
</style>

<div class="featured-posts-container <?php echo esc_attr($layout_class); ?>">

    <span class="fp-section-label">FEATURED POST</span>

    <div class="featured-posts-grid">
        <?php while ( $query->have_posts() ) : $query->the_post(); 
                $image_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
                $author_name = get_the_author();
                $date = get_the_date('d F Y');
            ?>

        <article class="featured-post-card">
            <div class="fp-header">
                <h3 class="fp-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h3>

                <div class="fp-excerpt">
                    <?php echo wp_trim_words( get_the_excerpt(), 20, '...' ); ?>
                </div>

                <div class="fp-meta">
                    <span class="fp-date">
                        <svg aria-hidden="true" class="e-font-icon-svg e-far-calendar" viewBox="0 0 448 512"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M400 64h-48V12c0-6.6-5.4-12-12-12h-40c-6.6 0-12 5.4-12 12v52H160V12c0-6.6-5.4-12-12-12h-40c-6.6 0-12 5.4-12 12v52H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48zm-6 400H54c-3.3 0-6-2.7-6-6V160h352v298c0 3.3-2.7 6-6 6z">
                            </path>
                        </svg>
                        <?php echo $date; ?>
                    </span>

                    <span class="fp-author">
                        <svg aria-hidden="true" class="e-font-icon-svg e-far-user" viewBox="0 0 448 512"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M313.6 304c-28.7 0-42.5 16-89.6 16-47.1 0-60.8-16-89.6-16C60.2 304 0 364.2 0 438.4V464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48v-25.6c0-74.2-60.2-134.4-134.4-134.4zM400 464H48v-25.6c0-47.6 38.8-86.4 86.4-86.4 14.6 0 38.3 16 89.6 16 51.7 0 74.9-16 89.6-16 47.6 0 86.4 38.8 86.4 86.4V464zM224 288c79.5 0 144-64.5 144-144S303.5 0 224 0 80 64.5 80 144s64.5 144 144 144zm0-240c52.9 0 96 43.1 96 96s-43.1 96-96 96-96-43.1-96-96 43.1-96 96-96z">
                            </path>
                        </svg>
                        <?php echo $author_name; ?>
                    </span>
                </div>
            </div>

            <?php if ( $image_url ) : ?>
            <div class="fp-image">
                <a href="<?php the_permalink(); ?>">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>">
                </a>
            </div>
            <?php endif; ?>
        </article>

        <?php endwhile; ?>
    </div>
</div>

<?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode( 'show_featured_posts', 'display_featured_posts_shortcode' );