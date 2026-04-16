/**
 * Sub-module for Transfer Movements (Intercambio).
 */
window.MovementsApp.Transfer = (function (App) {
    'use strict';

    return {
        async init() {
            App.initRepeater('transfer-rows', 'tpl-transfer-row', 'transfer-add-row', async (row) => {
                const itemSelect = row.querySelector('[data-field="item_id"]');
                const originSelect = row.querySelector('[data-field="origin_location_id"]');
                const destSelect = row.querySelector('[data-field="destination_location_id"]');
                const stockDisplay = row.querySelector('[data-field="current_stock"]');

                const items = await App.loadItems();
                App.populateSelect(itemSelect, items, 'Item');

                itemSelect.addEventListener('change', async () => {
                    const itemId = itemSelect.value;
                    if (!itemId) {
                        originSelect.innerHTML = '<option value="">Select item...</option>';
                        destSelect.innerHTML = '<option value="">Select item...</option>';
                        stockDisplay.textContent = '—';
                        return;
                    }
                    
                    originSelect.innerHTML = '<option value="">Loading...</option>';
                    destSelect.innerHTML = '<option value="">Loading...</option>';

                    // Fetch all locations with stock for the chosen item
                    const [availableForOrigin, allLocations] = await Promise.all([
                        App.apiGet('/item-locations', { item_id: itemId }),
                        App.apiGet('/all-locations-stock', { item_id: itemId })
                    ]);
                    
                    // Origin: only those with stock
                    App.populateSelect(originSelect, availableForOrigin.map(l => ({ 
                        id: l.location_id, 
                        name: `${l.location_name} (Stock: ${l.quantity})` 
                    })), 'Origin');

                    // Destination: all locations showing current stock
                    App.populateSelect(destSelect, allLocations.map(l => ({ 
                        id: l.location_id, 
                        name: `${l.location_name} (Stock: ${l.quantity})` 
                    })), 'Destination');
                    
                    stockDisplay.textContent = '—';
                });

                originSelect.addEventListener('change', async () => {
                    if (!originSelect.value) {
                        stockDisplay.textContent = '—';
                        return;
                    }
                    const data = await App.apiGet('/stock', { 
                        item_id: itemSelect.value, 
                        location_id: originSelect.value 
                    });
                    stockDisplay.textContent = data.stock ?? 0;
                });
            });
        },

        isValid() {
            const rows = document.querySelectorAll('#transfer-rows [data-row]');
            if (rows.length === 0) return false;
            return Array.from(rows).every(row => {
                const item = row.querySelector('[data-field="item_id"]').value;
                const origin = row.querySelector('[data-field="origin_location_id"]').value;
                const dest = row.querySelector('[data-field="destination_location_id"]').value;
                const qty = parseFloat(row.querySelector('[data-field="quantity"]').value);
                return item && origin && dest && !isNaN(qty) && qty > 0 && origin !== dest;
            });
        },

        async submit() {
            const rows = document.querySelectorAll('#transfer-rows [data-row]');
            const transfers = Array.from(rows).map(row => ({
                item_id: parseInt(row.querySelector('[data-field="item_id"]').value),
                origin_location_id: parseInt(row.querySelector('[data-field="origin_location_id"]').value),
                destination_location_id: parseInt(row.querySelector('[data-field="destination_location_id"]').value),
                quantity: parseFloat(row.querySelector('[data-field="quantity"]').value),
            }));
            return App.apiPost('/batch-transfer', { transfers, notes: document.getElementById('movement-notes').value });
        }
    };
})(window.MovementsApp);
