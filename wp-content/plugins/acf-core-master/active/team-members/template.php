<?php
/**
 * Block Name: Team Members
 */

$id = $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

$className = 'team-members';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}

$members = get_field('team_members');

if (empty($members)) : ?>
    <div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">
        <p class="team-members__empty">No team members added yet.</p>
    </div>
<?php return; endif; ?>

<div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">

    <div class="team-members__grid">

        <?php foreach ($members as $member) :
            $photo       = $member['photo'] ?? null;
            $img_url     = $photo['url'] ?? '';
            $img_alt     = $photo['alt'] ?? '';
            $name         = $member['name'] ?? '';
            $credentials  = $member['credentials'] ?? '';
            $role         = $member['role'] ?? '';
            $bio          = $member['bio'] ?? '';
            $social_links = $member['social_links'] ?? [];

            // Sanitize bio for data attribute (strip tags, encode)
            $bio_data = esc_attr(wp_strip_all_tags($bio));

            // Encode social links as JSON for the data attribute
            $social_data = '';
            if (!empty($social_links)) {
                $clean_links = array_map(function ($link) {
                    return [
                        'url'   => $link['social_url'] ?? '',
                        'label' => $link['social_label'] ?? '',
                    ];
                }, $social_links);
                $social_data = esc_attr(wp_json_encode(array_values($clean_links)));
            }
        ?>
        <div class="team-members__card"
             data-name="<?php echo esc_attr($name); ?>"
             data-credentials="<?php echo esc_attr($credentials); ?>"
             data-role="<?php echo esc_attr($role); ?>"
             data-bio="<?php echo $bio_data; ?>"
             data-photo="<?php echo esc_url($img_url); ?>"
             data-photo-alt="<?php echo esc_attr($img_alt ?: $name); ?>"
             <?php if ($social_data) : ?>data-social="<?php echo $social_data; ?>"<?php endif; ?>>

            <?php if ($img_url) : ?>
                <div class="team-members__photo-wrap">
                    <img class="team-members__photo"
                         src="<?php echo esc_url($img_url); ?>"
                         alt="<?php echo esc_attr($img_alt ?: $name); ?>"
                         loading="lazy" />
                </div>
            <?php endif; ?>

            <div class="team-members__info">
                <?php if ($name) : ?>
                    <p class="team-members__name"><?php echo esc_html($name); ?></p>
                <?php endif; ?>

                <?php if ($credentials) : ?>
                    <p class="team-members__credentials"><?php echo esc_html($credentials); ?></p>
                <?php endif; ?>

                <?php if ($role) : ?>
                    <p class="team-members__role"><?php echo esc_html($role); ?></p>
                <?php endif; ?>

                <?php if ($bio) : ?>
                    <button class="team-members__bio-btn" type="button" aria-haspopup="dialog">
                        View bio <span aria-hidden="true"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 16L6.575 14.6L12.175 9H0V7H12.175L6.575 1.4L8 0L16 8L8 16Z" fill="#00508D"/>
                            </svg>
</span>
                    </button>
                <?php endif; ?>
            </div>

        </div>
        <?php endforeach; ?>

    </div>

    <!-- Modal -->
    <div class="team-members__modal" role="dialog" aria-modal="true" aria-label="Team member bio" hidden>
        <div class="team-members__modal-backdrop"></div>
        <div class="team-members__modal-inner">
            <button class="team-members__modal-close" type="button" aria-label="Close bio">
                <svg width="39" height="39" viewBox="0 0 39 39" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M29.25 9.75L9.75 29.25M9.75 9.75L29.25 29.25" stroke="#00508D" stroke-width="3.25" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <div class="team-members__modal-body">
                <div class="team-members__modal-header">
                    
                    <div class="team-members__modal-meta">
                        <p class="team-members__modal-name"></p>
                        <p class="team-members__modal-credentials"></p>
                        <p class="team-members__modal-role"></p>
                    </div>
                    <img class="team-members__modal-photo" src="" alt="" />
                    
                </div>
                <div class="team-members__modal-bio-wrap">
                    <div class="team-members__modal-bio"></div>
                    <div class="team-members__modal-social"></div>
                </div>
            </div>
        </div>
    </div>

</div>
