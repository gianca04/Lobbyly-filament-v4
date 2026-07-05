/**
 * Main Orchestrator for Inventory Movements Form.
 * Manages the UI state, type switching, and global events.
 */
(function (App) {
    'use strict';
    if (!App) return;

    let currentType = null;

    /* ================================================================
     * UI COORDINATION
     * ================================================================ */

    function updateSubmitState() {
        const submitBtn = document.getElementById('submit-movement');
        if (!submitBtn) return;

        let isValid = false;
        switch (currentType) {
            case 'input': isValid = App.Input.isValid(); break;
            case 'output': isValid = App.Output.isValid(); break;
            case 'transfer': isValid = App.Transfer.isValid(); break;
            case 'adjustment': isValid = App.Adjustment.isValid(); break;
        }
        submitBtn.disabled = !isValid;
    }

    function switchType(type) {
        currentType = type;
        
        // Update Buttons
        document.querySelectorAll('.mv-type-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.type === type);
        });

        // Update Sections
        document.querySelectorAll('.mv-section').forEach(sec => {
            sec.classList.toggle('active', sec.id === `section-${type}`);
        });

        // Toggle Notes Visibility
        const notesSection = document.getElementById('notes-section');
        if (notesSection) notesSection.style.display = 'block';

        updateSubmitState();
    }

    /* ================================================================
     * INITIALIZATION
     * ================================================================ */

    async function init() {
        // Shared Event Listeners
        window.addEventListener('mv-state-changed', updateSubmitState);
        document.addEventListener('change', (e) => {
            if (e.target.closest('#movementForm')) updateSubmitState();
        });

        // Initialize Specific Modules
        await Promise.all([
            App.Input.init(),
            App.Output.init(),
            App.Transfer.init(),
            App.Adjustment.init()
        ]);

        // Type Selector Handle
        const typeButtons = document.querySelectorAll('.mv-type-btn');
        typeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                if (App.CONFIG.mode === 'create') switchType(btn.dataset.type);
            });
        });

        // Mode Initial Logic
        if (App.CONFIG.mode === 'view') {
            const movement = JSON.parse(App.form.dataset.movement || '{}');
            switchType(movement.type);
            // View mode would need hydration logic here if needed beyond simple display
        } else {
            switchType('transfer'); // Default
        }

        // Global Submit Handle
        const submitBtn = document.getElementById('submit-movement');
        if (submitBtn) submitBtn.addEventListener('click', handleSubmit);
    }

    async function handleSubmit() {
        const submitBtn = document.getElementById('submit-movement');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing...';
        App.form.classList.add('mv-loading');

        try {
            let result;
            switch (currentType) {
                case 'input':      result = await App.Input.submit(); break;
                case 'output':     result = await App.Output.submit(); break;
                case 'transfer':   result = await App.Transfer.submit(); break;
                case 'adjustment': result = await App.Adjustment.submit(); break;
            }
            App.showNotification('success', 'Movement registered successfully.');
            setTimeout(() => { window.location.href = App.CONFIG.listUrl; }, 1500);
        } catch (error) {
            App.showNotification('error', error.message);
            submitBtn.disabled = false;
            submitBtn.textContent = 'Register Movement';
            App.form.classList.remove('mv-loading');
        }
    }

    // Start
    init();

})(window.MovementsApp);
