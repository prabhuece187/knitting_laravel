<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    protected function paginate($query, int $page, int $limit)
    {
        $page = max(1, $page);
        $limit = max(1, $limit);

        $total = $query->count();
        $totalPages = (int) ceil($total / $limit);

        $data = $query
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        return [
            'data' => $data,
            'meta' => [
                'page'       => $page,
                'limit'      => $limit,
                'total'      => $total,
                'totalPages' => $totalPages,
                'hasNext'    => $page < $totalPages,
                'hasPrev'    => $page > 1,
                'pages'      => $totalPages > 0 ? range(1, $totalPages) : [],
            ],
        ];
    }
}
