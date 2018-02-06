<?php

/*
 * This file is part of the Laravel StatusBit package.
 *
 * (c) Yvon Viger <yvon@baytek.ca>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Baytek\LaravelSortable;

use Exception;
use Request;
use Schema;

trait Sortable
{
    public function scopeSorted($query)
    {
        $properties = collect(Request::instance()->query)->only(['sort', 'order']);
        $sort = $properties->get('sort');
        $order = $properties->get('order');

        if(!$sort) {
            //Try to sort by newest first if no sorting is provided
            $model = $query->getModel();
            $table = $model->getTable();

            if (Schema::hasColumn($table, 'created_at')) {
                return $query->orderBy("$table.created_at", 'desc');
            }

            //Otherwise, just return the query unmodified
            return $query;
        }

        if(str_contains($sort, '.')) {

            $sortPair = collect(explode('.', $sort));
            $table = str_plural($sortPair->get(0));
            $field = $sortPair->get(1);

            // Check that the number of arguments is exactly zero
            if($sortPair->count() != 2) {

                // We cannot handle more than two parts when sorting
                return $query;
            }

            // Check that the model has the relation method
            if(!method_exists($this, $table) && !method_exists($this, str_singular($table))) {

                // If the sorted table is the current table we don't need to join, we simply order by those fields
                if($table == $query->getModel()->getTable()) {

                    // Do the ordering
                    return $query->orderBy("$table.$field", $order ?: 'asc');
                }

                // Cannot sort. Model "{$table}" doesn\'t exist
                return $query;
            }

            if(!Schema::hasColumn($table, $field)) {

                if($field != 'count') {

                    //Cannot sort. Field "{$field}" doesn\'t exist
                    return $query;
                }
            }

            // if the field is count
            if($field == 'count') {
                return $query->withCount($table)->orderBy($table . '_count', $order ?: 'asc');
            }

            $doJoin = true;
            $model = $query->getModel();
            $key = $model->getKeyName();
            $joinTable = $model->getTable();
            $foreignkey = $this->{str_singular($table)}()->getForeignKey();
            $joinedTables = collect([]);

            // Check if we should do the join or not
            if(count($query->getQuery()->joins)) {
                $joinedTables->merge($query->getQuery()->joins);
                dump($joinedTables->pluck('table'));

                throw new \Exception('Please tell Yvon about this, I need to know the info above or how to reproduce.');

                foreach($query->getQuery()->joins as $join) {
                    if($join->table == $table) {
                        $doJoin = false;
                    }
                }
            }

            if($doJoin) {
                $query->select(["$table.$field", "$joinTable.*"]);
                $query->leftJoin($table, "$table.$key", '=', "$joinTable.$foreignkey");
            }

            return $query->orderBy("$table.$field", $order ?: 'asc');
        }

        return $query->orderBy($sort, $order ?: 'asc');
    }
}