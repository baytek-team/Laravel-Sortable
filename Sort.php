<?php

namespace App\Helpers;

use Request;

class Sort
{
	public static function links($fields)
	{
		// String result of links to be created
		$result = '';

		// Query string properties only 'sort' and 'order'
		$properties = collect(Request::instance()->query)->only(['sort', 'order']);

		// Sort query string property
		$sort = $properties->get('sort');

		// Order query string property
		$order = $properties->get('order');

		// Loop through the fields
		collect($fields)->each(function ($field, $key) use (&$result, $sort, $order)
		{
			// Set the title field value
			$title = ($field == 'id') ? strtoupper($field) : title_case(str_replace('_', ' ', $field));

			// If the keys are not numeric that means that we must use them as the sort field
			if(!is_numeric($key)) {
				$field = $key;
			}

			// Set the active class flag
			$active = ($field == $sort) ? ' active': '';

			// Set the ordering for sorting
			$ordering = ($active && $order == 'asc') ? 'desc': 'asc';

			// Set the link location, current URL with the sort query strings
			$href = urldecode(Request::fullUrlWithQuery(['sort' => $field, 'order' => $ordering]));

			// set the ordering icon
			$icon = ($active) ? ' ' . $order . 'ending' : '';

			// Append the link to the final result
			$result .= "<a href='$href' class='item$active'><i class='sort$icon icon'></i> $title</a>";
		});

		return $result;
	}

	public static function pagination()
	{
		// Query string value
		$query = Request::instance()->query;

		// return sort and order array
		return collect($query)->only(['sort', 'order'])->all();
	}
}