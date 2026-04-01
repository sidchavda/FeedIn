<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\ImportLeadsJob;
use App\Models\Lead;
class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::select('id','name','mobile','father_name','emergency_contact','blood_group','created_at','created_by')->with('createdBy')->paginate(10);
        // dd($leads);
        return view('pages.lead.index',['leads' => $leads]);
    }

    public function details($id)
    {
        $lead = Lead::find($id);
        return view('pages.lead.detail',['lead' => $lead]);
    }

}
