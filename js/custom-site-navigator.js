jQuery(document).ready(function($) {
    // Initialize sortable
    $('#csn-sites').sortable({
        items: '.csn-site',
        handle: '.csn-site-icon',
        stop: function(event, ui) {
            const order = [];
            $('.csn-site').each(function() {
                order.push($(this).data('id'));
            });
            
            $.ajax({
                url: csnAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'csn_update_order',
                    order: order,
                    nonce: csnAjax.nonce
                },
                success: function(response) {
                    if (!response.success) {
                        console.error('Failed to update order');
                    }
                }
            });
        }
    });
    
    // Show/hide dots menu
    $(document).on('mouseenter', '.csn-site', function() {
        $(this).find('.csn-dots').show();
    }).on('mouseleave', '.csn-site', function() {
        $(this).find('.csn-dots').hide();
        $(this).find('.csn-dropdown').hide();
    });
    
    // Toggle dropdown menu
    $(document).on('click', '.csn-dots', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).siblings('.csn-dropdown').toggle();
    });
    
    // Add site modal
    $('.csn-add-site').click(function() {
        $('#csn-add-modal').show();
    });
    
    // Close modals
    $('.csn-btn-secondary').click(function() {
        $('.csn-modal').hide();
    });
    
    // Add site form submission
    $('#csn-add-form').submit(function(e) {
        e.preventDefault();
        
        const siteName = $('#site-name').val();
        const siteUrl = $('#site-url').val();
        
        $.ajax({
            url: csnAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'csn_add_site',
                site_name: siteName,
                site_url: siteUrl,
                nonce: csnAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to add site');
                }
            }
        });
    });
    
    // Delete site
    $(document).on('click', '.csn-delete', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to delete this site?')) {
            return;
        }
        
        const siteId = $(this).data('id');
        
        $.ajax({
            url: csnAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'csn_delete_site',
                site_id: siteId,
                nonce: csnAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to delete site');
                }
            }
        });
    });
    
    // Rename site
    $(document).on('click', '.csn-rename', function(e) {
        e.preventDefault();
        
        const siteId = $(this).data('id');
        const currentName = $(this).closest('.csn-site').find('.csn-site-name').text();
        
        $('#rename-site-id').val(siteId);
        $('#new-name').val(currentName);
        $('#csn-rename-modal').show();
    });
    
    // Rename form submission
    $('#csn-rename-form').submit(function(e) {
        e.preventDefault();
        
        const siteId = $('#rename-site-id').val();
        const newName = $('#new-name').val();
        
        $.ajax({
            url: csnAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'csn_rename_site',
                site_id: siteId,
                new_name: newName,
                nonce: csnAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to rename site');
                }
            }
        });
    });
    
    // Close dropdown when clicking outside
    $(document).click(function() {
        $('.csn-dropdown').hide();
    });
}); 