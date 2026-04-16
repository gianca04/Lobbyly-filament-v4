/**
 * Core module for Inventory Movements.
 * Handles shared configuration, API interaction, and common utilities.
 */
window.MovementsApp = (function () {
    'use strict';

    const form = document.getElementById('movementForm');
    if (!form) return null;

    const CONFIG = Object.freeze({
        mode: form.dataset.mode,
        csrf: form.dataset.csrf,
        apiBase: form.dataset.apiBase,
        apiMovements: form.dataset.apiMovements,
        listUrl: form.dataset.listUrl,
    });

    const cache = { items: null, locations: null };

    /** API Utilities */
    async function apiRequest(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrf,
                ...options.headers
            },
            credentials: 'same-origin',
        });

        const data = await response.json();
        if (!response.ok) {
            const message = data.message || extractValidationErrors(data);
            throw new Error(message);
        }
        return data;
    }

    function extractValidationErrors(data) {
        if (!data.errors) return 'Unknown error.';
        return Object.values(data.errors).flat().join('\n');
    }

    return {
        CONFIG,
        form,
        
        async apiGet(endpoint, params = {}) {
            const url = new URL(`${CONFIG.apiBase}${endpoint}`, window.location.origin);
            Object.entries(params).forEach(([k, v]) => v && url.searchParams.set(k, v));
            return apiRequest(url.toString());
        },

        async apiPost(endpoint, body) {
            return apiRequest(`${CONFIG.apiMovements}${endpoint}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body),
            });
        },

        async loadItems() {
            if (!cache.items) cache.items = await this.apiGet('/items');
            return cache.items;
        },

        async loadLocations() {
            if (!cache.locations) cache.locations = await this.apiGet('/locations');
            return cache.locations;
        },

        populateSelect(select, data, placeholder) {
            if (!select) return;
            select.innerHTML = `<option value="">${placeholder}</option>`;
            data.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.name;
                select.appendChild(opt);
            });
        },

        showNotification(type, message) {
            const el = document.createElement('div');
            el.className = `mv-notification ${type}`;
            el.textContent = message;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 4000);
        },

        showValidationError(message, heading = 'Validation Error') {
            const headingEl = document.getElementById('mv-modal-heading');
            const contentEl = document.getElementById('mv-modal-content');
            if (headingEl) headingEl.textContent = heading;
            if (contentEl) contentEl.textContent = message;

            window.dispatchEvent(new CustomEvent('open-modal', { 
                detail: { id: 'mv-validation-modal' } 
            }));
        },

        initRepeater(containerId, templateId, addBtnId, onRowAdded) {
            const container = document.getElementById(containerId);
            const template = document.getElementById(templateId);
            const addBtn = document.getElementById(addBtnId);
            if (!container || !template) return;

            const addRow = () => {
                const clone = template.content.cloneNode(true);
                const row = clone.querySelector('[data-row]');
                container.appendChild(clone);
                if (onRowAdded) onRowAdded(row);
                window.dispatchEvent(new Event('mv-state-changed'));
            };

            if (addBtn) addBtn.addEventListener('click', addRow);

            container.addEventListener('click', (e) => {
                if (e.target.closest('.mv-row-remove')) {
                    const row = e.target.closest('[data-row]');
                    if (row && container.querySelectorAll('[data-row]').length > 1) {
                        row.remove();
                        window.dispatchEvent(new Event('mv-state-changed'));
                    }
                }
            });

            if (CONFIG.mode === 'create') addRow();
        }
    };
})();
