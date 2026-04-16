{{-- Partial for the TRANSFER form --}}
<div class="mv-section" id="section-transfer">
    <div class="mv-table-container">
        <table class="mv-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Origin</th>
                    <th style="width: 100px; text-align: center;">Stock</th>
                    <th>Destination</th>
                    <th style="width: 120px;">Quantity</th>
                    @if($mode === 'create')
                        <th style="width: 80px; text-align: center;">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody id="transfer-rows">
                {{-- Dynamic rows inserted via JS --}}
            </tbody>
        </table>
    </div>

    @if($mode === 'create')
        <button type="button" class="mv-add-btn" id="transfer-add-row" style="margin-top: 1rem;">
            <span>+</span> Add another transfer
        </button>
    @endif
</div>

{{-- Template for transfer rows --}}
<template id="tpl-transfer-row">
    <tr data-row>
        <td>
            <div class="mv-field">
                <select data-field="item_id">
                    <option value="">Select...</option>
                </select>
            </div>
        </td>
        <td>
            <div class="mv-field">
                <select data-field="origin_location_id">
                    <option value="">Select item...</option>
                </select>
            </div>
        </td>
        <td style="text-align: center;">
            <span data-field="current_stock" class="mv-stock-display">—</span>
        </td>
        <td>
            <div class="mv-field">
                <select data-field="destination_location_id">
                    <option value="">Select...</option>
                </select>
            </div>
        </td>
        <td>
            <div class="mv-field">
                <input type="number" data-field="quantity" min="0.01" step="0.01" value="1">
            </div>
        </td>
        <td style="text-align: center; width: 80px;">
            <button type="button" class="mv-row-remove" title="Remove">&times;</button>
        </td>
    </tr>
</template>
