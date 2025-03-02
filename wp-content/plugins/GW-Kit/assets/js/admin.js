jQuery(document).ready(function($) {
    const container = $('.gw-kit-vertical-tabs');
    if (!container.length) return;

    const tabsList = container.find('.gw-kit-tabs-list');
    const tabsContent = container.find('.gw-kit-tabs-content');
    const manageButton = $('#gw-kit-manage-envs');
    const newEnvContainer = $('.gw-kit-new-env');
    const addEnvButton = $('.add-env-button');
    
    // Tab switching
    tabsList.on('click', '.gw-kit-tab', function(e) {
        // Don't switch tabs if clicking input in manage mode
        if ($(e.target).is('input')) return;

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
        container.toggleClass('manage-mode', !isManaging);
        
        if (!isManaging) {
            // Enter manage mode
            $('.env-name').addClass('hidden');
            $('.env-name-input').removeClass('hidden');
            $('.delete-env').removeClass('hidden');
            addEnvButton.removeClass('hidden');
            $(this).find('.dashicons').removeClass('dashicons-admin-generic').addClass('dashicons-saved');
        } else {
            // Exit manage mode
            $('.env-name').removeClass('hidden');
            $('.env-name-input').addClass('hidden');
            $('.delete-env').addClass('hidden');
            addEnvButton.addClass('hidden');
            newEnvContainer.addClass('hidden');
            $(this).find('.dashicons').removeClass('dashicons-saved').addClass('dashicons-admin-generic');

            // Update display names from inputs
            $('.gw-kit-tab').each(function() {
                const input = $(this).find('.env-name-input');
                const display = $(this).find('.env-name');
                display.text(input.val());
            });
        }
    });
    
    // Show new environment form
    addEnvButton.on('click', function() {
        $(this).addClass('hidden');
        newEnvContainer.removeClass('hidden');
        newEnvContainer.find('.new-env-name').focus();
    });
    
    // Cancel new environment
    newEnvContainer.find('.cancel-env').on('click', function() {
        newEnvContainer.addClass('hidden');
        addEnvButton.removeClass('hidden');
        newEnvContainer.find('.new-env-name').val('');
    });
    
    // Add new environment
    newEnvContainer.find('.add-env').on('click', function() {
        const input = newEnvContainer.find('.new-env-name');
        const name = input.val().trim();
        
        if (!name) return;
        
        const id = name.toLowerCase().replace(/[^a-z0-9]/g, '-');
        
        // Create new tab
        const tab = $(`
            <div class="gw-kit-tab-wrapper">
                <button type="button" class="gw-kit-tab active" data-env="${id}">
                    <span class="env-name hidden">${name}</span>
                    <input type="text" 
                           name="gw_kit_gtm_environments[${id}][id]" 
                           value="${name}" 
                           class="env-name-input" 
                           placeholder="Environment name">
                    <span class="dashicons dashicons-trash delete-env"></span>
                </button>
            </div>
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
        
        if (!confirm('Are you sure you want to delete this environment?')) {
            return;
        }
        
        const content = tabsContent.find(`.gw-kit-tab-content[data-env="${envId}"]`);
        
        // If this was the active tab, activate first remaining tab
        if (tab.hasClass('active')) {
            const firstTab = tabsList.find('.gw-kit-tab').not(tab).first();
            if (firstTab.length) firstTab.click();
        }
        
        tab.closest('.gw-kit-tab-wrapper').remove();
        content.remove();
    });
});

