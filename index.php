<?php

require_once 'config.php';

/*
|--------------------------------------------------------------------------
| HELPER
|--------------------------------------------------------------------------
*/

function h($v)
{
    return htmlspecialchars(
        (string) $v,
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| LOAD SITE SETTINGS
|--------------------------------------------------------------------------
*/

$settings = [];

foreach (
    $pdo->query(
        'SELECT setting_key, setting_value FROM site_settings'
    ) as $row
) {
    $settings[$row['setting_key']] = $row['setting_value'];
}


/*
|--------------------------------------------------------------------------
| LOAD SKILLS
|--------------------------------------------------------------------------
*/

$skills = $pdo
    ->query(
        'SELECT *
         FROM skills
         ORDER BY sort_order, id'
    )
    ->fetchAll();


/*
|--------------------------------------------------------------------------
| LOAD PROJECTS
|--------------------------------------------------------------------------
*/

$projects = $pdo
    ->query(
        'SELECT *
         FROM projects
         ORDER BY sort_order, id DESC'
    )
    ->fetchAll();


/*
|--------------------------------------------------------------------------
| LOAD TYPING ROLES
|--------------------------------------------------------------------------
*/

$typingRoles = $pdo
    ->query(
        "SELECT text
         FROM typing_roles
         WHERE is_active = 1
         ORDER BY sort_order, id"
    )
    ->fetchAll(PDO::FETCH_COLUMN);

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        <?= h($settings['site_name'] ?? 'Portfolio') ?>
        |
        Portfolio
    </title>


    <meta
        name="description"
        content="<?= h($settings['hero_text'] ?? '') ?>"
    >


    <!-- Google Fonts -->

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <!-- Main CSS -->

    <link
        rel="stylesheet"
        href="style.css"
    >


    <style>
        /* =========================================================
           PROJECT CARD - COMPACT DESCRIPTION
        ========================================================= */

        .project-description-wrap {
            margin-top: 14px;
            margin-bottom: 0;
        }

        .project-description-preview {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            overflow: hidden;
            max-height: 4.8em;
            margin: 0;
        }

        /*
        |--------------------------------------------------------------------------
        | SEE MORE
        |--------------------------------------------------------------------------
        */

        .project-see-more {
            display: flex;
            align-items: center;
            width: fit-content;
            margin: 16px 0 14px;
            padding: 0;
            border: 0;
            background: transparent;
            color: inherit;
            font: inherit;
            font-weight: 800;
            line-height: 1.2;
            cursor: pointer;
            transition:
                opacity 0.2s ease,
                transform 0.2s ease;
        }

        .project-see-more::after {
            content: '';
            display: block;
            width: 28px;
            height: 1px;
            margin-left: 8px;
            background: currentColor;
            opacity: 0.55;
            transition:
                width 0.2s ease,
                opacity 0.2s ease;
        }

        .project-see-more:hover {
            opacity: 0.85;
            transform: translateX(2px);
        }

        .project-see-more:hover::after {
            width: 38px;
            opacity: 0.9;
        }

        /*
        |--------------------------------------------------------------------------
        | CARD TAGS
        |--------------------------------------------------------------------------
        */

        .project-body .tags {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-top: 2px;
        }

        .project-body .tags span {
            line-height: 1;
        }

        .project-body > h3 {
            margin-bottom: 0;
        }

        .project-body > .project-see-more + .tags {
            margin-top: 0;
        }


        /* =========================================================
           PROJECT MODAL
        ========================================================= */

        body.project-modal-open {
            overflow: hidden;
        }

        .project-modal {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .project-modal.is-open {
            display: flex;
        }

        .project-modal-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.78);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .project-modal-dialog {
            position: relative;
            z-index: 1;
            width: min(950px, 100%);
            max-height: min(90vh, 900px);
            overflow: hidden;
            border-radius: 20px;
            background: #101010;
            color: #fff;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.12);
            animation: projectModalIn 0.25s ease;
        }

        @keyframes projectModalIn {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .project-modal-content {
            max-height: min(90vh, 900px);
            overflow-y: auto;
            padding: 34px;
            scrollbar-width: thin;
        }

        .project-modal-close {
            position: absolute;
            top: 14px;
            right: 14px;
            z-index: 5;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.55);
            color: #fff;
            font-size: 28px;
            line-height: 1;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .project-modal-close:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: rotate(90deg);
        }

        .project-modal-header {
            padding-right: 55px;
            margin-bottom: 25px;
        }

        .project-modal-eyebrow {
            margin: 0 0 8px;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            opacity: 0.55;
        }

        .project-modal-header h2 {
            margin: 0;
            font-size: clamp(1.7rem, 4vw, 2.5rem);
            line-height: 1.15;
        }


        /* =========================================================
           MODAL GALLERY
        ========================================================= */

        .project-modal-gallery { margin-bottom: 30px; }
        .gallery-main { position:relative; display:flex; align-items:center; justify-content:center; min-height:240px; border-radius:14px; overflow:hidden; background:#080808; border:1px solid rgba(255,255,255,.1); }
        .gallery-main img { display:block; width:100%; max-height:520px; object-fit:contain; user-select:none; -webkit-user-drag:none; }
        .gallery-control { position:absolute; top:50%; transform:translateY(-50%); width:42px; height:42px; border:1px solid rgba(255,255,255,.2); border-radius:50%; color:#fff; background:rgba(0,0,0,.65); cursor:pointer; font-size:22px; }
        .gallery-prev { left:12px; } .gallery-next { right:12px; }
        .gallery-tools { display:flex; justify-content:space-between; align-items:center; gap:12px; margin:10px 0 14px; color:rgba(255,255,255,.65); font-size:.82rem; }
        .gallery-fullscreen { border:1px solid rgba(255,255,255,.18); border-radius:8px; padding:7px 10px; color:#fff; background:rgba(255,255,255,.07); cursor:pointer; }
        .gallery-thumbnails { display:flex; gap:10px; overflow-x:auto; padding:2px 1px 10px; }
        .gallery-thumb { flex:0 0 96px; height:68px; padding:0; overflow:hidden; border:2px solid transparent; border-radius:9px; background:#080808; cursor:pointer; opacity:.68; }
        .gallery-thumb.active { border-color:#fff; opacity:1; }
        .gallery-thumb img { width:100%; height:100%; display:block; object-fit:cover; }
        .project-modal-gallery.is-fullscreen { position:fixed; inset:0; z-index:100000; display:flex; flex-direction:column; justify-content:center; padding:30px; margin:0; background:rgba(0,0,0,.97); }
        .project-modal-gallery.is-fullscreen .gallery-main { flex:1; border:0; background:transparent; }
        .project-modal-gallery.is-fullscreen .gallery-main img { max-height:75vh; }
        .project-modal-gallery.is-fullscreen .gallery-tools, .project-modal-gallery.is-fullscreen .gallery-thumbnails { flex-shrink:0; }
        @media (max-width:700px) { .gallery-main { min-height:190px; } .gallery-main img { max-height:360px; } .gallery-thumb { flex-basis:78px; height:58px; } .project-modal-gallery.is-fullscreen { padding:12px; } }


        /* =========================================================
           MODAL DESCRIPTION
        ========================================================= */

        .project-modal-description {
            margin-top: 5px;
        }

        .project-modal-description h3 {
            margin: 0 0 12px;
            font-size: 1.05rem;
        }

        .project-modal-description p {
            margin: 0;
            line-height: 1.8;
            white-space: normal;
            overflow-wrap: anywhere;
            opacity: 0.82;
        }


        /* =========================================================
           MODAL TAGS
        ========================================================= */

        .project-modal-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 22px;
        }

        .project-modal-tags span {
            display: inline-flex;
            align-items: center;
            padding: 7px 11px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.10);
            font-size: 0.78rem;
        }

        .project-modal-actions {
            margin-top: 25px;
        }


        /* =========================================================
           MOBILE
        ========================================================= */

        @media (max-width: 700px) {

            .project-modal {
                padding: 12px;
            }

            .project-modal-dialog {
                max-height: 94vh;
                border-radius: 16px;
            }

            .project-modal-content {
                max-height: 94vh;
                padding: 24px 18px;
            }

            .project-modal-close {
                width: 38px;
                height: 38px;
                top: 10px;
                right: 10px;
                font-size: 24px;
            }

            .project-modal-gallery {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .project-modal-image:first-child {
                grid-column: auto;
            }

            .project-modal-image img {
                max-height: 400px;
            }

            .project-modal-header {
                padding-right: 48px;
            }
        }
    </style>

</head>


<body>


<!-- =========================================================
     NAVIGATION
========================================================= -->

<header class="navbar">


    <!-- LOGO -->

    <a
        href="#home"
        class="logo"
    >

        <?= h($settings['site_name'] ?? 'SMARTER') ?>

        <span class="online-dot"></span>

    </a>


    <!-- MOBILE MENU -->

    <button
        class="menu-toggle"
        id="menuToggle"
        type="button"
        aria-label="Open navigation"
    >
        ☰
    </button>


    <!-- NAVIGATION -->

    <nav id="navMenu">

        <a href="#home">
            Home
        </a>

        <a href="#about">
            About
        </a>

        <a href="#expertise">
            Expertise
        </a>

        <a href="#projects">
            Projects
        </a>

        <a href="#contact">
            Contact
        </a>

    </nav>


    <!-- SOCIAL LINKS -->

    <div class="socials">

        <?php if (!empty($settings['linkedin'])): ?>

            <a
                href="<?= h($settings['linkedin']) ?>"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="LinkedIn"
            >
                in
            </a>

        <?php endif; ?>


        <?php if (!empty($settings['github'])): ?>

            <a
                href="<?= h($settings['github']) ?>"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="GitHub"
            >
                ⌂
            </a>

        <?php endif; ?>


        <?php if (!empty($settings['facebook'])): ?>

            <a
                href="<?= h($settings['facebook']) ?>"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Facebook"
            >
                ◉
            </a>

        <?php endif; ?>

    </div>


</header>



<main>


<!-- =========================================================
     HERO
========================================================= -->

<section
    class="hero"
    id="home"
>


    <!-- BACKGROUND SHAPES -->

    <div class="blob blob-one"></div>

    <div class="blob blob-two"></div>

    <div class="blob blob-three"></div>


    <!-- HERO CONTENT -->

    <div class="hero-content">


        <p class="greeting">

            <?= h($settings['greeting'] ?? 'Hello') ?>

            <span></span>

        </p>


        <h1 class="typing-title">

            I'm

            <strong id="typingText"></strong>

            <span class="typing-cursor"></span>

        </h1>


        <p class="hero-text">

            <?= nl2br(
                h($settings['hero_text'] ?? '')
            ) ?>

        </p>


        <!-- BUTTONS -->

        <div class="hero-buttons">

            <a
                href="#projects"
                class="btn btn-dark"
            >
                View My Projects
            </a>


            <a
                href="#contact"
                class="btn btn-outline"
            >
                Contact Me
            </a>

        </div>


        <!-- MINI SKILLS -->

        <div class="mini-stack">

            <?php foreach ($skills as $skill): ?>

                <span>
                    <?= h($skill['name']) ?>
                </span>

            <?php endforeach; ?>

        </div>


    </div>



    <!-- HERO IMAGE -->

    <div class="hero-image-wrap">

    <div class="paint"></div>

    <img
        src="profile.png"
        alt="Profile"
        class="hero-image"
        draggable="false"
        oncontextmenu="return false;"
        ondragstart="return false;"
        onerror="
            this.style.display='none';
            document.querySelector('.photo-placeholder').style.display='flex';
        "
    >

    <div class="photo-placeholder">
        <span>
            YOUR
            <br>
            PHOTO
        </span>
    </div>

</div>


</section>



<!-- =========================================================
     ABOUT
========================================================= -->

<section
    class="about section"
    id="about"
>


    <div class="section-heading">

        <p>
            ABOUT ME
        </p>


        <h2>

            <?= h(
                $settings['about_title'] ?? 'About Me'
            ) ?>

        </h2>

    </div>



    <div class="about-grid">


        <div>


            <p>

                <b>

                    <?= h(
                        $settings['name'] ?? ''
                    ) ?>

                </b>

            </p>


            <p>

                <?= nl2br(
                    h(
                        $settings['about_text'] ?? ''
                    )
                ) ?>

            </p>


        </div>



        <div class="stats">


            <div>

                <b>
                    <?= count($skills) ?>
                </b>

                <small>
                    Core Skills
                </small>

            </div>


            <div>

                <b>
                    <?= count($projects) ?>
                </b>

                <small>
                    Projects
                </small>

            </div>


            <div>

                <b>
                    100%
                </b>

                <small>
                    Responsive Design
                </small>

            </div>


        </div>


    </div>


</section>



<!-- =========================================================
     EXPERTISE
========================================================= -->

<section
    class="expertise section"
    id="expertise"
>


    <div class="section-heading center">


        <p>
            EXPERTISE
        </p>


        <h2>

            My

            <span>
                technology stack
            </span>

        </h2>


    </div>



    <div class="skills-grid">


        <?php foreach ($skills as $skill): ?>


            <article class="skill-card">


                <div class="skill-icon">


                    <?php if (!empty($skill['icon_image'])): ?>

                        <img
                            src="admin/<?= h($skill['icon_image']) ?>"
                            alt="<?= h($skill['name']) ?> icon"
                            loading="lazy"
                        >

                    <?php elseif (!empty($skill['icon'])): ?>

                        <?= h($skill['icon']) ?>

                    <?php else: ?>

                        <?= h($skill['name']) ?>

                    <?php endif; ?>


                </div>



                <h3>

                    <?= h(
                        $skill['name']
                    ) ?>

                </h3>



                <p>

                    <?= nl2br(
                        h(
                            $skill['description']
                        )
                    ) ?>

                </p>


            </article>


        <?php endforeach; ?>


    </div>


</section>



<!-- =========================================================
     PROJECTS
========================================================= -->

<section
    class="projects section"
    id="projects"
>


    <div class="section-heading">


        <p>
            SELECTED WORK
        </p>


        <h2>

            Featured

            <span>
                projects
            </span>

        </h2>


    </div>



    <div class="project-grid">


        <?php if (empty($projects)): ?>


            <div class="project-empty">

                <p>
                    No projects available yet.
                </p>

            </div>


        <?php endif; ?>



        <?php foreach ($projects as $project): ?>


            <?php

            /*
            |--------------------------------------------------------------------------
            | PROJECT IMAGES
            |--------------------------------------------------------------------------
            |
            | Database example:
            |
            | image1.jpg,image2.jpg
            |
            | or:
            |
            | uploads/projects/image1.jpg,
            | uploads/projects/image2.jpg
            |
            */

            $projectImages = array_filter(
                array_map(
                    'trim',
                    explode(
                        ',',
                        $project['image'] ?? ''
                    )
                )
            );

            ?>


            <?php
            /*
            |--------------------------------------------------------------------------
            | PROJECT TAGS
            |--------------------------------------------------------------------------
            */

            $tags = array_filter(
                array_map(
                    'trim',
                    explode(',', $project['tags'] ?? '')
                )
            );

            /*
            |--------------------------------------------------------------------------
            | UNIQUE MODAL ID
            |--------------------------------------------------------------------------
            */

            $projectModalId = 'projectModal_' . (int) $project['id'];
            ?>

            <article class="project-card">

                <!-- =====================================================
                     SMALL IMAGE PREVIEW
                ====================================================== -->

                <div class="project-image-area">

                    <?php if (!empty($projectImages)): ?>

                        <div class="project-image-preview">

                            <?php foreach (
                                $projectImages
                                as $index => $image
                            ): ?>

                                <img
                                    src="<?= h($image) ?>"
                                    alt="<?= h($project['title']) ?> preview <?= $index + 1 ?>"
                                    class="<?= $index === 0 ? 'active' : '' ?>"
                                    loading="lazy"
                                    draggable="false"
                                    oncontextmenu="return false;"
                                    ondragstart="return false;"
                                    onerror="this.style.display='none';"
                                >

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <div class="project-image-placeholder">
                            <span>
                                PROJECT
                            </span>
                        </div>

                    <?php endif; ?>

                </div>


                <!-- =====================================================
                     COMPACT PROJECT BODY
                ====================================================== -->

                <div class="project-body">

                    <h3>
                        <?= h($project['title']) ?>
                    </h3>

                    <div class="project-description-wrap">

                        <p class="project-description-preview">
                            <?= nl2br(
                                h(
                                    trim($project['description'] ?? '')
                                )
                            ) ?>
                        </p>

                    </div>


                    <!-- SEE MORE -->

                    <button
                        type="button"
                        class="project-see-more"
                        data-project-modal="<?= h($projectModalId) ?>"
                        aria-label="See more about <?= h($project['title']) ?>"
                    >
                        See more →
                    </button>


                    <!-- TAGS -->

                    <?php if (!empty($tags)): ?>

                        <div class="tags">

                            <?php foreach ($tags as $tag): ?>

                                <span>
                                    <?= h($tag) ?>
                                </span>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </div>

            </article>


            <!-- =====================================================
                 PROJECT MODAL
            ====================================================== -->

            <div
                class="project-modal"
                id="<?= h($projectModalId) ?>"
                aria-hidden="true"
                role="dialog"
                aria-modal="true"
                aria-labelledby="<?= h($projectModalId) ?>_title"
            >

                <div class="project-modal-overlay" data-modal-close></div>

                <div class="project-modal-dialog">

                    <button
                        type="button"
                        class="project-modal-close"
                        data-modal-close
                        aria-label="Close project details"
                    >
                        ×
                    </button>


                    <div class="project-modal-content">

                        <div class="project-modal-header">

                            <p class="project-modal-eyebrow">
                                FEATURED PROJECT
                            </p>

                            <h2 id="<?= h($projectModalId) ?>_title">
                                <?= h($project['title']) ?>
                            </h2>

                        </div>


                        <!-- =================================================
                             ALL PROJECT IMAGES
                        ================================================== -->

                        <?php if (!empty($projectImages)): ?>
                            <div class="project-modal-gallery" data-gallery>
                                <div class="gallery-main">
                                    <img class="gallery-current-image" src="<?= h($projectImages[0]) ?>" alt="<?= h($project['title']) ?> image 1" draggable="false">
                                    <?php if (count($projectImages) > 1): ?>
                                        <button type="button" class="gallery-control gallery-prev" aria-label="Previous image">‹</button>
                                        <button type="button" class="gallery-control gallery-next" aria-label="Next image">›</button>
                                    <?php endif; ?>
                                </div>
                                <div class="gallery-tools">
                                    <span class="gallery-count">Image 1 of <?= count($projectImages) ?></span>
                                    <button type="button" class="gallery-fullscreen">⛶ View fullscreen</button>
                                </div>
                                <?php if (count($projectImages) > 1): ?>
                                    <div class="gallery-thumbnails" aria-label="Project image thumbnails">
                                        <?php foreach ($projectImages as $index => $image): ?>
                                            <button type="button" class="gallery-thumb <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>" aria-label="View image <?= $index + 1 ?>">
                                                <img src="<?= h($image) ?>" alt="" loading="lazy">
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <script type="application/json" class="gallery-image-data"><?= json_encode(array_values($projectImages), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) ?></script>
                            </div>
                        <?php endif; ?>


                        <!-- =================================================
                             FULL PROJECT DESCRIPTION
                        ================================================== -->

                        <div class="project-modal-description">

                            <h3>
                                About this project
                            </h3>

                            <p>
                                <?= nl2br(
                                    h(
                                        $project['description'] ?? ''
                                    )
                                ) ?>
                            </p>

                        </div>


                        <!-- =================================================
                             TAGS
                        ================================================== -->

                        <?php if (!empty($tags)): ?>

                            <div class="project-modal-tags">

                                <?php foreach ($tags as $tag): ?>

                                    <span>
                                        <?= h($tag) ?>
                                    </span>

                                <?php endforeach; ?>

                            </div>

                        <?php endif; ?>


                        <!-- =================================================
                             PROJECT URL
                        ================================================== -->

                        <?php if (!empty($project['url'])): ?>

                            <div class="project-modal-actions">

                                <a
                                    class="btn btn-dark project-button"
                                    href="<?= h($project['url']) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    View Project ↗
                                </a>

                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>


        <?php endforeach; ?>


    </div>


</section>



<!-- =========================================================
     CONTACT
========================================================= -->

<section
    class="contact section"
    id="contact"
>


    <div class="contact-card">


        <div>


            <p class="eyebrow">

                LET'S WORK TOGETHER

            </p>


            <h2>

                Have a project in mind?

            </h2>


            <p>

                Send a message and let's turn your idea
                into a working web application.

            </p>


            <?php if (!empty($settings['email'])): ?>

                <p style="margin-top:15px;">

                    <b>

                        <?= h(
                            $settings['email']
                        ) ?>

                    </b>

                </p>

            <?php endif; ?>


        </div>



        <form
            id="contactForm"
            action="contact.php"
            method="POST"
        >


            <input
                type="text"
                name="name"
                placeholder="Your name"
                required
            >


            <input
                type="email"
                name="email"
                placeholder="Email address"
                required
            >


            <textarea
                name="message"
                rows="5"
                placeholder="Tell me about your project..."
                required
            ></textarea>


            <button
                type="submit"
                class="btn btn-dark"
            >

                Send Message

            </button>


            <div id="formMessage"></div>


        </form>


    </div>


</section>


</main>



<!-- =========================================================
     FOOTER
========================================================= -->

<footer class="site-footer">
    <div class="footer-main">
        <div class="footer-brand">
            <div class="logo"><?= h($settings['site_name'] ?? 'ICONice by Mike.') ?><span class="online-dot"></span></div>
            <p><?= nl2br(h($settings['footer_text'] ?? 'Building ideas into working systems.')) ?></p>
        </div>
        <div class="footer-contact">
            <h3>Let's Connect</h3>
            <?php if (!empty($settings['email'])): ?><a href="mailto:<?= h($settings['email']) ?>">✉ <?= h($settings['email']) ?></a><?php endif; ?>
            <?php if (!empty($settings['phone'])): ?><a href="tel:<?= h(preg_replace('/[^0-9+]/', '', $settings['phone'])) ?>">☎ <?= h($settings['phone']) ?></a><?php endif; ?>
            <?php if (!empty($settings['location'])): ?><span>⌖ <?= h($settings['location']) ?></span><?php endif; ?>
        </div>
        <div class="footer-socials">
            <h3>Social Links</h3>
            <?php foreach (['github' => 'GitHub', 'linkedin' => 'LinkedIn', 'facebook' => 'Facebook', 'dribbble' => 'Dribbble'] as $key => $label): ?>
                <?php if (!empty($settings[$key]) && $settings[$key] !== '#'): ?><a href="<?= h($settings[$key]) ?>" target="_blank" rel="noopener noreferrer"><?= h($label) ?> ↗</a><?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="footer-bottom"><span>© <?= date('Y') ?> <?= h($settings['site_name'] ?? 'Portfolio') ?>. All rights reserved.</span><a href="admin/login.php">Admin</a></div>
</footer>
<style>
.site-footer{padding:48px max(5%,calc((100% - 1180px)/2)) 20px;background:#0b0d10;color:#e9edf2;border-top:1px solid rgba(255,255,255,.09)}
.footer-main{display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:35px;padding-bottom:30px}.footer-main h3{font-size:1rem;margin:0 0 14px}.footer-brand p{max-width:340px;color:#9ba5b1;line-height:1.7}.footer-contact,.footer-socials{display:flex;flex-direction:column;align-items:flex-start;gap:10px}.footer-contact a,.footer-contact span,.footer-socials a{color:#b9c1cc;text-decoration:none;overflow-wrap:anywhere}.footer-contact a:hover,.footer-socials a:hover,.footer-bottom a:hover{color:#fff}.footer-bottom{display:flex;justify-content:space-between;gap:15px;border-top:1px solid rgba(255,255,255,.1);padding-top:18px;color:#89939f;font-size:.82rem}.footer-bottom a{color:#b9c1cc;text-decoration:none}@media(max-width:700px){.footer-main{grid-template-columns:1fr;gap:22px}.footer-bottom{flex-direction:column}}
</style>



<!-- =========================================================
     PROJECT IMAGE FADE SCRIPT
========================================================= -->

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {


        const previews =
            document.querySelectorAll(
                '.project-image-preview'
            );


        previews.forEach(
            function (preview) {


                const images =
                    preview.querySelectorAll('img');


                /*
                |--------------------------------------------------------------------------
                | One image only
                |--------------------------------------------------------------------------
                */

                if (images.length <= 1) {

                    return;

                }


                let current = 0;


                /*
                |--------------------------------------------------------------------------
                | IMAGE FADE
                |--------------------------------------------------------------------------
                */

                setInterval(
                    function () {


                        images[current]
                            .classList
                            .remove('active');


                        current++;


                        if (
                            current >= images.length
                        ) {

                            current = 0;

                        }


                        images[current]
                            .classList
                            .add('active');


                    },
                    3000
                );


            }
        );


    }
);

</script>



<!-- =========================================================
     TYPING EFFECT
========================================================= -->

<script>

const typingRoles = <?= json_encode(
    $typingRoles,
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
) ?>;


const typingText =
    document.getElementById(
        'typingText'
    );


let wordIndex = 0;

let charIndex = 0;

let isDeleting = false;



/*
|--------------------------------------------------------------------------
| TYPING SETTINGS
|--------------------------------------------------------------------------
*/

const typingSpeed = 100;

const deletingSpeed = 60;

const pauseAfterTyping = 1800;

const pauseAfterDeleting = 500;



/*
|--------------------------------------------------------------------------
| TYPING EFFECT
|--------------------------------------------------------------------------
*/

function typeEffect()
{


    if (
        !typingText ||
        !Array.isArray(typingRoles) ||
        typingRoles.length === 0
    ) {

        return;

    }


    const currentWord =
        typingRoles[wordIndex];


    /*
    |--------------------------------------------------------------------------
    | TYPING
    |--------------------------------------------------------------------------
    */

    if (!isDeleting) {


        typingText.textContent =
            currentWord.substring(
                0,
                charIndex + 1
            );


        charIndex++;


        if (
            charIndex ===
            currentWord.length
        ) {


            setTimeout(
                function () {

                    isDeleting = true;

                    typeEffect();

                },
                pauseAfterTyping
            );


            return;

        }


        setTimeout(
            typeEffect,
            typingSpeed
        );


        return;

    }



    /*
    |--------------------------------------------------------------------------
    | DELETING
    |--------------------------------------------------------------------------
    */

    typingText.textContent =
        currentWord.substring(
            0,
            charIndex - 1
        );


    charIndex--;


    if (charIndex === 0) {


        isDeleting = false;

        wordIndex++;


        if (
            wordIndex >=
            typingRoles.length
        ) {

            wordIndex = 0;

        }


        setTimeout(
            typeEffect,
            pauseAfterDeleting
        );


        return;

    }


    setTimeout(
        typeEffect,
        deletingSpeed
    );

}



/*
|--------------------------------------------------------------------------
| START TYPING
|--------------------------------------------------------------------------
*/

typeEffect();

</script>




<!-- =========================================================
     PROJECT MODAL SCRIPT
========================================================= -->

<script>
document.addEventListener('DOMContentLoaded', function () {

    const modalButtons = document.querySelectorAll(
        '[data-project-modal]'
    );

    const modals = document.querySelectorAll(
        '.project-modal'
    );

    let activeModal = null;
    let lastFocusedElement = null;


    /*
    |--------------------------------------------------------------------------
    | OPEN MODAL
    |--------------------------------------------------------------------------
    */

    function openProjectModal(modal) {

        if (!modal) {
            return;
        }

        lastFocusedElement = document.activeElement;
        activeModal = modal;

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');

        document.body.classList.add('project-modal-open');

        const closeButton =
            modal.querySelector('.project-modal-close');

        if (closeButton) {
            setTimeout(function () {
                closeButton.focus();
            }, 50);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CLOSE MODAL
    |--------------------------------------------------------------------------
    */

    function closeProjectModal(modal) {

        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

        document.body.classList.remove('project-modal-open');

        if (activeModal === modal) {
            activeModal = null;
        }

        if (
            lastFocusedElement &&
            typeof lastFocusedElement.focus === 'function'
        ) {
            lastFocusedElement.focus();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SEE MORE BUTTONS
    |--------------------------------------------------------------------------
    */

    modalButtons.forEach(function (button) {

        button.addEventListener('click', function () {

            const modalId =
                button.getAttribute('data-project-modal');

            const modal =
                document.getElementById(modalId);

            openProjectModal(modal);

        });

    });


    /*
    |--------------------------------------------------------------------------
    | CLOSE BUTTON + OVERLAY
    |--------------------------------------------------------------------------
    */

    modals.forEach(function (modal) {

        const closeElements =
            modal.querySelectorAll('[data-modal-close]');

        closeElements.forEach(function (element) {

            element.addEventListener('click', function () {
                closeProjectModal(modal);
            });

        });

    });


    /*
    |--------------------------------------------------------------------------
    | ESCAPE KEY
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', function (event) {

        if (
            event.key === 'Escape' &&
            activeModal
        ) {
            closeProjectModal(activeModal);
        }

    });


    /*
    |--------------------------------------------------------------------------
    | PREVENT MODAL CONTENT CLICK FROM CLOSING
    |--------------------------------------------------------------------------
    */

    modals.forEach(function (modal) {

        const dialog =
            modal.querySelector('.project-modal-dialog');

        if (!dialog) {
            return;
        }

        dialog.addEventListener('click', function (event) {
            event.stopPropagation();
        });

    });


    /*
    |--------------------------------------------------------------------------
    | DISABLE IMAGE DRAGGING INSIDE PROJECT MODALS
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.project-modal img')
        .forEach(function (image) {

            image.addEventListener(
                'dragstart',
                function (event) {
                    event.preventDefault();
                }
            );

        });

});
</script>

<!-- =========================================================
     MAIN SCRIPT
========================================================= -->

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-gallery]').forEach(function(gallery) {
    let images = [];
    try { images = JSON.parse(gallery.querySelector('.gallery-image-data')?.textContent || '[]'); } catch(e) {}
    if (!images.length) return;
    let index = 0;
    const main = gallery.querySelector('.gallery-current-image');
    const count = gallery.querySelector('.gallery-count');
    const thumbs = Array.from(gallery.querySelectorAll('.gallery-thumb'));
    function show(next) {
      index = (next + images.length) % images.length;
      main.src = images[index];
      main.alt = 'Project image ' + (index + 1);
      if (count) count.textContent = 'Image ' + (index + 1) + ' of ' + images.length;
      thumbs.forEach(function(t, i) { t.classList.toggle('active', i === index); if(i === index) t.scrollIntoView({block:'nearest',inline:'nearest',behavior:'smooth'}); });
    }
    gallery.querySelector('.gallery-prev')?.addEventListener('click', function(){show(index - 1)});
    gallery.querySelector('.gallery-next')?.addEventListener('click', function(){show(index + 1)});
    thumbs.forEach(function(t){t.addEventListener('click', function(){show(Number(t.dataset.index || 0))})});
    gallery.querySelector('.gallery-fullscreen')?.addEventListener('click', function(){
      const isFull = gallery.classList.toggle('is-fullscreen');
      this.textContent = isFull ? '× Exit fullscreen' : '⛶ View fullscreen';
      if (isFull) gallery.scrollIntoView({block:'center'});
    });
    document.addEventListener('keydown', function(e){
      if (!gallery.closest('.project-modal.is-open')) return;
      if (e.key === 'ArrowLeft') show(index - 1);
      if (e.key === 'ArrowRight') show(index + 1);
      if (e.key === 'Escape' && gallery.classList.contains('is-fullscreen')) {
        gallery.classList.remove('is-fullscreen');
        const btn=gallery.querySelector('.gallery-fullscreen'); if(btn) btn.textContent='⛶ View fullscreen';
      }
    });
  });
});
</script>

<script src="script.js"></script>


</body>

</html>