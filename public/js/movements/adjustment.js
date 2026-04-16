/**
 * Sub-module for Inventory Adjustments (Ajuste).
 */
window.MovementsApp.Adjustment = (function (App) {
    'use strict';

    return {
        async init() {
            App.initRepeater('adjustment-rows', 'tpl-adjustment-row', 'adjustment-add-row', async (row) => {
                const itemSelect = row.querySelector('[data-field="item_id"]');
                const locSelect = row.querySelector('[data-field="location_id"]');
                const stockDisplay = row.querySelector('[data-field="current_stock"]');

                const [items, locations] = await Promise.all([App.loadItems(), App.loadLocations()]);
                App.populateSelect(itemSelect, items, 'Item');

                itemSelect.addEventListener('change', async () => {
                    if (!itemSelect.value) {
                        locSelect.innerHTML = '<option value="">Select item...</option>';
                        stockDisplay.textContent = '—';
                        return;
                    }

                    locSelect.innerHTML = '<option value="">Loading...</option>';
                    const availableLocations = await App.apiGet('/item-locations', { item_id: itemSelect.value });
                    
                    App.populateSelect(locSelect, availableLocations.map(l => ({ 
                        id: l.location_id, 
                        name: `${l.location_name} (Stock: ${l.quantity})`
                    })), 'Location');
                    
                    stockDisplay.textContent = '—';
                });

                locSelect.addEventListener('change', async () => {
                    if (!locSelect.value) {
                        stockDisplay.textContent = '—';
                        return;
                    }
                    const data = await App.apiGet('/stock', { 
                        item_id: itemSelect.value, 
                        location_id: locSelect.value 
                    });
                    stockDisplay.textContent = data.stock ?? 0;
                });
            });
        },

        isValid() {
            const rows = document.querySelectorAll('#adjustment-rows [data-row]');
            if (rows.length === 0) return false;
            return Array.from(rows).every(row => {
                const item = row.querySelector('[data-field="item_id"]').value;
                const loc = row.querySelector('[data-field="location_id"]').value;
                const qty = row.querySelector('[data-field="new_quantity"]').value;
                return item && loc && qty !== '';
            });
        },

        async submit() {
            const rows = document.querySelectorAll('#adjustment-rows [data-row]');
            const adjustments = Array.from(rows).map(row => ({
                item_id: parseInt(row.querySelector('[data-field="item_id"]').value),
                location_id: parseInt(row.querySelector('[data-field="location_id"]').value),
                new_quantity: parseFloat(row.querySelector('[data-field="new_quantity"]').value),
            }));
            return App.apiPost('/batch-adjustment', { adjustments, notes: document.getElementById('movement-notes').value });
        }
    };
})(window.MovementsApp);
