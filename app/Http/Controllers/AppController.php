<?php

namespace App\Http\Controllers;

use App\SavedQuery;

class AppController extends Controller
{
    protected function view($queries = [], $title = null)
    {
        $latest = config('app.show_latest') ? SavedQuery::where('public', true)->orderBy('created_at', 'desc')->limit(5)->get()->toArray() : [];

        return view('app')->withQueries(array_merge($latest, $queries))->withTitle($title);
    }

    public function home()
    {
        return $this->view();
    }

    public function query($uid)
    {
        $query = SavedQuery::where('uid', $uid)->firstOrFail();

        return $this->view([$query], $query->title);
    }
}
