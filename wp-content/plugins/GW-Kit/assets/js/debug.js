/**
 * GW Kit Debug JavaScript
 * Handles debug logging in the browser console
 */

jQuery(document).ready(function($) {
    // Ensure gwKitDebug object exists
    if (typeof gwKitDebug === 'undefined') {
        console.error('GW Kit Debug: Debug data not found');
        return;
    }

    // Create styled console output
    const styles = {
        success: 'color: #4CAF50; font-weight: bold',
        warning: 'color: #FFC107; font-weight: bold',
        error: 'color: #F44336; font-weight: bold',
        info: 'color: #2196F3; font-weight: bold',
        disabled: 'color: #9E9E9E; font-weight: bold'
    };

    // Main debug group
    console.group('%cGW Kit Debug', styles.info);
    console.log('Current Environment:', gwKitDebug.currentEnv);
    console.log('WP_DEBUG:', gwKitDebug.wpDebug ? 'enabled' : 'disabled');

    // GTM status subgroup
    console.group('GTM Status');

    if (!gwKitDebug.gtmEnabled) {
        console.log('%cGTM Module: Disabled in settings', styles.disabled);
    } else {
        console.log('GTM Module: Enabled');
        const envFound = gwKitDebug.environments.includes(gwKitDebug.currentEnv);
        
        console.log('Environment Matching:', {
            'Current WP Environment': gwKitDebug.currentEnv,
            'Available GTM Environments': gwKitDebug.environments,
            'Environment Found': envFound
        });

        if (envFound) {
            console.log('%cGTM Environment Match Found', styles.success);
        } else {
            console.log('%cNo Matching GTM Environment', styles.error);
        }
    }

    console.groupEnd(); // GTM Status
    console.groupEnd(); // GW Kit Debug
});
