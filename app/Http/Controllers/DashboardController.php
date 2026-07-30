<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $section = $request->string('section')->toString() === 'lists'
            ? 'lists'
            : 'competitions';
        $search = $request->string('search')->trim()->toString();
        $sort = $request->string('sort')->toString();

        $order = match (true) {
            $sort === 'title' => ['title', 'asc'],
            $sort === 'names' => ['names_count', 'desc'],
            $sort === 'results' && $section === 'competitions' => ['results_count', 'desc'],
            $sort === 'oldest' => ['updated_at', 'asc'],
            default => ['updated_at', 'desc'],
        };

        $query = $section === 'lists'
            ? $request->user()->savedWheels()
            : $request->user()->competitions();

        $items = $query
            ->when($search, fn (Builder $query) => $query->where('title', 'like', '%'.$search.'%'))
            ->orderBy(...$order)
            ->paginate(12)
            ->withQueryString();

        return view('users.dashboard', compact('items', 'search', 'section', 'sort'));
    }
}
