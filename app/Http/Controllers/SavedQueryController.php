<?php

namespace App\Http\Controllers;

use App\Query;
use App\SavedQuery;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Uuid;

class SavedQueryController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'query' => 'exists:queries,uid',
            'title' => 'required|string|max:200',
            'public' => 'required|boolean',
        ]);

        /**
         * @var $query Query
         */
        $query = Query::where('uid', $request->get('query'))->firstOrFail();

        if ($query->error) {
            throw ValidationException::withMessages([
                'query' => 'You can only save successful queries',
            ]);
        }

        $savedQuery = new SavedQuery();
        $savedQuery->uid = Uuid::uuid4()->toString();
        $savedQuery->title = $request->get('title');
        $savedQuery->public = $request->get('public');
        $savedQuery->sql = $query->sql;
        $savedQuery->preview = $query->preview;
        $savedQuery->save();

        return $savedQuery->toArray();
    }
}
