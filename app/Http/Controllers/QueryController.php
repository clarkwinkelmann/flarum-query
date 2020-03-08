<?php

namespace App\Http\Controllers;

use App\Query;
use App\SavedQuery;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class QueryController extends Controller
{
    public function store(Request $request, DatabaseManager $db)
    {
        $query = new Query();
        $query->uid = Uuid::uuid4()->toString();
        $query->sql = $request->get('sql');

        if ($saved = $request->get('fromSaved')) {
            $saved = SavedQuery::where('uid', $saved)->first();

            if ($saved) {
                $query->saved_query_id = $saved->id;
            }
        }

        $autoLimited = false;

        try {
            $sql = $query->sql;

            // Very primitive measures to add a LIMIT clause to queries, or replace LIMIT clauses for above 100
            // This won't prevent users from asking many results, but should at least prevent user errors lagging the server
            if (preg_match('~LIMIT\s*([0-9]+)~i', $sql, $matches) !== 1) {
                $sql .= ' LIMIT 100';
                $autoLimited = true;
            } else if ($matches && $matches[1] > 100) {
                $sql = preg_replace('~LIMIT\s*' . $matches[1] . '~i', 'LIMIT 100', $sql);
                $autoLimited = true;
            }

            $results = $db->connection('query-read')->getReadPdo()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

            $query->rows_count = count($results);
            $query->preview = array_slice($results, 0, 5);
        } catch (\Exception $exception) {
            $query->error = $exception->getMessage();
        }

        $query->save();

        return array_merge($query->toArray(), [
            'results' => isset($results) ? $results : null,
            'auto_limited' => $autoLimited,
        ]);
    }
}
