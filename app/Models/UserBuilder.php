<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class UserBuilder extends Builder
{
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (is_string($column)) {
            if ($column === 'id' || $column === 'users.id') {
                $column = 'users.id';
            } elseif ($column === 'role') {
                $column = 'admins.role';
            } elseif ($column === 'is_active') {
                $column = 'admins.is_active';
            } elseif ($column === 'api_token') {
                $column = 'admins.api_token';
            } elseif ($column === 'api_token_hits') {
                $column = 'admins.api_token_hits';
            }
        }
        return parent::where($column, $operator, $value, $boolean);
    }

    public function whereNull($columns, $boolean = 'and', $not = false)
    {
        if (is_string($columns)) {
            if ($columns === 'api_token') {
                $columns = 'admins.api_token';
            }
        }
        return parent::whereNull($columns, $boolean, $not);
    }

    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        if (is_string($column)) {
            if ($column === 'id') {
                $column = 'users.id';
            } elseif ($column === 'role') {
                $column = 'admins.role';
            }
        }
        return parent::whereIn($column, $values, $boolean, $not);
    }
}
