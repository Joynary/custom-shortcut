<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'custom_site_navigator';
$sites = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d ORDER BY display_order ASC",
        get_current_user_id()
    )
);
?>

<div class="csn-container">
    <div class="csn-sites" id="csn-sites">
        <?php foreach ($sites as $site): ?>
            <div class="csn-site" data-id="<?php echo esc_attr($site->id); ?>">
                <a href="<?php echo esc_url($site->site_url); ?>" target="_blank" class="csn-site-link">
                    <div class="csn-site-icon">
                        <img src="<?php echo esc_url($site->site_logo); ?>" alt="<?php echo esc_attr($site->site_name); ?>" onerror="this.parentElement.innerHTML = '🌏'">
                    </div>
                    <div class="csn-site-name"><?php echo esc_html($site->site_name); ?></div>
                </a>
                <div class="csn-site-actions">
                    <span class="csn-dots">•••</span>
                    <div class="csn-dropdown">
                        <a href="#" class="csn-rename" data-id="<?php echo esc_attr($site->id); ?>">Rename</a>
                        <a href="#" class="csn-delete" data-id="<?php echo esc_attr($site->id); ?>">Delete</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="csn-add-site">
            <div class="csn-site-icon">
                <span class="csn-plus">+</span>
            </div>
            <div class="csn-site-name">Add Site</div>
        </div>
    </div>
</div>

<!-- Add Site Modal -->
<div class="csn-modal" id="csn-add-modal">
    <div class="csn-modal-content">
        <h3>Add New Site</h3>
        <form id="csn-add-form">
            <div class="csn-form-group">
                <label for="site-name">Site Name:</label>
                <input type="text" id="site-name" name="site-name" required>
            </div>
            <div class="csn-form-group">
                <label for="site-url">Site URL:</label>
                <input type="url" id="site-url" name="site-url" required>
            </div>
            <div class="csn-form-actions">
                <button type="submit" class="csn-btn csn-btn-primary">Add</button>
                <button type="button" class="csn-btn csn-btn-secondary" id="csn-cancel">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Rename Modal -->
<div class="csn-modal" id="csn-rename-modal">
    <div class="csn-modal-content">
        <h3>Rename Site</h3>
        <form id="csn-rename-form">
            <input type="hidden" id="rename-site-id">
            <div class="csn-form-group">
                <label for="new-name">New Name:</label>
                <input type="text" id="new-name" name="new-name" required>
            </div>
            <div class="csn-form-actions">
                <button type="submit" class="csn-btn csn-btn-primary">Save</button>
                <button type="button" class="csn-btn csn-btn-secondary csn-cancel">Cancel</button>
            </div>
        </form>
    </div>
</div> 