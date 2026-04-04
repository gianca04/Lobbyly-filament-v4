<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Modelo pivot que representa la relación entre artículos y categorías.
 */
class CategoryItem extends Pivot
{
    /**
     * Indica si los IDs de la tabla son incrementales.
     *
     * @var bool
     */
    public $incrementing = true;
}
