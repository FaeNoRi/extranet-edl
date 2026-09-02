<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class JournalController extends Controller
{
    public function index(Request $request): View
    {
        $activites = Activity::query()
            ->with('causer', 'subject')
            ->when($request->string('type')->toString(), fn ($q, $t) => $q->where('log_name', $t))
            ->when($request->string('evenement')->toString(), fn ($q, $e) => $q->where('event', $e))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.journal.index', [
            'activites' => $activites,
            'types' => Activity::query()->select('log_name')->distinct()->orderBy('log_name')->pluck('log_name')->filter(),
        ]);
    }
}
