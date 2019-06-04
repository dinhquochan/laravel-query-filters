<?php

namespace DinhQuocHan\QueryFilters;

use Illuminate\Support\Str;

trait Searchable
{
    /**
     * Get searchable column.
     *
     * @return array
     */
    protected function searchable()
    {
        return property_exists($this, 'searchable') ? $this->searchable : [];
    }

    /**
     * Search filter.
     *
     * @param  string  $keyword
     */
    public function search($keyword)
    {
        if (! $this->searchable() || ! $keyword) {
            return;
        }

        $modifiedKeyword = $this->modifiedKeywords($keyword);

        if ($this->shouldSearchSpecificColumn()) {
            if ($this->shouldColumnIsAllowedToSearch($this->getRequest()->query('search_by'))) {
                $this->getQuery()->where($this->getRequest()->query('search_by'), 'like', $modifiedKeyword);
            }

            return;
        }

        $this->getQuery()->where(function ($query) use ($modifiedKeyword) {
            foreach ($this->searchable() as $column) {
                $query->orWhere($column, 'like', $modifiedKeyword);
            }
        });
    }

    /**
     * Search filter.
     *
     * @param  string  $keyword
     */
    public function q($keyword)
    {
        $this->search($keyword);
    }

    /**
     * Modified keywords.
     *
     * @param  string  $keyword
     * @return string
     */
    protected function modifiedKeywords($keyword)
    {
        $endSearch = Str::startsWith($keyword, '*');
        $startSearch = Str::endsWith($keyword, '*');

        if (! $endSearch && ! $startSearch) {
            return '%'.$keyword.'%';
        }

        if ($endSearch) {
            return '%'.ltrim($keyword, '*');
        }

        if ($startSearch) {
            return rtrim($keyword, '*').'%';
        }

        return $keyword;
    }

    /**
     * Determine if search by specific column.
     *
     * @return bool
     */
    protected function shouldSearchSpecificColumn()
    {
        return $this->getRequest()->filled('search_by');
    }

    /**
     * Determine if column is allowed to search.
     *
     * @param  string  $column
     * @return bool
     */
    protected function shouldColumnIsAllowedToSearch($column)
    {
        return in_array($column, $this->searchable());
    }
}
