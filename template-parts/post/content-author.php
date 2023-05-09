<?php
$author_id = $post->post_author;
$author_image = get_field('author_profile_image', 'user_' . $author_id);
$author_title = get_field('author_title', 'user_' . $author_id);
$author_name = get_the_author_meta('display_name');
?>
<div class="author">
  <div class="author-wrapper">
    <?php if ($author_image): ?>
    <div class="author-image">
      <img src="<?= $author_image['url'] ?>" alt="<?= $author_name ?>">
    </div>
    <?php endif; ?>
    <div class="author-body">
      <?php if ($author_title): ?>
        <div class="author-body-title">
          <?= $author_title ?>
        </div>
      <?php endif; ?>
      <h4 class="author-body-name">
        <?php if ($author_url = get_the_author_meta('user_url')): ?>
          <a href="<?= $author_url ?>" target="_blank"><?= $author_name ?></a>
        <?php else: ?>
          <?= $author_name ?>
        <?php endif; ?>
      </h4>
      <div class="author-body-description"><?= get_the_author_meta('description') ?></div>
    </div>
  </div>
  <div class="author-description"><?= get_the_author_meta('description') ?></div>
</div>