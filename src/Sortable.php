<?php

namespace DinhQuocHan\QueryFilters;

trait Sortable
{
    /**
     * Initialize sortable trait.
     *
     * @return void
     */
    public function initializeSortable()
    {
        $this->getQuery()->orderBy($this->sortBy, $this->sortDirection);
    }

    /**
     * Get sortable columns.
     *
     * @return array
     */
    public function sortable()
    {
        return property_exists($this, 'sortable') ? $this->sortable : [];
    }

    /**
     * Set sort direction.
     *
     * @param  string  $direction
     */
    public function sort(string $direction = 'asc')
    {
        $this->sortDirection = ($direction === 'asc') ? 'asc' : 'desc';
    }

    /**
     * Sort by filter.
     *
     * @param  string  $column
     */
    public function sortBy($column)
    {
        if (in_array($column, $this->sortable())) {
            $this->sortBy = $column;
        }
    }
}
