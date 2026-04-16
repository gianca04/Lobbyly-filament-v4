{{-- Partial for the ADJUSTMENT form --}}
<div class="mv-section" id="section-adjustment">
    <div class="mv-table-container">
        <table class="mv-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Location</th>
                    <th style="width: 120px; text-align: center;">System Stock</th>
                    <th style="width: 120px;">Real Quantity</th>
                    @if($mode === 'create')
                        <th style="width: 80px; text-align: center;">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody id="adjustment-rows">
                {{-- Dynamic rows inserted via JS --}}
            </tbody>
        </table>
    </div>

    @if($mode === 'create')
        <button type="button" class="mv-add-btn" id="adjustment-add-row" style="margin-top: 1rem;">
            <span>+</span> Add another adjustment
        </button>
    @endif
</div>

{{-- Template for adjustment rows --}}
<template id="tpl-adjustment-row">
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
                <select data-field="location_id">
                    <option value="">Select item...</option>
                </select>
            </div>
        </td>
        <td style="text-align: center;">
            <span data-field="current_stock" class="mv-stock-display">—</span>
        </td>
        <td>
            <div class="mv-field">
                <input type="number" data-field="new_quantity" min="0" step="0.01" value="0">
            </div>
        </td>
        <td style="text-align: center; width: 80px;">
            <button type="button" class="mv-row-remove" title="Remove">&times;</button>
        </td>
    </tr>
</template>
