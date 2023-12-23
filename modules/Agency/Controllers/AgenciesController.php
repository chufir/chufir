<?php

namespace Modules\Agency\Controllers;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Modules\Agency\Models\Agency;
use Modules\Agency\Models\AgencyAgent;
use Modules\Contact\Models\Contact;
use Modules\Property\Models\Property;
use Modules\Review\Models\Review;

class AgenciesController extends Controller
{
    protected $agenciesClass;
    protected $agenciesAgentClass;
    protected $propertyClass;

    public function __construct()
    {
        $this->agenciesClass = new Agency();
        $this->agenciesAgentClass = new AgencyAgent();
        $this->propertyClass = new Property();
    }

    public function callAction($method, $parameters)
    {
        return parent::callAction($method, $parameters); // TODO: Change the autogenerated stub
    }

    public function index()
    {
        $agencies_count = $this->agenciesClass::where("status", "publish")->count();
        $agencies       = $this->agenciesClass->getListAgencies();
        $data = [
            'agencies_count' => $agencies_count,
            'page_title' => __('Our Agencies'),
            'agencies' => $agencies,
        ];

        return view('Agency::frontend.search', $data);
    }

    public function detail(Request $request, $slug)
    {
        $row = $this->agenciesClass::with(['agent', 'user'])->where('slug', $slug)->first();
        if (empty($row) or !$row->hasPermissionDetailView()) {
            return redirect('/');
        }
        $agentIds = $this->agenciesAgentClass::where('agencies_id', $row->id)->pluck('agent_id')->toArray();
        $listProperty = $this->propertyClass::with('Category')->whereIn('author_id', $agentIds)->where("status", "publish")
            ->with(['translation'])
            ->get();
        $review_list = Review::where('object_id', $row->id)
            ->where('object_model', 'agencies')
            ->where("status", "approved")
            ->orderBy("id", "desc")
            ->with('author')
            ->paginate(5);
        $translation = $row->translate();
        $data = [
            'row' => $row,
            'review_list' => $review_list,
            'listings' => $listProperty,
            'countListing' => $listProperty->count(),
            'translation'=>$translation,
            'page_title'=>$translation->name ?? ''
        ];
        $this->setActiveMenu($row);
        return view('Agency::frontend.detail', $data);
    }
}
