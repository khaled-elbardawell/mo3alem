<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $sort = $request->string('sort')->toString();

        $order = match ($sort) {
            'title' => ['title', 'asc'],
            'names' => ['names_count', 'desc'],
            'oldest' => ['updated_at', 'asc'],
            default => ['updated_at', 'desc'],
        };

        $savedWheels = $request->user()
            ->savedWheels()
            ->when($search, fn (Builder $query) => $query->where('title', 'like', '%'.$search.'%'))
            ->orderBy(...$order)
            ->paginate(12)
            ->withQueryString();

        return view('dashboard', compact('savedWheels', 'search', 'sort'));
    }
}
