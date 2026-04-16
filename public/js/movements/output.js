/**
 * Sub-module for Output Movements (Salida).
 */
window.MovementsApp.Output = (function (App) {
    'use strict';

    return {
        async init() {
            const itemSelect = document.getElementById('output-item');
            const locationSelect = document.getElementById('output-location');
            const stockDisplay = document.getElementById('output-current-stock');
            if (!itemSelect) return;

            const items = await App.loadItems();
            App.populateSelect(itemSelect, items, 'Select item...');

            itemSelect.addEventListener('change', async () => {
                if (!itemSelect.value) {
                    locationSelect.innerHTML = '<option value="">Select item first...</option>';
                    stockDisplay.textContent = '—';
                    return;
                }
                
                locationSelect.innerHTML = '<option value="">Loading...</option>';
                const availableLocations = await App.apiGet('/item-locations', { item_id: itemSelect.value });
                
                App.populateSelect(locationSelect, availableLocations.map(l => ({ 
                    id: l.location_id, 
                    name: `${l.location_name} (Stock: ${l.quantity})` 
                })), 'Select location...');
                
                stockDisplay.textContent = '—';
            });

            locationSelect.addEventListener('change', async () => {
                if (!locationSelect.value) {
                    stockDisplay.textContent = '—';
                    return;
                }
                const data = await App.apiGet('/stock', { 
                    item_id: itemSelect.value, 
                    location_id: locationSelect.value 
                });
                stockDisplay.textContent = data.stock ?? 0;
            });
        },

        isValid() {
            const itemId = document.getElementById('output-item').value;
            const locationId = document.getElementById('output-location').value;
            const quantity = parseFloat(document.getElementById('output-quantity').value);
            return itemId && locationId && !isNaN(quantity) && quantity > 0;
        },

        async submit() {
            const body = {
                item_id: parseInt(document.getElementById('output-item').value),
                location_id: parseInt(document.getElementById('output-location').value),
                quantity: parseFloat(document.getElementById('output-quantity').value),
                notes: document.getElementById('movement-notes').value || null,
            };
            return App.apiPost('/output', body);
        }
    };
})(window.MovementsApp);
