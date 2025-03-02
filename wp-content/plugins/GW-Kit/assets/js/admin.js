jQuery(document).ready(function($) {
    const container = $('.gw-kit-vertical-tabs');
    if (!container.length) return;

    const tabsList = container.find('.gw-kit-tabs-list');
    const tabsContent = container.find('.gw-kit-tabs-content');
    const manageButton = $('#gw-kit-manage-envs');
    const newEnvContainer = $('.gw-kit-new-env');
    const addEnvButton = $('.add-env-button');
    
    // Tab switching
    tabsList.on('click', '.gw-kit-tab', function() {
        const envId = $(this).data('env');
        
        // Update active tab
        tabsList.find('.gw-kit-tab').removeClass('active');
        $(this).addClass('active');
        
        // Show corresponding content
        tabsContent.find('.gw-kit-tab-content').removeClass('active');
        tabsContent.find(`.gw-kit-tab-content[data-env="${envId}"]`).addClass('active');
        
        // Update URL without reload
        const url = new URL(window.location);
        url.searchParams.set('env', envId);
        window.history.pushState({}, '', url);
    });
    
    // Manage environments mode
    manageButton.on('click', function() {
        const isManaging = $(this).hasClass('active');
        $(this).toggleClass('active');
        
        if (!isManaging) {
            tabsList.find('.delete-env').removeClass('hidden');
            addEnvButton.removeClass('hidden');
            $(this).find('.dashicons').removeClass('dashicons-admin-generic').addClass('dashicons-saved');
        } else {
            tabsList.find('.delete-env').addClass('hidden');
            addEnvButton.addClass('hidden');
            newEnvContainer.addClass('hidden');
            $(this).find('.dashicons').removeClass('dashicons-saved').addClass('dashicons-admin-generic');
        }
    });
    
    // Show new environment form
    addEnvButton.on('click', function() {
        $(this).addClass('hidden');
        newEnvContainer.removeClass('hidden');
        newEnvContainer.find('input').focus();
    });
    
    // Cancel new environment
    newEnvContainer.find('.cancel-env').on('click', function() {
        newEnvContainer.addClass('hidden');
        addEnvButton.removeClass('hidden');
        newEnvContainer.find('input').val('');
    });
    
    // Add new environment
    newEnvContainer.find('.add-env').on('click', function() {
        const input = newEnvContainer.find('input');
        const name = input.val().trim();
        
        if (!name) return;
        
        const id = name.toLowerCase().replace(/[^a-z0-9]/g, '-');
        
        // Create new tab
        const tab = $(`
            <button type="button" class="gw-kit-tab active" data-env="${id}">
                ${name}
                <span class="dashicons dashicons-trash delete-env"></span>
            </button>
        `);
        
        // Create new content
        const content = $(`
            <div class="gw-kit-tab-content active" data-env="${id}">
                <div class="gw-kit-code-field">
                    <label>GTM Head Code</label>
                    <textarea name="gw_kit_gtm_environments[${id}][head_code]" 
                              class="gw-kit-code-editor" 
                              placeholder="Paste your GTM head code here..."></textarea>
                </div>
                
                <div class="gw-kit-code-field">
                    <label>GTM Body Code</label>
                    <textarea name="gw_kit_gtm_environments[${id}][body_code]" 
                              class="gw-kit-code-editor" 
                              placeholder="Paste your GTM body code here..."></textarea>
                </div>
            </div>
        `);
        
        // Add to DOM
        tabsList.find('.gw-kit-tab').removeClass('active');
        tabsContent.find('.gw-kit-tab-content').removeClass('active');
        
        tab.insertBefore(newEnvContainer);
        tabsContent.append(content);
        
        // Reset form
        input.val('');
        newEnvContainer.addClass('hidden');
        addEnvButton.removeClass('hidden');
        
        // Update URL
        const url = new URL(window.location);
        url.searchParams.set('env', id);
        window.history.pushState({}, '', url);
    });
    
    // Delete environment
    tabsList.on('click', '.delete-env', function(e) {
        e.stopPropagation();
        
        const tab = $(this).closest('.gw-kit-tab');
        const envId = tab.data('env');
        
        if (envId === 'production') {
            alert('Cannot delete production environment');
            return;
        }
        
        if (!confirm('Are you sure you want to delete this environment?')) {
            return;
        }
        
        const content = tabsContent.find(`.gw-kit-tab-content[data-env="${envId}"]`);
        
        // If this was the active tab, activate production
        if (tab.hasClass('active')) {
            tabsList.find('[data-env="production"]').click();
        }
        
        tab.remove();
        content.remove();
    });
});
