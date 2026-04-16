/**
 * Sub-module for Input Movements (Ingreso Masivo).
 */
window.MovementsApp.Input = (function (App) {
    'use strict';

    return {
        async init() {
            App.initRepeater('input-distributions', 'tpl-input-row', 'input-add-row', async (row) => {
                const itemSelect = row.querySelector('[data-field="item_id"]');
                const locationSelect = row.querySelector('[data-field="location_id"]');
                const [items, locations] = await Promise.all([App.loadItems(), App.loadLocations()]);

                App.populateSelect(itemSelect, items, 'Select item...');
                App.populateSelect(locationSelect, locations, 'Select location...');

                const validateRow = () => {
                    const rowItem = itemSelect.value;
                    const rowLoc = locationSelect.value;
                    if (!rowItem || !rowLoc) return;

                    const allRows = document.querySelectorAll('#input-distributions [data-row]');
                    let count = 0;
                    allRows.forEach(r => {
                        if (r.querySelector('[data-field="item_id"]').value === rowItem &&
                            r.querySelector('[data-field="location_id"]').value === rowLoc) count++;
                    });

                    if (count > 1) {
                        App.showValidationError('This Item and Location combination already exists.', 'Duplicate entry');
                        locationSelect.value = '';
                        window.dispatchEvent(new Event('mv-state-changed'));
                    }
                };

                locationSelect.addEventListener('change', validateRow);
                itemSelect.addEventListener('change', validateRow);

                row.querySelector('[data-field="quantity"]').addEventListener('change', (e) => {
                    if (parseFloat(e.target.value) < 1) {
                        App.showValidationError('Quantity must be at least 1.', 'Invalid quantity');
                        e.target.value = 1;
                        window.dispatchEvent(new Event('mv-state-changed'));
                    }
                });
            });
        },

        isValid() {
            const rows = document.querySelectorAll('#input-distributions [data-row]');
            if (rows.length === 0) return false;
            return Array.from(rows).every(row => {
                const itemId = row.querySelector('[data-field="item_id"]').value;
                const locationId = row.querySelector('[data-field="location_id"]').value;
                const quantity = parseFloat(row.querySelector('[data-field="quantity"]').value);
                return itemId && locationId && !isNaN(quantity) && quantity >= 1;
            });
        },

        async submit() {
            const rows = document.querySelectorAll('#input-distributions [data-row]');
            const distributions = Array.from(rows).map(row => ({
                item_id: parseInt(row.querySelector('[data-field="item_id"]').value),
                location_id: parseInt(row.querySelector('[data-field="location_id"]').value),
                quantity: parseFloat(row.querySelector('[data-field="quantity"]').value),
            }));
            const notes = document.getElementById('movement-notes').value || null;

            return App.apiPost('/input', { distributions, notes });
        }
    };
})(window.MovementsApp);
